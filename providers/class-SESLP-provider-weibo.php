<?php
/**
 * Weibo Provider (implements SESLP_Provider_Interface)
 * - Builds auth URL
 * - Exchanges code for tokens
 * - Fetches and normalizes userinfo (and tries to fetch email if permission granted)
 */

declare(strict_types=1);

if (!defined('ABSPATH')) exit;

if (!interface_exists('SESLP_Provider_Interface')) {
  // Interface should be loaded by the main plugin before this file.
  return;
}

final class SESLP_Provider_Weibo implements SESLP_Provider_Interface {
  /** Provider slug */
  // private const SLUG = 'weibo';
  private const SLUG = WB_SLUG;

  /** Cached registry config */
  private array $cfg;

  public function __construct() {
    $this->cfg = class_exists('SESLP_Providers_Registry') ? (SESLP_Providers_Registry::get(self::SLUG) ?: []) : [];
  }

  /** Build the authorization URL for Weibo */
  public function get_auth_url(): string {
    $client_id = class_exists('SESLP_Helpers') ? SESLP_Helpers::get_client_id(self::SLUG) : '';
    if ($client_id === '') return '';

    $auth_base = (string)($this->cfg['auth_url'] ?? 'https://api.weibo.com/oauth2/authorize');

    // Weibo scopes are space-separated; email requires special permission and may not be available.
    $scopes    = $this->cfg['scopes'] ?? ['email'];
    $scope_str = implode(' ', array_map('sanitize_text_field', $scopes));

    if (!class_exists('SESLP_State')) return '';
    $state = SESLP_State::create(self::SLUG);

    $args = [
      'response_type' => 'code',
      'client_id'     => $client_id,      // App Key
      'redirect_uri'  => $this->get_redirect_uri(),
      'scope'         => $scope_str,
      'state'         => $state,
      // 'forcelogin' => 'true', // optional: force login screen
      // 'display'   => 'default',
    ];

    return add_query_arg($args, $auth_base);
  }

  /** Compute the redirect/callback URI (?social_login=weibo) */
  public function get_redirect_uri(): string {
    return add_query_arg(['social_login' => self::SLUG], home_url('/'));
  }

  /** Exchange authorization code for tokens (Weibo returns access_token and uid) */
  public function exchange_code(string $code, string $state): array {
    if ($code === '') return [];

    $client_id     = class_exists('SESLP_Helpers') ? SESLP_Helpers::get_client_id(self::SLUG) : '';
    $client_secret = class_exists('SESLP_Helpers') ? SESLP_Helpers::get_client_secret(self::SLUG) : '';
    if ($client_id === '' || $client_secret === '') return [];

    $token_url = (string)($this->cfg['token_url'] ?? 'https://api.weibo.com/oauth2/access_token');

    $body = [
      'grant_type'    => 'authorization_code',
      'code'          => $code,
      'client_id'     => $client_id,
      'client_secret' => $client_secret,
      'redirect_uri'  => $this->get_redirect_uri(),
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

  /** Fetch raw userinfo using access token and uid */
  public function fetch_userinfo(string $access_token): array {
    // Weibo userinfo requires both access_token and uid; we'll expect caller to pass uid via cfg or keep it externally.
    return [];
  }

  /** Normal provider interface expects just access_token; Weibo also needs uid. Provide a dedicated fetch. */
  public function fetch_userinfo_with_uid(string $access_token, string $uid): array {
    if ($access_token === '' || $uid === '') return [];

    $userinfo_url = (string)($this->cfg['userinfo_url'] ?? 'https://api.weibo.com/2/users/show.json');

    $url = add_query_arg([
      'access_token' => $access_token,
      'uid'          => $uid,
    ], $userinfo_url);

    $resp = wp_remote_get($url, [ 'timeout' => 15 ]);
    if (is_wp_error($resp)) return [];
    $profile = json_decode(wp_remote_retrieve_body($resp), true);
    return is_array($profile) ? $profile : [];
  }

  /** Try to fetch email via email endpoint (requires special permission) */
  public function fetch_email(string $access_token): string {
    if ($access_token === '') return '';

    // The /2/account/profile/email.json endpoint may require elevated permission; not all apps can use it.
    $url = add_query_arg(['access_token' => $access_token], 'https://api.weibo.com/2/account/profile/email.json');
    $resp = wp_remote_get($url, [ 'timeout' => 15 ]);
    if (is_wp_error($resp)) return '';
    $data = json_decode(wp_remote_retrieve_body($resp), true);

    // The response format can vary; try common keys
    $email = '';
    if (is_array($data)) {
      if (!empty($data['email'])) {
        $email = (string)$data['email'];
      } elseif (!empty($data[0]['email'])) { // some responses are arrays
        $email = (string)$data[0]['email'];
      }
    }
    return sanitize_email($email);
  }

  /** Normalize Weibo userinfo -> [id,email,name,picture] */
  public function normalize_userinfo(array $raw): array {
    // Weibo /2/users/show.json returns: idstr, id, screen_name, name, avatar_hd, profile_image_url ...
    $id    = sanitize_text_field((string)($raw['idstr'] ?? ($raw['id'] ?? '')));
    $name  = sanitize_text_field((string)($raw['screen_name'] ?? ($raw['name'] ?? '')));
    $pic   = esc_url_raw((string)($raw['avatar_hd'] ?? ($raw['profile_image_url'] ?? '')));

    // Email is not present here; must be fetched via fetch_email() (if permitted) or handled in the Auth layer.
    return [
      'id'      => $id,
      'email'   => '',
      'name'    => $name,
      'picture' => $pic,
    ];
  }
}