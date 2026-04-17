<?php
/**
 * Shared helper utilities for SESLP.
 *
 * Responsible for:
 * - returning normalized plugin options,
 * - reading provider-specific configuration values,
 * - sanitizing common config inputs,
 * - exposing validated public error codes for UI notices,
 * - building Freemius upgrade URLs with optional coupon support.
 */

declare(strict_types=1); 
if (!defined('ABSPATH')) {
  exit;
}

if (!class_exists('SESLP_Helpers')) {
  /**
   * Provide shared static helper methods used across the plugin.
   *
   * This class centralizes lightweight utility logic so other modules can
   * reuse consistent option access, sanitization, and URL helper behavior.
   */
  final class SESLP_Helpers {
    /**
     * Cached plugin options for the current request.
     *
     * @var array<string, mixed>
     */
    private static array $options_cache = [];

    /**
     * Return the default plugin options structure.
     *
     * @return array<string, mixed>
     */
    public static function get_default_options(): array {
      return [
        'general' => [
          'auto_create_user' => true,
        ],
        'providers' => [],
      ];
    }

    /**
     * Return normalized plugin options, cached per request.
     *
     * Ensures a consistent array shape even when the saved option is missing
     * or malformed.
     *
     * @return array<string, mixed>
     */
    private static function options(): array {
      if (self::$options_cache) {
        return self::$options_cache;
      }

      $raw = get_option(defined('SESLP_OPT_KEY') ? SESLP_OPT_KEY : 'seslp_options', []);

      // Merging with default values
      $defaults = self::get_default_options();
      self::$options_cache = wp_parse_args( 
        is_array($raw) ? $raw : [], 
        $defaults
      );

      return self::$options_cache;
    }

    /**
     * Return plugin options through the shared cached accessor.
     *
     * @return array<string, mixed>
     */
    public static function get_options(): array {
      return self::options();
    }

    /**
     * Return a provider-specific option value from the unified options array.
     *
     * @param string $provider
     * @param string $key
     * @param string $default
     * @return string
     */
    public static function get_provider_option(string $provider, string $key, string $default = ''): string {
      $provider = sanitize_key($provider);
      $key      = sanitize_key($key);

      $opts = self::options();
      $val  = $opts['providers'][$provider][$key] ?? $default;
      $val  = is_scalar($val) ? (string) $val : $default;

      /** @var string $val */
      $val = trim($val);

      return (string) apply_filters('seslp_provider_option', $val, $provider, $key, $default, $opts);
    }

    /**
     * Return the configured client ID for a provider.
     *
     * @param string $provider
     * @return string
     */
    public static function get_client_id(string $provider): string {
      return self::get_provider_option($provider, 'client_id', '');
    }

    /**
     * Return the configured client secret for a provider.
     *
     * @param string $provider
     * @return string
     */
    public static function get_client_secret(string $provider): string {
      return self::get_provider_option($provider, 'client_secret', '');
    }

    /**
     * Return a sanitized string value from a provider config array.
     *
     * @param array<string, mixed> $config
     * @param string               $key
     * @param string               $default
     * @return string
     */
    public static function get_config_string(array $config, string $key, string $default): string {
      $value = $config[$key] ?? $default;
      $value = is_string($value) ? $value : (string) $value;

      return sanitize_text_field($value);
    }

    /**
     * Return sanitized OAuth scopes with a safe fallback.
     *
     * @param array<string, mixed> $config
     * @param array<int, string>   $fallback
     * @return array<int, string>
     */
    public static function get_scopes(array $config, array $fallback): array {
      $scopes = $config['scopes'] ?? $fallback;

      if (!is_array($scopes)) {
        $scopes = [$scopes];
      }

      $scopes = array_filter(array_map('sanitize_text_field', $scopes));

      return $scopes === [] ? $fallback : $scopes;
    }

    /**
     * Read and validate a public SESLP error code from the current request.
     *
     * Intended only for read-only UI messaging such as login error notices.
     * The returned value is restricted to a known allowlist.
     *
     * @return string
     */
    public static function get_public_error_code(): string {
      if (!isset($_GET['seslp_err'])) {
        return '';
      }

      $error_code = sanitize_key(wp_unslash($_GET['seslp_err'])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display flag; validated against a strict allowlist before use.

      $allowed_error_codes = [
        'invalid_state',
        'invalid_nonce',
        'token_exchange_failed',
        'oauth_exception',
        'oauth_failed',
        'invalid_provider',
        'provider_not_allowed',
        'config_missing',
        'email_missing',
        'email_exists',
        'account_link_failed',
        'registration_disabled_by_plugin',
      ];

      return in_array($error_code, $allowed_error_codes, true) ? $error_code : '';
    }

    /**
     * Get Freemius upgrade / checkout URL with an optional coupon auto-applied.
     *
     * @param string $coupon_code Coupon code to auto-apply in the hosted checkout.
     * @return string Upgrade URL (may be empty if Freemius isn't available).
     */
    public static function get_upgrade_url(string $coupon_code = 'SESLP30'): string {
      $url = '';

      if (function_exists('simple_easy_social_login_freemius')) {
        $fs = simple_easy_social_login_freemius();

        if (is_object($fs) && method_exists($fs, 'get_upgrade_url')) {
          // get_upgrade_url() is an alias to pricing_url() in the Freemius SDK.
          $url = (string) $fs->get_upgrade_url();
        }
      }

      if ($url !== '' && $coupon_code !== '') {
        $url = add_query_arg(
          [
            'coupon' => $coupon_code,
          ],
          $url
        );
      }

      /**
       * Filter the final upgrade URL.
       *
       * @param string $url         The checkout URL.
       * @param string $coupon_code Coupon code.
       */
      return (string) apply_filters('seslp_upgrade_url', $url, $coupon_code);
    }
  }
}