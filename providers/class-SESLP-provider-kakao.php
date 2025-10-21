<?php
/**
 * Kakao Provider (implements SESLP_Provider_Interface)
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

final class SESLP_Provider_Kakao implements SESLP_Provider_Interface {
  /** Provider slug */
  // private const SLUG = 'kakao';
  private const SLUG = KA_SLUG;

  /** Cached registry config */
  private array $cfg;

  public function __construct() {
    $this->cfg = class_exists('SESLP_Providers_Registry') ? (SESLP_Providers_Registry::get(self::SLUG) ?: []) : [];
  }

  /** Build the authorization URL for Kakao */
  public function get_auth_url(): string {
    $client_id = class_exists('SESLP_Helpers') ? SESLP_Helpers::get_client_id(self::SLUG) : ''; // Kakao REST API Key
    if ($client_id === '') return '#';

    $auth_base = (string)($this->cfg['auth_url'] ?? 'https://kauth.kakao.com/oauth/authorize');
    $scopes    = $this->cfg['scopes'] ?? ['account_email','profile_nickname','profile_image'];
    // Kakao uses space-separated scopes
    $scope_str = implode(' ', array_map('sanitize_text_field', $scopes));

    // CSRF state
    if (!class_exists('SESLP_State')) return '#';
    $state = SESLP_State::create(self::SLUG);

    $args = [
      'response_type' => 'code',
      'client_id'     => $client_id,            // REST API Key
      'redirect_uri'  => $this->get_redirect_uri(),
      'state'         => $state,
      'scope'         => $scope_str,
    ];

    return add_query_arg($args, $auth_base);
  }

  /** Compute the redirect/callback URI (?social_login=kakao) */
  public function get_redirect_uri(): string {
    return add_query_arg(['social_login' => self::SLUG], home_url('/'));
  }

  /** Exchange authorization code for tokens (state already validated in Auth) */
  public function exchange_code(string $code, string $state): array {
    if ($code === '') return [];

    $client_id     = class_exists('SESLP_Helpers') ? SESLP_Helpers::get_client_id(self::SLUG) : '';        // REST API Key
    $client_secret = class_exists('SESLP_Helpers') ? SESLP_Helpers::get_client_secret(self::SLUG) : '';    // (optional) if enabled in Kakao app
    if ($client_id === '') return [];

    $token_url = (string)($this->cfg['token_url'] ?? 'https://kauth.kakao.com/oauth/token');

    $body = [
      'grant_type'   => 'authorization_code',
      'client_id'    => $client_id,
      'redirect_uri' => $this->get_redirect_uri(),
      'code'         => $code,
    ];
    if ($client_secret !== '') {
      $body['client_secret'] = $client_secret;
    }

    $resp = wp_remote_post($token_url, [
      'timeout' => 15,
      'body'    => $body,
    ]);
    if (is_wp_error($resp)) return [];
    $data = json_decode(wp_remote_retrieve_body($resp), true);
    return is_array($data) ? $data : [];
  }

  /** Fetch raw userinfo using access token */
  public function fetch_userinfo(string $access_token): array {
    if ($access_token === '') return [];

    $userinfo_url = (string)($this->cfg['userinfo_url'] ?? 'https://kapi.kakao.com/v2/user/me');

    $resp = wp_remote_get($userinfo_url, [
      'timeout' => 15,
      'headers' => [
        'Authorization' => 'Bearer ' . $access_token,
      ],
    ]);
    if (is_wp_error($resp)) return [];
    $data = json_decode(wp_remote_retrieve_body($resp), true);
    return is_array($data) ? $data : [];
  }

  /** Normalize Kakao userinfo -> [id,email,name,picture] */
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