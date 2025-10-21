<?php
/**
 * Provider registry
 * - Central place for static metadata (auth URLs, token URLs, userinfo URLs, scopes, labels).
 * - Keeps provider-specific info out of the main plugin.
 */

if (!defined('ABSPATH')) exit;

final class SESLP_Providers_Registry {
  /**
   * Return static configuration for all supported providers.
   *
   * @return array<string, array<string, mixed>>
   */
  public static function all(): array {
    return [
      'google' => [
        'auth_url'      => 'https://accounts.google.com/o/oauth2/v2/auth',
        'token_url'     => 'https://oauth2.googleapis.com/token',
        'userinfo_url'  => 'https://www.googleapis.com/oauth2/v3/userinfo',
        'scopes'        => ['openid', 'email', 'profile'],
      ],
      'facebook' => [
        'auth_url'     => 'https://www.facebook.com/v18.0/dialog/oauth',
        'token_url'    => 'https://graph.facebook.com/v18.0/oauth/access_token',
        'userinfo_url' => 'https://graph.facebook.com/v18.0/me',
        'scopes'       => ['email','public_profile'],
      ],
      'linkedin' => [
        'auth_url'      => 'https://www.linkedin.com/oauth/v2/authorization',
        'token_url'     => 'https://www.linkedin.com/oauth/v2/accessToken',
        'userinfo_url'  => 'https://api.linkedin.com/v2/me',
        'scopes'        => ['r_liteprofile', 'r_emailaddress'],
      ],
      'naver' => [
        'auth_url'      => 'https://nid.naver.com/oauth2.0/authorize',
        'token_url'     => 'https://nid.naver.com/oauth2.0/token',
        'userinfo_url'  => 'https://openapi.naver.com/v1/nid/me',
        'scopes'        => [], // Naver doesn’t require explicit scopes
      ],
      'kakao' => [
        'auth_url'      => 'https://kauth.kakao.com/oauth/authorize',
        'token_url'     => 'https://kauth.kakao.com/oauth/token',
        'userinfo_url'  => 'https://kapi.kakao.com/v2/user/me',
        'scopes'        => ['account_email','profile_nickname','profile_image'],
      ],
      'line' => [
        'auth_url'      => 'https://access.line.me/oauth2/v2.1/authorize',
        'token_url'     => 'https://api.line.me/oauth2/v2.1/token',
        'userinfo_url'  => 'https://api.line.me/v2/profile',
        'scopes'        => ['profile','openid','email'],
      ],
    ];
  }

  /**
   * Return the list of supported provider slugs.
   *
   * @return array<int,string>
   */
  public static function list(): array {
    // Single source of truth: derive from `all()`
    return array_keys(self::all());
  }

  /**
   * Return config for a single provider.
   *
   * @param string $provider
   * @return array<string, mixed>
   */
  public static function get(string $provider): array {
    $all = self::all();
    return $all[$provider] ?? [];
  }

  /**
   * Base UI labels for client credentials.
   *
   * @return array{id:string, secret:string}
   */
  public static function base_labels(): array {
    return [
      'id'     => 'Client ID',
      'secret' => 'Client Secret',
    ];
  }
  
  /**
   * Provider-specific label overrides (only keys that differ from base).
   *
   * @return array<string, array<string,string>>
   */
  public static function label_overrides(): array {
    return [
      'facebook' => ['id' => 'App ID',        'secret' => 'App Secret'],
      'kakao'    => ['id' => 'REST API Key'], // secret same as base
      'line'     => ['id' => 'Channel ID',    'secret' => 'Channel Secret'],
      // 'weibo'    => ['id' => 'App Key',       'secret' => 'App Secret'],
      // google, naver use base labels
    ];
  }
}