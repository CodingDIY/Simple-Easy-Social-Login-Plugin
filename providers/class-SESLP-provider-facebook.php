<?php
/**
 * Facebook Provider (implements SESLP_Provider_Interface)
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

final class SESLP_Provider_Facebook implements SESLP_Provider_Interface {
  /** Provider slug */
  // private const SLUG = 'facebook';
  private const SLUG = FB_SLUG;

  /** Cached registry config */
  private array $cfg;

  public function __construct() {
    $this->cfg = class_exists('SESLP_Providers_Registry') ? (SESLP_Providers_Registry::get(self::SLUG) ?: []) : [];
  }

  /** Build the authorization URL for Facebook */
  public function get_auth_url(): string {
    $client_id = class_exists('SESLP_Helpers') ? SESLP_Helpers::get_client_id(self::SLUG) : '';
    if ($client_id === '') return '#';

    $auth_base = (string)($this->cfg['auth_url'] ?? 'https://www.facebook.com/v18.0/dialog/oauth');
    $scopes    = $this->cfg['scopes'] ?? ['email','public_profile'];
    $scope_str = implode(',', array_map('sanitize_text_field', $scopes)); // FB uses comma-separated scopes

    // CSRF state
    if (!class_exists('SESLP_State')) return '#';
    $state = SESLP_State::create(self::SLUG);

    $args = [
      'response_type' => 'code',
      'client_id'     => $client_id,
      'redirect_uri'  => $this->get_redirect_uri(),
      'state'         => $state,
      'scope'         => $scope_str,
      // 'auth_type'   => 'rerequest', // (optional) to re-prompt declined permissions
    ];

    return add_query_arg($args, $auth_base);
  }

  /** Compute the redirect/callback URI (?social_login=facebook) */
  public function get_redirect_uri(): string {
    return add_query_arg(['social_login' => self::SLUG], home_url('/'));
  }

  /** Exchange authorization code for tokens (state already validated in Auth) */
  public function exchange_code(string $code, string $state): array {
    if ($code === '') return [];

    $client_id     = class_exists('SESLP_Helpers') ? SESLP_Helpers::get_client_id(self::SLUG) : '';
    $client_secret = class_exists('SESLP_Helpers') ? SESLP_Helpers::get_client_secret(self::SLUG) : '';
    if ($client_id === '' || $client_secret === '') return [];

    $token_url = (string)($this->cfg['token_url'] ?? 'https://graph.facebook.com/v18.0/oauth/access_token');

    // Facebook token exchange is a GET with query parameters
    $url = add_query_arg([
      'client_id'     => $client_id,
      'client_secret' => $client_secret,
      'redirect_uri'  => $this->get_redirect_uri(),
      'code'          => $code,
    ], $token_url);

    $resp = wp_remote_get($url, [ 'timeout' => 15 ]);
    if (is_wp_error($resp)) return [];
    $data = json_decode(wp_remote_retrieve_body($resp), true);
    return is_array($data) ? $data : [];
  }

  /** Fetch raw userinfo using access token */
  public function fetch_userinfo(string $access_token): array {
    if ($access_token === '') return [];

    // Request name, email and a square profile picture URL
    $userinfo_url = (string)($this->cfg['userinfo_url'] ?? 'https://graph.facebook.com/v18.0/me');
    $url = add_query_arg([
      'fields'       => 'id,name,email,picture.type(large)',
      'access_token' => $access_token,
    ], $userinfo_url);

    $resp = wp_remote_get($url, [ 'timeout' => 15 ]);
    if (is_wp_error($resp)) return [];
    $data = json_decode(wp_remote_retrieve_body($resp), true);
    return is_array($data) ? $data : [];
  }

  /** Normalize Facebook userinfo -> [id,email,name,picture] */
  public function normalize_userinfo(array $raw): array {
    $id    = sanitize_text_field((string)($raw['id'] ?? ''));
    $email = sanitize_email((string)($raw['email'] ?? ''));
    $name  = sanitize_text_field((string)($raw['name'] ?? ''));
    $pic   = '';

    // picture structure: ['picture' => ['data' => ['url' => '...']]]
    if (!empty($raw['picture']['data']['url'])) {
      $pic = esc_url_raw((string)$raw['picture']['data']['url']);
    }

    return [
      'id'      => $id,
      'email'   => $email,   // May be empty if not granted or not verified
      'name'    => $name,
      'picture' => $pic,
    ];
  }
}