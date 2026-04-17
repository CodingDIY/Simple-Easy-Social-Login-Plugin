<?php
/**
 * LINE OAuth provider implementation.
 *
 * Responsible for:
 * - building the LINE authorization URL,
 * - exchanging authorization codes for access tokens,
 * - fetching user profile data from LINE APIs,
 * - normalizing provider-specific user data into a unified structure.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
  exit;
}

if (!interface_exists('SESLP_Provider_Interface')) {
  // Interface should be loaded by the main plugin before this file.
  return;
}

/**
 * LINE provider adapter.
 *
 * Implements the SESLP provider interface so the authentication flow
 * can remain provider-agnostic across the plugin.
 */
final class SESLP_Provider_Line implements SESLP_Provider_Interface {
  /** Provider slug */
  private const SLUG = SESLP_LN_SLUG;

  /** Cached registry config */
  private array $cfg;

  private string $client_id = '';
  private string $client_secret = '';

  /**
   * Initialize provider configuration and credentials.
   *
   * Loads provider configuration from the registry and retrieves
   * stored client credentials from plugin options.
   *
   * @return void
   */
  public function __construct() {
    $this->cfg = class_exists('SESLP_Providers_Registry') ? ((array)SESLP_Providers_Registry::get(self::SLUG) ?: []) : [];
    if (class_exists('SESLP_Helpers')) {
      $this->client_id     = (string) SESLP_Helpers::get_client_id(self::SLUG);
      $this->client_secret = (string) SESLP_Helpers::get_client_secret(self::SLUG);
    }
  }

  /**
   * Build the LINE authorization URL.
   *
   * Includes required OAuth parameters such as client ID, redirect URI,
   * requested scopes, and CSRF state token.
   *
   * @return string
   */
  public function get_auth_url(): string {
    if ($this->client_id === '') {
      return '#';
    }

    $auth_base = (string)($this->cfg['auth_url'] ?? 'https://access.line.me/oauth2/v2.1/authorize');
    // Line scopes are space-separated. For email we need openid + email; profile gives name/picture.
    $scope_str = implode(' ', SESLP_Helpers::get_scopes($this->cfg, ['profile','openid','email']));

    // CSRF state
    if (!class_exists('SESLP_State')) {
      return '#';
    }
    $state = SESLP_State::create(self::SLUG);

    $args = [
      'response_type' => 'code',
      'client_id'     => $this->client_id,
      'redirect_uri'  => $this->get_redirect_uri(),
      'state'         => $state,
      'scope'         => $scope_str,
    ];

    return add_query_arg($args, $auth_base);
  }

  /**
   * Generate the OAuth callback URL for this provider.
   *
   * @return string
   */
  public function get_redirect_uri(): string {
    return esc_url_raw(add_query_arg(['social_login' => self::SLUG], home_url('/')));
  }

  /**
   * Exchange authorization code for an access token.
   *
   * @param string $code
   * @param string $state
   * @return array<string, mixed>
   */
  public function exchange_code(string $code, string $state): array {
    if ($code === '') {
      return [];
    }

    if ($this->client_id === '' || $this->client_secret === '') {
      return [];
    }

    $token_url = SESLP_Helpers::get_config_string($this->cfg, 'token_url', 'https://api.line.me/oauth2/v2.1/token');

    $body = [
      'grant_type'   => 'authorization_code',
      'code'         => $code,
      'redirect_uri' => $this->get_redirect_uri(),
      'client_id'    => $this->client_id,
      'client_secret'=> $this->client_secret,
    ];

    $resp = wp_remote_post($token_url, [
      'timeout' => 15,
      'headers' => [ 'Content-Type' => 'application/x-www-form-urlencoded' ],
      'body'    => $body,
    ]);
    if (is_wp_error($resp)) {
      return [];
    }
    $data = json_decode(wp_remote_retrieve_body($resp), true);
    if (class_exists('SESLP_Logger')) {
      SESLP_Logger::debug('Line token response', [
        'has_access_token' => (is_array($data) && isset($data['access_token'])),
      ]);
    }
    return is_array($data) ? $data : [];
  }

  /**
   * Fetch raw user profile data from LINE.
   *
   * @param string $access_token
   * @return array<string, mixed>
   */
  public function fetch_userinfo(string $access_token): array {
    if ($access_token === '') {
      return [];
    }

    $userinfo_url = SESLP_Helpers::get_config_string($this->cfg, 'userinfo_url', 'https://api.line.me/v2/profile');

    $resp = wp_remote_get($userinfo_url, [
      'timeout' => 15,
      'headers' => [ 'Authorization' => 'Bearer ' . $access_token ],
    ]);
    if (is_wp_error($resp)) {
      return [];
    }
    $profile = json_decode(wp_remote_retrieve_body($resp), true);
    return is_array($profile) ? $profile : [];
  }

  /**
   * Normalize LINE user data into a standard structure.
   *
   * Note: email is not included in the /v2/profile response and may need
   * to be retrieved separately using the ID token verification endpoint.
   *
   * @param array<string, mixed> $raw
   * @return array{id:string,email:string,name:string,picture:string}
   */
  public function normalize_userinfo(array $raw): array {
    $id   = sanitize_text_field((string)($raw['userId'] ?? ''));
    $name = sanitize_text_field((string)($raw['displayName'] ?? ''));
    $pic  = esc_url_raw((string)($raw['pictureUrl'] ?? ''));

    // Email is not present in /v2/profile. The auth handler may enrich this later using id_token.
    return [
      'id'      => $id,
      'email'   => '',
      'name'    => $name,
      'picture' => $pic,
    ];
  }

  /**
   * Retrieve email address from LINE ID token.
   *
   * Requires openid and email scopes and validates the token using
   * the LINE verification endpoint.
   *
   * @param string $id_token
   * @return string
   */
  public function fetch_email_from_id_token(string $id_token): string {
    $client_id = $this->client_id;
    if ($id_token === '' || $client_id === '') {
      return '';
    }

    $verify_url = SESLP_Helpers::get_config_string($this->cfg, 'verify_url', 'https://api.line.me/oauth2/v2.1/verify');
    $resp = wp_remote_post($verify_url, [
      'timeout' => 15,
      'headers' => [ 'Content-Type' => 'application/x-www-form-urlencoded' ],
      'body'    => [
        'id_token'  => $id_token,
        'client_id' => $client_id,
      ],
    ]);
    if (is_wp_error($resp)) {
      return '';
    }
    $data = json_decode(wp_remote_retrieve_body($resp), true);
    $email = (string)($data['email'] ?? '');
    return sanitize_email($email);
  }
}