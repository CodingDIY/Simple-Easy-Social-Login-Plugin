<?php
/**
 * LinkedIn Provider (implements SESLP_Provider_Interface)
 * - Builds auth URL
 * - Exchanges code for tokens
 * - Fetches and normalizes userinfo (name, email, avatar)
 */

declare(strict_types=1);
if (!defined('ABSPATH')) exit;

if (!interface_exists('SESLP_Provider_Interface')) {
  // Interface should be loaded by the main plugin before this file.
  return;
}

final class SESLP_Provider_Linkedin implements SESLP_Provider_Interface {
  public const SLUG = 'linkedin';

  /** Cached registry config */
  private array $cfg;

  public function __construct() {
    $this->cfg = class_exists('SESLP_Providers_Registry') ? (SESLP_Providers_Registry::get(self::SLUG) ?: []) : [];
  }

  /** Build the authorization URL for LinkedIn */
  public function get_auth_url(): string {
    $client_id = class_exists('SESLP_Helpers') ? SESLP_Helpers::get_client_id(self::SLUG) : '';
    if ($client_id === '') return '#';

    $auth_base = (string)($this->cfg['auth_url'] ?? 'https://www.linkedin.com/oauth/v2/authorization');
    $scopes    = $this->cfg['scopes'] ?? ['r_liteprofile', 'r_emailaddress'];
    $scope_str = implode(' ', array_map('sanitize_text_field', $scopes));

    $state = class_exists('SESLP_State') ? SESLP_State::generate(self::SLUG) : wp_create_nonce('seslp_'.$client_id);

    $query = http_build_query([
      'response_type' => 'code',
      'client_id'     => $client_id,
      'redirect_uri'  => $this->get_redirect_uri(),
      'state'         => $state,
      'scope'         => $scope_str,
    ], '', '&', PHP_QUERY_RFC3986);

    $final = $auth_base . '?' . $query;

    return $final;
  }

  /** Compute the redirect/callback URI (?social_login=linkedin) */
  public function get_redirect_uri(): string {
    // Force a trailing slash base URL and log the final redirect URI for debugging
    $base = trailingslashit(home_url());
    $uri  = add_query_arg(['social_login' => self::SLUG], $base);

    return $uri;
  }

  /** Exchange authorization code for tokens (state already validated in Auth) */
  public function exchange_code(string $code, string $state): array {
    // $state is validated in Auth; accepted here to comply with the interface
    $client_id     = class_exists('SESLP_Helpers') ? SESLP_Helpers::get_client_id(self::SLUG) : '';
    $client_secret = class_exists('SESLP_Helpers') ? SESLP_Helpers::get_client_secret(self::SLUG) : '';
    $token_url     = (string)($this->cfg['token_url'] ?? 'https://www.linkedin.com/oauth/v2/accessToken');

    if ($client_id === '' || $client_secret === '' || $code === '') {
      return ['error' => 'missing_credentials_or_code'];
    }

    $body = [
      'grant_type'    => 'authorization_code',
      'code'          => $code,
      'redirect_uri'  => $this->get_redirect_uri(),
      'client_id'     => $client_id,
      'client_secret' => $client_secret,
    ];

    $resp = wp_remote_post($token_url, [
      'timeout' => 20,
      'body'    => $body,
    ]);

    if (is_wp_error($resp)) {
      return ['error' => 'http_error', 'message' => $resp->get_error_message()];
    }

    $http = wp_remote_retrieve_response_code($resp);
    $json = json_decode((string) wp_remote_retrieve_body($resp), true);

    if ($http !== 200 || !is_array($json)) {
      return ['error' => 'invalid_token_response', 'http_code' => $http, 'raw' => $json];
    }

    $access_token = (string)($json['access_token'] ?? '');
    $expires_in   = (int)($json['expires_in'] ?? 0);

    if ($access_token === '') {
      return ['error' => 'empty_access_token', 'raw' => $json];
    }

    return [
      'access_token' => $access_token,
      'expires_in'   => $expires_in,
    ];
  }

  /** Fetch user info */
  public function fetch_userinfo(string $access_token): array {
    // If OpenID Connect scopes are present, use the OIDC userinfo endpoint first.
    $scopes   = $this->cfg['scopes'] ?? [];
    $has_oidc = is_array($scopes) && in_array('openid', $scopes, true);

    if ($has_oidc) {
      $resp = wp_remote_get('https://api.linkedin.com/v2/userinfo', [
        'timeout' => 20,
        'headers' => [
          'Authorization' => 'Bearer ' . $access_token,
        ],
      ]);

      if (is_wp_error($resp)) {
        return ['error' => 'http_error', 'message' => $resp->get_error_message()];
      }

      $code = wp_remote_retrieve_response_code($resp);
      $json = json_decode((string) wp_remote_retrieve_body($resp), true);

      if ($code === 200 && is_array($json)) {
        // OIDC claims mapping
        $id     = sanitize_text_field((string)($json['sub'] ?? ''));
        $name   = sanitize_text_field(trim((string)($json['name'] ?? (($json['given_name'] ?? '') . ' ' . ($json['family_name'] ?? '')))));
        $email  = sanitize_email((string)($json['email'] ?? ''));
        $avatar = esc_url_raw((string)($json['picture'] ?? ''));

        return [
          'id'      => $id,
          'email'   => $email,
          'name'    => $name !== '' ? $name : $id,
          'picture' => $avatar,
        ];
      }

      // If OIDC call fails/denied, fall back to legacy v2 endpoints.
    }

    return $this->fetch_userinfo_legacy($access_token);
  }

  /** Legacy v2/me + v2/emailAddress flow (requires r_liteprofile & r_emailaddress) */
  private function fetch_userinfo_legacy(string $access_token): array {
    $me_url = (string)($this->cfg['userinfo_url'] ?? 'https://api.linkedin.com/v2/me');

    // Request localized names and profile picture (highest available)
    $resp = wp_remote_get(add_query_arg([
      'projection' => '(id,localizedFirstName,localizedLastName,profilePicture(displayImage~:playableStreams))'
    ], $me_url), [
      'timeout' => 20,
      'headers' => [
        'Authorization'             => 'Bearer ' . $access_token,
        'X-Restli-Protocol-Version' => '2.0.0',
      ],
    ]);

    if (is_wp_error($resp)) {
      return ['error' => 'http_error', 'message' => $resp->get_error_message()];
    }

    $code = wp_remote_retrieve_response_code($resp);
    $json = json_decode((string) wp_remote_retrieve_body($resp), true);

    if ($code !== 200 || !is_array($json)) {
      return ['error' => 'invalid_userinfo_response', 'http_code' => $code, 'raw' => $json];
    }

    // Fetch email via legacy endpoint (existing method)
    $email = $this->fetch_email($access_token);

    return $this->normalize_userinfo($json, is_string($email) ? $email : '');
  }

  /** Fetch primary email from LinkedIn */
  private function fetch_email(string $access_token) {
    $email_url = 'https://api.linkedin.com/v2/emailAddress';
    $resp = wp_remote_get(add_query_arg([
      'q'          => 'members',
      'projection' => '(elements*(handle~))',
    ], $email_url), [
      'timeout' => 20,
      'headers' => [
        'Authorization' => 'Bearer ' . $access_token,
      ],
    ]);

    if (is_wp_error($resp)) {
      return null;
    }

    $code = wp_remote_retrieve_response_code($resp);
    $json = json_decode((string) wp_remote_retrieve_body($resp), true);
    if ($code !== 200 || !is_array($json)) {
      return null;
    }

    if (!empty($json['elements']) && is_array($json['elements'])) {
      $first = $json['elements'][0] ?? null;
      if (is_array($first) && isset($first['handle~']['emailAddress'])) {
        return sanitize_email((string)$first['handle~']['emailAddress']);
      }
    }
    return null;
  }

  /** Normalize LinkedIn user info into our standard shape */
  public function normalize_userinfo(array $me, string $email = ''): array {
    $id    = sanitize_text_field((string)($me['id'] ?? ''));
    $first = sanitize_text_field((string)($me['localizedFirstName'] ?? ''));
    $last  = sanitize_text_field((string)($me['localizedLastName'] ?? ''));
    $name  = trim($first . ' ' . $last);

    // Try to extract avatar URL (best available)
    $picture = '';
    if (!empty($me['profilePicture']['displayImage~']['elements']) && is_array($me['profilePicture']['displayImage~']['elements'])) {
      $elements = $me['profilePicture']['displayImage~']['elements'];
      $lastEl   = end($elements);
      if (is_array($lastEl) && !empty($lastEl['identifiers'][0]['identifier'])) {
        $picture = esc_url_raw((string)$lastEl['identifiers'][0]['identifier']);
      }
    }

    return [
      'id'      => $id,
      'email'   => $email,
      'name'    => $name !== '' ? $name : $id,
      'picture' => $picture,
    ];
  }
}