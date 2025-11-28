<?php
/**
 * Naver Provider (implements SESLP_Provider_Interface)
 * - Builds auth URL
 * - Exchanges code for tokens
 * - Fetches and normalizes userinfo
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
  exit;
}

if (!interface_exists('SESLP_Provider_Interface')) {
  // Ensure interface is loaded first
  return;
}

final class SESLP_Provider_Naver implements SESLP_Provider_Interface {
  /** Provider slug */
  private const SLUG = NV_SLUG;

  /** Cached registry config */
  private array $cfg;

  /** Cached client credentials */
  private string $client_id = '';
  private string $client_secret = '';

  public function __construct() {
    $this->cfg = class_exists('SESLP_Providers_Registry') ? ((array) SESLP_Providers_Registry::get(self::SLUG) ?: []) : [];

    if (class_exists('SESLP_Helpers')) {
      $this->client_id     = (string) SESLP_Helpers::get_client_id(self::SLUG);
      $this->client_secret = (string) SESLP_Helpers::get_client_secret(self::SLUG);
    }
  }

  /** Build the authorization URL for Naver */
  public function get_auth_url(): string {
    if ($this->client_id === '') {
      return '#';
    }

    $auth_base = $this->get_config_string('auth_url', 'https://nid.naver.com/oauth2.0/authorize');
    $scopes    = $this->get_scopes();

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

  /** Compute the redirect/callback URI (?social_login=naver) */
  public function get_redirect_uri(): string {
    return esc_url_raw(add_query_arg(['social_login' => self::SLUG], home_url('/')));
  }

  /** Exchange authorization code for tokens */
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

    $token_url = $this->get_config_string('token_url', 'https://nid.naver.com/oauth2.0/token');

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

  /** Fetch raw userinfo using access token */
  public function fetch_userinfo(string $access_token): array {
    if ($access_token === '') {
      return [];
    }
    $userinfo_url = $this->get_config_string('userinfo_url', 'https://openapi.naver.com/v1/nid/me');
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

  /** Get a config value as a sanitized string */
  private function get_config_string(string $key, string $default): string {
    $value = $this->cfg[$key] ?? $default;
    return sanitize_text_field(is_string($value) ? $value : (string) $value);
  }

  /** Retrieve and sanitize scopes with a safe fallback */
  private function get_scopes(): array {
    $scopes = $this->cfg['scopes'] ?? [];
    $scopes = is_array($scopes) ? $scopes : [$scopes];
    $scopes = array_filter(array_map('sanitize_text_field', $scopes));
    return $scopes;
  }
}