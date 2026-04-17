<?php
/**
 * Naver OAuth provider implementation.
 *
 * Responsible for:
 * - building the Naver authorization URL,
 * - exchanging authorization codes for access tokens,
 * - fetching user profile data from Naver APIs,
 * - normalizing provider-specific user data into a unified structure.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
  exit;
}

if (!interface_exists('SESLP_Provider_Interface')) {
  // Ensure interface is loaded first
  return;
}

/**
 * Naver provider adapter.
 *
 * Implements the SESLP provider interface so the authentication flow
 * can remain provider-agnostic across the plugin.
 */
final class SESLP_Provider_Naver implements SESLP_Provider_Interface {
  /** Provider slug */
  private const SLUG = SESLP_NV_SLUG;

  /** Cached registry config */
  private array $cfg;

  /** Cached client credentials */
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
    $this->cfg = class_exists('SESLP_Providers_Registry') ? ((array) SESLP_Providers_Registry::get(self::SLUG) ?: []) : [];

    if (class_exists('SESLP_Helpers')) {
      $this->client_id     = (string) SESLP_Helpers::get_client_id(self::SLUG);
      $this->client_secret = (string) SESLP_Helpers::get_client_secret(self::SLUG);
    }
  }

  /**
   * Build the Naver authorization URL.
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

    $auth_base = SESLP_Helpers::get_config_string($this->cfg, 'auth_url', 'https://nid.naver.com/oauth2.0/authorize');
    $scopes    = SESLP_Helpers::get_scopes($this->cfg, []);

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
    ];
    if ($scopes) {
      // Naver accepts space-delimited scopes; empty list means default app scopes.
      $args['scope'] = implode(' ', $scopes);
    }

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
   * Validates the CSRF state token before performing the token request.
   *
   * @param string $code
   * @param string $state
   * @return array<string, mixed>
   */
  public function exchange_code(string $code, string $state): array {
    if ($code === '' || $state === '') {
      return [];
    }
    if (!class_exists('SESLP_State') || !SESLP_State::validate(self::SLUG, $state)) {
      return [];
    }

    if ($this->client_id === '' || $this->client_secret === '') {
      return [];
    }

    $token_url = SESLP_Helpers::get_config_string($this->cfg, 'token_url', 'https://nid.naver.com/oauth2.0/token');

    $resp = wp_remote_post($token_url, [
      'timeout' => 15,
      'body' => [
        'grant_type'    => 'authorization_code',
        'client_id'     => $this->client_id,
        'client_secret' => $this->client_secret,
        'code'          => $code,
        'state'         => $state,
        'redirect_uri'  => $this->get_redirect_uri(),
      ],
    ]);
    if (is_wp_error($resp)) {
      return [];
    }
    $data = json_decode(wp_remote_retrieve_body($resp), true);
    SESLP_Logger::debug('Naver token response', [
      'has_access_token' => isset($data['access_token']),
      'raw' => $data
    ]);
    return is_array($data) ? $data : [];
  }

  /**
   * Fetch raw user profile data from Naver.
   *
   * @param string $access_token
   * @return array<string, mixed>
   */
  public function fetch_userinfo(string $access_token): array {
    if ($access_token === '') {
      return [];
    }
    $userinfo_url = SESLP_Helpers::get_config_string($this->cfg, 'userinfo_url', 'https://openapi.naver.com/v1/nid/me');
    $resp = wp_remote_get($userinfo_url, [
      'timeout' => 15,
      'headers' => ['Authorization' => 'Bearer ' . $access_token],
    ]);
    if (is_wp_error($resp)) {
      return [];
    }
    $data = json_decode(wp_remote_retrieve_body($resp), true);
    return is_array($data) ? $data : [];
  }

  /**
   * Normalize Naver user data into a standard structure.
   *
   * @param array<string, mixed> $raw
   * @return array{id:string,email:string,name:string,picture:string}
   */
  public function normalize_userinfo(array $raw): array {
    $resp    = is_array($raw) ? ($raw['response'] ?? []) : [];
    $id      = sanitize_text_field((string)($resp['id'] ?? ''));
    $email   = sanitize_email((string)($resp['email'] ?? ''));
    $name    = sanitize_text_field((string)($resp['nickname'] ?? ($resp['name'] ?? '')));
    $picture = esc_url_raw((string)($resp['profile_image'] ?? ''));
    return [
      'id'      => $id,
      'email'   => $email,
      'name'    => $name,
      'picture' => $picture,
    ];
  }
}