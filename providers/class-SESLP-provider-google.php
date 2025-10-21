<?php
/**
 * Google Provider (implements SESLP_Provider_Interface)
 * - Builds auth URL
 * - Exchanges code for tokens
 * - Fetches and normalizes userinfo
 */

declare(strict_types=1);

if (!defined('ABSPATH')) exit;

if (!interface_exists('SESLP_Provider_Interface')) {
  // Soft guard: interface should be loaded by the main plugin before this file.
  return;
}

final class SESLP_Provider_Google implements SESLP_Provider_Interface {
  /** Provider slug */
  // private const SLUG = 'google';
  private const SLUG = GL_SLUG;

  /** Cached registry config */
  private array $cfg;

  public function __construct() {
    $this->cfg = class_exists('SESLP_Providers_Registry') ? (SESLP_Providers_Registry::get(self::SLUG) ?: []) : [];
  }

  /** Build the authorization URL for Google */
  public function get_auth_url(): string {
    $client_id = class_exists('SESLP_Helpers') ? SESLP_Helpers::get_client_id(self::SLUG) : '';
    if ($client_id === '') return '#';

    $auth_base = (string)($this->cfg['auth_url'] ?? 'https://accounts.google.com/o/oauth2/v2/auth');
    $scopes    = $this->cfg['scopes'] ?? ['openid','email','profile'];
    $scope_str = implode(' ', array_map('sanitize_text_field', $scopes));

    // CSRF state
    if (!class_exists('SESLP_State')) return '#';
    $state = SESLP_State::create(self::SLUG);

    $args = [
      'response_type' => 'code',
      'client_id'     => $client_id,
      'redirect_uri'  => $this->get_redirect_uri(),
      'scope'         => $scope_str,
      'state'         => $state,
      'access_type'   => 'online',
      'include_granted_scopes' => 'true',
    ];
    return add_query_arg($args, $auth_base);
  }

  /** Compute the redirect/callback URI (?social_login=google) */
  public function get_redirect_uri(): string {
    return add_query_arg(['social_login' => self::SLUG], home_url('/'));
  }

  /** Exchange authorization code for tokens */
  public function exchange_code(string $code, string $state): array {
    if ($code === '' || $state === '') return [];
    if (!class_exists('SESLP_State') || !SESLP_State::validate(self::SLUG, $state)) {
      return [];
    }

    $client_id     = class_exists('SESLP_Helpers') ? SESLP_Helpers::get_client_id(self::SLUG) : '';
    $client_secret = class_exists('SESLP_Helpers') ? SESLP_Helpers::get_client_secret(self::SLUG) : '';
    if ($client_id === '' || $client_secret === '') return [];

    $token_url = (string)($this->cfg['token_url'] ?? 'https://oauth2.googleapis.com/token');

    $resp = wp_remote_post($token_url, [
      'timeout' => 15,
      'body' => [
        'code'          => $code,
        'client_id'     => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri'  => $this->get_redirect_uri(),
        'grant_type'    => 'authorization_code',
      ],
    ]);
    if (is_wp_error($resp)) return [];
    $data = json_decode(wp_remote_retrieve_body($resp), true);
    return is_array($data) ? $data : [];
  }

  /** Fetch raw userinfo using access token */
  public function fetch_userinfo(string $access_token): array {
    if ($access_token === '') return [];
    $userinfo_url = (string)($this->cfg['userinfo_url'] ?? 'https://www.googleapis.com/oauth2/v3/userinfo');
    $resp = wp_remote_get($userinfo_url, [
      'timeout' => 15,
      'headers' => ['Authorization' => 'Bearer ' . $access_token],
    ]);
    if (is_wp_error($resp)) return [];
    $data = json_decode(wp_remote_retrieve_body($resp), true);
    return is_array($data) ? $data : [];
  }

  /** Normalize Google userinfo -> [id,email,name,picture] */
  public function normalize_userinfo(array $raw): array {
    $id      = sanitize_text_field((string)($raw['sub'] ?? ''));
    $email   = sanitize_email((string)($raw['email'] ?? ''));
    $name    = sanitize_text_field((string)($raw['name'] ?? ''));
    $picture = esc_url_raw((string)($raw['picture'] ?? ''));
    return [
      'id'      => $id,
      'email'   => $email,
      'name'    => $name,
      'picture' => $picture,
    ];
  }
}