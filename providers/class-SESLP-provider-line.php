<?php
/**
 * Line Provider (implements SESLP_Provider_Interface)
 * - Builds auth URL
 * - Exchanges code for tokens
 * - Fetches and normalizes userinfo
 */

declare(strict_types=1);

if (!defined('ABSPATH')) exit;

if (!interface_exists('SESLP_Provider_Interface')) {
  // Interface should be loaded by the main plugin before this file.
  return;
}

final class SESLP_Provider_Line implements SESLP_Provider_Interface {
  /** Provider slug */
  // private const SLUG = 'line';
  private const SLUG = LN_SLUG;

  /** Cached registry config */
  private array $cfg;

  public function __construct() {
    $this->cfg = class_exists('SESLP_Providers_Registry') ? (SESLP_Providers_Registry::get(self::SLUG) ?: []) : [];
  }

  /** Build the authorization URL for Line */
  public function get_auth_url(): string {
    $client_id = class_exists('SESLP_Helpers') ? SESLP_Helpers::get_client_id(self::SLUG) : '';
    if ($client_id === '') return '#';

    $auth_base = (string)($this->cfg['auth_url'] ?? 'https://access.line.me/oauth2/v2.1/authorize');
    // Line scopes are space-separated. For email we need openid + email; profile gives name/picture.
    $scopes    = $this->cfg['scopes'] ?? ['profile','openid','email'];
    $scope_str = implode(' ', array_map('sanitize_text_field', $scopes));

    // CSRF state
    if (!class_exists('SESLP_State')) return '#';
    $state = SESLP_State::create(self::SLUG);

    $args = [
      'response_type' => 'code',
      'client_id'     => $client_id,             // Channel ID
      'redirect_uri'  => $this->get_redirect_uri(),
      'state'         => $state,
      'scope'         => $scope_str,
    ];

    return add_query_arg($args, $auth_base);
  }

  /** Compute the redirect/callback URI (?social_login=line) */
  public function get_redirect_uri(): string {
    return add_query_arg(['social_login' => self::SLUG], home_url('/'));
  }

  /** Exchange authorization code for tokens (state already validated in Auth) */
  public function exchange_code(string $code, string $state): array {
    if ($code === '') return [];

    $client_id     = class_exists('SESLP_Helpers') ? SESLP_Helpers::get_client_id(self::SLUG) : '';
    $client_secret = class_exists('SESLP_Helpers') ? SESLP_Helpers::get_client_secret(self::SLUG) : '';
    if ($client_id === '' || $client_secret === '') return [];

    $token_url = (string)($this->cfg['token_url'] ?? 'https://api.line.me/oauth2/v2.1/token');

    $body = [
      'grant_type'   => 'authorization_code',
      'code'         => $code,
      'redirect_uri' => $this->get_redirect_uri(),
      'client_id'    => $client_id,
      'client_secret'=> $client_secret,
    ];

    $resp = wp_remote_post($token_url, [
      'timeout' => 15,
      'headers' => [ 'Content-Type' => 'application/x-www-form-urlencoded' ],
      'body'    => $body,
    ]);
    if (is_wp_error($resp)) return [];
    $data = json_decode(wp_remote_retrieve_body($resp), true);
    return is_array($data) ? $data : [];
  }

  /** Fetch raw userinfo using access token */
  public function fetch_userinfo(string $access_token): array {
    if ($access_token === '') return [];

    $userinfo_url = (string)($this->cfg['userinfo_url'] ?? 'https://api.line.me/v2/profile');

    $resp = wp_remote_get($userinfo_url, [
      'timeout' => 15,
      'headers' => [ 'Authorization' => 'Bearer ' . $access_token ],
    ]);
    if (is_wp_error($resp)) return [];
    $profile = json_decode(wp_remote_retrieve_body($resp), true);
    return is_array($profile) ? $profile : [];
  }

  /** Normalize Line userinfo -> [id,email,name,picture]
   *  Note: email requires openid+email and must be read from id_token via verify endpoint.
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

  /** Optional: verify id_token to get email (requires openid+email scope). */
  public function fetch_email_from_id_token(string $id_token): string {
    $client_id = class_exists('SESLP_Helpers') ? SESLP_Helpers::get_client_id(self::SLUG) : '';
    if ($id_token === '' || $client_id === '') return '';

    $verify_url = 'https://api.line.me/oauth2/v2.1/verify';
    $resp = wp_remote_post($verify_url, [
      'timeout' => 15,
      'headers' => [ 'Content-Type' => 'application/x-www-form-urlencoded' ],
      'body'    => [
        'id_token'  => $id_token,
        'client_id' => $client_id,
      ],
    ]);
    if (is_wp_error($resp)) return '';
    $data = json_decode(wp_remote_retrieve_body($resp), true);
    $email = (string)($data['email'] ?? '');
    return sanitize_email($email);
  }
}