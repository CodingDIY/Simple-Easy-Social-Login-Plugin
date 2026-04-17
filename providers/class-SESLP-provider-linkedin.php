<?php
/**
 * LinkedIn OAuth provider implementation.
 *
 * Responsible for:
 * - building the LinkedIn authorization URL,
 * - exchanging authorization codes for access tokens,
 * - fetching user profile data from LinkedIn APIs,
 * - supporting both OIDC and legacy LinkedIn profile flows,
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
 * LinkedIn provider adapter.
 *
 * Implements the SESLP provider interface so the authentication flow
 * can remain provider-agnostic across the plugin.
 */
final class SESLP_Provider_Linkedin implements SESLP_Provider_Interface {
  public const SLUG = SESLP_LK_SLUG;

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
    $this->cfg = class_exists('SESLP_Providers_Registry') ? ((array)SESLP_Providers_Registry::get(self::SLUG) ?: []) : [];

    if (class_exists('SESLP_Helpers')) {
      $this->client_id     = (string) SESLP_Helpers::get_client_id(self::SLUG);
      $this->client_secret = (string) SESLP_Helpers::get_client_secret(self::SLUG);
    }
  }

  /**
   * Build the LinkedIn authorization URL.
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

    $auth_base = SESLP_Helpers::get_config_string(
      $this->cfg,
      'auth_url',
      'https://www.linkedin.com/oauth/v2/authorization'
    );
    $scope_str = implode(' ', SESLP_Helpers::get_scopes($this->cfg, ['r_liteprofile', 'r_emailaddress']));

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
    $token_url = SESLP_Helpers::get_config_string(
      $this->cfg,
      'token_url',
      'https://www.linkedin.com/oauth/v2/accessToken'
    );

    if ($this->client_id === '' || $this->client_secret === '' || $code === '') {
      return ['error' => 'missing_credentials_or_code'];
    }

    $body = [
      'grant_type'    => 'authorization_code',
      'code'          => $code,
      'redirect_uri'  => $this->get_redirect_uri(),
      'client_id'     => $this->client_id,
      'client_secret' => $this->client_secret,
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

    if (class_exists('SESLP_Logger')) {
      SESLP_Logger::debug('LinkedIn token response', [
        'http_code'       => $http,
        'has_access_token'=> is_array($json) && isset($json['access_token']),
      ]);
    }

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

  /**
   * Fetch raw user profile data from LinkedIn.
   *
   * Uses the OpenID Connect userinfo endpoint when available and falls back
   * to the legacy profile/email endpoints when necessary.
   *
   * @param string $access_token
   * @return array<string, mixed>
   */
  public function fetch_userinfo(string $access_token): array {
    // If OpenID Connect scopes are present, use the OIDC userinfo endpoint first.
    $scopes   = SESLP_Helpers::get_scopes($this->cfg, ['r_liteprofile', 'r_emailaddress']);
    $has_oidc = in_array('openid', $scopes, true);

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

  /**
   * Fetch raw user data using the legacy LinkedIn v2 endpoints.
   *
   * Requires the r_liteprofile and r_emailaddress scopes.
   *
   * @param string $access_token
   * @return array<string, mixed>
   */
  private function fetch_userinfo_legacy(string $access_token): array {
    $me_url = SESLP_Helpers::get_config_string(
      $this->cfg,
      'userinfo_url',
      'https://api.linkedin.com/v2/me'
    );

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

  /**
   * Fetch the primary email address from LinkedIn.
   *
   * @param string $access_token
   * @return string|null
   */
  private function fetch_email(string $access_token): ?string {
    $email_url = SESLP_Helpers::get_config_string(
      $this->cfg,
      'email_url',
      'https://api.linkedin.com/v2/emailAddress'
    );
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

  /**
   * Normalize LinkedIn user data into a standard structure.
   *
   * @param array<string, mixed> $me
   * @param string               $email
   * @return array{id:string,email:string,name:string,picture:string}
   */
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

  /** Normalize configured scopes into a de-duplicated string list */
  // private function get_scopes(): array {
  //   $scopes = $this->cfg['scopes'] ?? ['r_liteprofile', 'r_emailaddress'];

  //   if (is_string($scopes)) {
  //     $scopes = preg_split('/[\s,]+/', $scopes) ?: [];
  //   } elseif (!is_array($scopes)) {
  //     $scopes = [];
  //   }

  //   $clean = array_filter(array_map('sanitize_text_field', $scopes));
  //   if (empty($clean)) {
  //     $clean = ['r_liteprofile', 'r_emailaddress'];
  //   }

  //   return array_values(array_unique($clean));
  // }

  /** Get a sanitized URL string from registry config with a default fallback */
  // private function get_config_url(string $key, string $default): string {
  //   $val = $this->cfg[$key] ?? '';
  //   if (!is_string($val)) {
  //     $val = '';
  //   }

  //   $url = esc_url_raw($val !== '' ? $val : $default);
  //   return $url !== '' ? $url : esc_url_raw($default);
  // }
}