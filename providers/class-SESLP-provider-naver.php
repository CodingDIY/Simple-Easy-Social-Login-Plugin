<?php
/**
 * Naver Provider (implements SESLP_Provider_Interface)
 * - Builds auth URL
 * - Exchanges code for tokens
 * - Fetches and normalizes userinfo
 */

declare(strict_types=1);

if (!defined('ABSPATH')) exit;

if (!interface_exists('SESLP_Provider_Interface')) {
  // Ensure interface is loaded first
  return;
}

final class SESLP_Provider_Naver implements SESLP_Provider_Interface {
  /** Provider slug */
  // private const SLUG = 'naver';
  private const SLUG = NV_SLUG;

  /** Cached registry config */
  private array $cfg;

  public function __construct() {
    $this->cfg = class_exists('SESLP_Providers_Registry') ? (SESLP_Providers_Registry::get(self::SLUG) ?: []) : [];
  }

  /** Build the authorization URL for Naver */
  public function get_auth_url(): string {
    $client_id = $this->get_client_id();
    if ($client_id === '') return '#';

    $auth_base = (string)($this->cfg['auth_url'] ?? 'https://nid.naver.com/oauth2.0/authorize');

    // CSRF state
    if (!class_exists('SESLP_State')) return '#';
    $state = SESLP_State::create(self::SLUG);

    $args = [
      'response_type' => 'code',
      'client_id'     => $client_id,
      'redirect_uri'  => $this->get_redirect_uri(),
      'state'         => $state,
    ];
    return add_query_arg($args, $auth_base);
  }

  /** Compute the redirect/callback URI (?social_login=naver) */
  public function get_redirect_uri(): string {
    return add_query_arg(['social_login' => self::SLUG], home_url('/'));
  }

  /** Exchange authorization code for tokens */
  public function exchange_code(string $code, string $state): array {
    if ($code === '' || $state === '') return [];
    if (!class_exists('SESLP_State') || !SESLP_State::validate(self::SLUG, $state)) {
      return [];
    }

    $client_id     = $this->get_client_id();
    $client_secret = $this->get_client_secret();
    if ($client_id === '' || $client_secret === '') return [];

    $token_url = (string)($this->cfg['token_url'] ?? 'https://nid.naver.com/oauth2.0/token');

    $resp = wp_remote_post($token_url, [
      'timeout' => 15,
      'body' => [
        'grant_type'    => 'authorization_code',
        'client_id'     => $client_id,
        'client_secret' => $client_secret,
        'code'          => $code,
        'state'         => $state,
        'redirect_uri'  => $this->get_redirect_uri(),
      ],
    ]);
    if (is_wp_error($resp)) return [];
    $data = json_decode(wp_remote_retrieve_body($resp), true);
    return is_array($data) ? $data : [];
  }

  /** Fetch raw userinfo using access token */
  public function fetch_userinfo(string $access_token): array {
    if ($access_token === '') return [];
    $userinfo_url = (string)($this->cfg['userinfo_url'] ?? 'https://openapi.naver.com/v1/nid/me');
    $resp = wp_remote_get($userinfo_url, [
      'timeout' => 15,
      'headers' => ['Authorization' => 'Bearer ' . $access_token],
    ]);
    if (is_wp_error($resp)) return [];
    $data = json_decode(wp_remote_retrieve_body($resp), true);
    return is_array($data) ? $data : [];
  }

  /** Normalize Naver userinfo -> [id,email,name,picture] */
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

  // -------------------------
  // Internal helpers
  // -------------------------

  /** Read client_id from unified options array */
  private function get_client_id(): string {
    $opts = get_option('seslp_options', []);
    return trim((string)($opts['providers'][self::SLUG]['client_id'] ?? ''));
  }

  /** Read client_secret from unified options array */
  private function get_client_secret(): string {
    $opts = get_option('seslp_options', []);
    return trim((string)($opts['providers'][self::SLUG]['client_secret'] ?? ''));
  }
}