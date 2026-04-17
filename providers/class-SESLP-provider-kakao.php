<?php
/**
 * Kakao OAuth provider implementation.
 *
 * Responsible for:
 * - building the Kakao authorization URL,
 * - exchanging authorization codes for access tokens,
 * - fetching user profile data from Kakao APIs,
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
 * Kakao provider adapter.
 *
 * Implements the SESLP provider interface so the authentication flow
 * can remain provider-agnostic across the plugin.
 */
final class SESLP_Provider_Kakao implements SESLP_Provider_Interface {
  /** Provider slug */
  private const SLUG = SESLP_KA_SLUG;

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
      $this->client_id     = (string) SESLP_Helpers::get_client_id(self::SLUG); // REST API Key
      $this->client_secret = (string) SESLP_Helpers::get_client_secret(self::SLUG);
    }
  }

  /**
   * Build the Kakao authorization URL.
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

    $auth_base = SESLP_Helpers::get_config_string($this->cfg, 'auth_url', 'https://kauth.kakao.com/oauth/authorize');
    // Kakao uses space-separated scopes
    $scope_str = implode(' ', SESLP_Helpers::get_scopes($this->cfg, ['account_email', 'profile_nickname', 'profile_image']));

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

    if ($this->client_id === '') {
      return [];
    }

    $token_url = SESLP_Helpers::get_config_string($this->cfg, 'token_url', 'https://kauth.kakao.com/oauth/token');

    $body = [
      'grant_type'   => 'authorization_code',
      'client_id'    => $this->client_id,
      'redirect_uri' => $this->get_redirect_uri(),
      'code'         => $code,
    ];
    if ($this->client_secret !== '') {
      $body['client_secret'] = $this->client_secret;
    }

    $resp = wp_remote_post($token_url, [
      'timeout' => 15,
      'body'    => $body,
    ]);
    if (is_wp_error($resp)) {
      return [];
    }
    $data = json_decode(wp_remote_retrieve_body($resp), true);

    if (class_exists('SESLP_Logger')) {
      SESLP_Logger::debug('Kakao token response', [
        'has_access_token' => is_array($data) && isset($data['access_token']),
      ]);
    }
    
    return is_array($data) ? $data : [];
  }

  /**
   * Fetch raw user profile data from Kakao.
   *
   * @param string $access_token
   * @return array<string, mixed>
   */
  public function fetch_userinfo(string $access_token): array {
    if ($access_token === '') {
      return [];
    }

    $userinfo_url = SESLP_Helpers::get_config_string($this->cfg, 'userinfo_url', 'https://kapi.kakao.com/v2/user/me');

    $resp = wp_remote_get($userinfo_url, [
      'timeout' => 15,
      'headers' => [
        'Authorization' => 'Bearer ' . $access_token,
      ],
    ]);
    if (is_wp_error($resp)) {
      return [];
    }
    $data = json_decode(wp_remote_retrieve_body($resp), true);
    return is_array($data) ? $data : [];
  }

  /**
   * Normalize Kakao user data into a standard structure.
   *
   * @param array<string, mixed> $raw
   * @return array{id:string,email:string,name:string,picture:string}
   */
  public function normalize_userinfo(array $raw): array {
    // Kakao returns: id, kakao_account[email, profile[nickname, profile_image_url]]
    $id  = sanitize_text_field((string)($raw['id'] ?? ''));
    $acc = (array)($raw['kakao_account'] ?? []);

    $email = sanitize_email((string)($acc['email'] ?? ''));
    $name  = '';
    $pic   = '';

    if (!empty($acc['profile']) && is_array($acc['profile'])) {
      $name = sanitize_text_field((string)($acc['profile']['nickname'] ?? ''));
      $pic  = esc_url_raw((string)($acc['profile']['profile_image_url'] ?? ''));
    }

    return [
      'id'      => $id,
      'email'   => $email,
      'name'    => $name,
      'picture' => $pic,
    ];
  }
}