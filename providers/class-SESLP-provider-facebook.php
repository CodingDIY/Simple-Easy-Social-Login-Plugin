<?php
/**
 * Facebook OAuth provider implementation.
 *
 * Responsible for:
 * - building the Facebook authorization URL,
 * - exchanging authorization codes for access tokens,
 * - fetching user profile data from the Graph API,
 * - normalizing provider-specific user data into a unified structure.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
  exit;
}

if (!interface_exists('SESLP_Provider_Interface')) {
  // Soft guard: interface should be loaded by the main plugin before this file.
  return;
}

/**
 * Facebook provider adapter.
 *
 * Implements the SESLP provider interface so the authentication flow
 * can remain provider-agnostic across the plugin.
 */
final class SESLP_Provider_Facebook implements SESLP_Provider_Interface {
  /** Provider slug */
  private const SLUG = SESLP_FB_SLUG;

  /** Cached registry config */
  private array $cfg;

  /** Cached client credentials */
  private string $client_id     = '';
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
    $this->cfg = class_exists('SESLP_Providers_Registry')
      ? (SESLP_Providers_Registry::get(self::SLUG) ?: [])
      : [];

    if (class_exists('SESLP_Helpers')) {
      $this->client_id     = (string) SESLP_Helpers::get_client_id(self::SLUG);
      $this->client_secret = (string) SESLP_Helpers::get_client_secret(self::SLUG);
    }
  }

  /**
   * Build the Facebook authorization URL.
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

    $auth_base = SESLP_Helpers::get_config_string(
      $this->cfg,
      'auth_url',
      'https://www.facebook.com/v18.0/dialog/oauth'
    );

    // FB uses comma-separated scopes
    $scope_str = implode(',', SESLP_Helpers::get_scopes($this->cfg, ['email', 'public_profile']));

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
      // 'auth_type'   => 'rerequest', // (optional) to re-prompt declined permissions
    ];

    return add_query_arg($args, $auth_base);
  }

  /**
   * Generate the OAuth callback URL for this provider.
   *
   * @return string
   */
  public function get_redirect_uri(): string {
    $url = add_query_arg(['social_login' => self::SLUG], home_url('/'));
    return esc_url_raw($url);
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

    // Ensure we have client credentials
    if ($this->client_id === '' || $this->client_secret === '') {
      return [];
    }

    $token_url = SESLP_Helpers::get_config_string(
      $this->cfg,
      'token_url',
      'https://graph.facebook.com/v18.0/oauth/access_token'
    );

    // Facebook token exchange must be a POST request
    $resp = wp_remote_post($token_url, [
      'timeout' => 15,
      'body'    => [
        'client_id'     => $this->client_id,
        'client_secret' => $this->client_secret,
        'redirect_uri'  => $this->get_redirect_uri(),
        'code'          => $code,
        'grant_type'    => 'authorization_code',
      ],
    ]);

    if (is_wp_error($resp)) {
      return [];
    }

    $body = wp_remote_retrieve_body($resp);
    $data = json_decode($body, true);

    if (class_exists('SESLP_Logger')) {
      SESLP_Logger::debug('FB token response', [
        'has_access_token' => isset($data['access_token']),
      ]);
    }

    return is_array($data) ? $data : [];
  }

  /**
   * Fetch raw user profile data from Facebook.
   *
   * @param string $access_token
   * @return array<string, mixed>
   */
  public function fetch_userinfo(string $access_token): array {
    if ($access_token === '') {
      return [];
    }

    // Request name, email and a square profile picture URL
    $userinfo_url = SESLP_Helpers::get_config_string(
      $this->cfg,
      'userinfo_url',
      'https://graph.facebook.com/v18.0/me'
    );

    $url = add_query_arg(
      [
        'fields'       => 'id,name,email,picture.type(large)',
        'access_token' => $access_token,
      ],
      $userinfo_url
    );

    $resp = wp_remote_get($url, ['timeout' => 15]);
    if (is_wp_error($resp)) {
      return [];
    }

    $data = json_decode(wp_remote_retrieve_body($resp), true);
    return is_array($data) ? $data : [];
  }

  /**
   * Normalize Facebook user data into a standard structure.
   *
   * @param array<string, mixed> $raw
   * @return array{id:string,email:string,name:string,picture:string}
   */
  public function normalize_userinfo(array $raw): array {
    $id    = sanitize_text_field((string) ($raw['id'] ?? ''));
    $email = sanitize_email((string) ($raw['email'] ?? ''));
    $name  = sanitize_text_field((string) ($raw['name'] ?? ''));
    $pic   = '';

    // picture structure: ['picture' => ['data' => ['url' => '...']]]
    if (!empty($raw['picture']['data']['url'])) {
      $pic = esc_url_raw((string) $raw['picture']['data']['url']);
    }

    return [
      'id'      => $id,
      'email'   => $email,   // May be empty if not granted or not verified
      'name'    => $name,
      'picture' => $pic,
    ];
  }
}