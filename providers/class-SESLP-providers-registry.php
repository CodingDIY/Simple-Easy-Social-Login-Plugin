<?php
/**
 * Static provider registry.
 *
 * Responsible for:
 * - defining built-in provider endpoint metadata,
 * - normalizing provider configuration into a predictable structure,
 * - exposing extensibility hooks for third-party providers,
 * - centralizing provider-specific credential labels for the admin UI.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
  exit;
}

/**
 * Provide normalized provider metadata for the plugin.
 *
 * This registry keeps endpoint URLs, scopes, and admin label overrides in
 * one place so provider implementations can stay lightweight.
 */
final class SESLP_Providers_Registry {
  /**
   * Cached, normalized provider registry.
   *
   * @var array<string, array<string, mixed>>|null
   */
  private static ?array $providers = null;

  /**
   * Cached, normalized label overrides.
   *
   * @var array<string, array<string, string>>|null
   */
  private static ?array $label_overrides = null;

  /**
   * Return normalized configuration for all registered providers.
   *
   * The returned array always uses provider slugs as keys.
   *
   * @return array<string, array<string, mixed>>
   */
  public static function all(): array {
    if (self::$providers !== null) {
      return self::$providers;
    }

    // Base registry defined by the plugin.
    $base = self::base_registry();

    /**
     * Filter the full providers registry before normalization.
     *
     * This allows addons to register additional providers or modify existing ones.
     *
     * @param array<string, array<string, mixed>> $base
     */
    $filtered = apply_filters('seslp_providers_registry', $base);

    // Normalize the final registry to a safe, predictable structure.
    self::$providers = self::normalize_registry(is_array($filtered) ? $filtered : []);

    return self::$providers;
  }

  /**
   * Return the built-in provider registry before filters are applied.
   *
   * @return array<string, array<string, mixed>>
   */
  private static function base_registry(): array {
    return [
      'google' => [
        'auth_url'     => 'https://accounts.google.com/o/oauth2/v2/auth',
        'token_url'    => 'https://oauth2.googleapis.com/token',
        'userinfo_url' => 'https://www.googleapis.com/oauth2/v3/userinfo',
        'scopes'       => ['openid', 'email', 'profile'],
      ],
      'facebook' => [
        'auth_url'     => 'https://www.facebook.com/v18.0/dialog/oauth',
        'token_url'    => 'https://graph.facebook.com/v18.0/oauth/access_token',
        'userinfo_url' => 'https://graph.facebook.com/v18.0/me',
        'scopes'       => ['email', 'public_profile'],
      ],
      'linkedin' => [
        'auth_url'     => 'https://www.linkedin.com/oauth/v2/authorization',
        'token_url'    => 'https://www.linkedin.com/oauth/v2/accessToken',
        'userinfo_url' => 'https://api.linkedin.com/v2/me',
        'scopes'       => ['openid', 'profile', 'email'],
      ],
      'naver' => [
        'auth_url'     => 'https://nid.naver.com/oauth2.0/authorize',
        'token_url'    => 'https://nid.naver.com/oauth2.0/token',
        'userinfo_url' => 'https://openapi.naver.com/v1/nid/me',
        // Naver does not require explicit scopes; can be extended by filters.
        'scopes'       => [],
      ],
      'kakao' => [
        'auth_url'     => 'https://kauth.kakao.com/oauth/authorize',
        'token_url'    => 'https://kauth.kakao.com/oauth/token',
        'userinfo_url' => 'https://kapi.kakao.com/v2/user/me',
        'scopes'       => ['account_email', 'profile_nickname', 'profile_image'],
      ],
      'line' => [
        'auth_url'     => 'https://access.line.me/oauth2/v2.1/authorize',
        'token_url'    => 'https://api.line.me/oauth2/v2.1/token',
        'userinfo_url' => 'https://api.line.me/v2/profile',
        'scopes'       => ['profile', 'openid', 'email'],
      ],
    ];
  }

  /**
   * Normalize the full provider registry to a safe structure.
   *
   * Invalid slugs or malformed provider configs are skipped.
   *
   * @param array<string, mixed> $registry
   * @return array<string, array<string, mixed>>
   */
  private static function normalize_registry(array $registry): array {
    $normalized = [];

    foreach ($registry as $slug => $config) {
      if (!is_string($slug) || $slug === '') {
        continue;
      }

      $slug = sanitize_key($slug);
      if ($slug === '') {
        continue;
      }

      if (!is_array($config)) {
        continue;
      }

      $provider = self::normalize_provider($config);
      if (empty($provider)) {
        continue;
      }

      $normalized[$slug] = $provider;
    }

    return $normalized;
  }

  /**
   * Normalize a single provider configuration array.
   *
   * @param array<string, mixed> $config
   * @return array<string, mixed>
   */
  private static function normalize_provider(array $config): array {
    $auth_url     = self::normalize_url($config['auth_url']     ?? '');
    $token_url    = self::normalize_url($config['token_url']    ?? '');
    $userinfo_url = self::normalize_url($config['userinfo_url'] ?? '');
    $scopes       = self::normalize_scopes($config['scopes']    ?? []);

    $out = [];

    if ($auth_url !== '') {
      $out['auth_url'] = $auth_url;
    }

    if ($token_url !== '') {
      $out['token_url'] = $token_url;
    }

    if ($userinfo_url !== '') {
      $out['userinfo_url'] = $userinfo_url;
    }

    // Always include scopes key, even if empty, for predictable access.
    $out['scopes'] = $scopes;

    return $out;
  }

  /**
   * Normalize a URL-like value from mixed input.
   *
   * @param mixed $url
   * @return string
   */
  private static function normalize_url($url): string {
    if (!is_string($url)) {
      return '';
    }

    $url = trim($url);

    return $url !== '' ? $url : '';
  }

  /**
   * Normalize provider scopes into a unique list of non-empty strings.
   *
   * Accepts either an array or a comma/whitespace separated string.
   *
   * @param mixed $scopes
   * @return array<int, string>
   */
  private static function normalize_scopes($scopes): array {
    if (is_string($scopes)) {
      // Allow comma/whitespace separated scopes in overrides.
      $scopes = preg_split('/[\s,]+/', $scopes) ?: [];
    }

    if (!is_array($scopes)) {
      return [];
    }

    $clean = [];

    foreach ($scopes as $scope) {
      if (!is_string($scope)) {
        continue;
      }

      $scope = trim($scope);

      if ($scope === '') {
        continue;
      }

      // Use key as value to de-duplicate.
      $clean[$scope] = $scope;
    }

    return array_values($clean);
  }

  /**
   * Return the list of supported provider slugs.
   *
   * @return array<int, string>
   */
  public static function list(): array {
    return array_keys(self::all());
  }

  /**
   * Return normalized configuration for a single provider.
   *
   * @param string $provider
   * @return array<string, mixed>
   */
  public static function get(string $provider): array {
    $all      = self::all();
    $provider = sanitize_key($provider);

    return $all[$provider] ?? [];
  }

  /**
   * Return the default admin UI labels for provider credentials.
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
   * Return provider-specific admin label overrides.
   *
   * Only labels that differ from the defaults are included.
   *
   * @return array<string, array<string, string>>
   */
  public static function label_overrides(): array {
    if (self::$label_overrides !== null) {
      return self::$label_overrides;
    }

    // Defaults defined by this plugin.
    $defaults = [
      'facebook' => ['id' => 'App ID',      'secret' => 'App Secret'],
      'kakao'    => ['id' => 'REST API Key'], // secret same as base
      'line'     => ['id' => 'Channel ID', 'secret' => 'Channel Secret'],
      // google, naver, linkedin use base labels
    ];

    /**
     * Filter provider-specific label overrides.
     *
     * @param array<string, array<string, string>> $defaults
     */
    $filtered = apply_filters('seslp_provider_label_overrides', $defaults);

    self::$label_overrides = self::normalize_label_overrides(
      is_array($filtered) ? $filtered : []
    );

    return self::$label_overrides;
  }

  /**
   * Normalize provider label overrides to a safe structure.
   *
   * @param array<string, mixed> $overrides
   * @return array<string, array<string, string>>
   */
  private static function normalize_label_overrides(array $overrides): array {
    $out = [];

    foreach ($overrides as $slug => $labels) {
      if (!is_string($slug) || $slug === '') {
        continue;
      }

      $slug = sanitize_key($slug);
      if ($slug === '') {
        continue;
      }

      if (!is_array($labels)) {
        continue;
      }

      $entry = [];

      if (isset($labels['id']) && is_string($labels['id'])) {
        $id = trim($labels['id']);
        if ($id !== '') {
          $entry['id'] = $id;
        }
      }

      if (isset($labels['secret']) && is_string($labels['secret'])) {
        $secret = trim($labels['secret']);
        if ($secret !== '') {
          $entry['secret'] = $secret;
        }
      }

      if (!empty($entry)) {
        $out[$slug] = $entry;
      }
    }

    return $out;
  }
}