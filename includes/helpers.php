<?php
 /*
  * Shared helper utilities for SESLP
  */

declare(strict_types=1); 
if (!defined('ABSPATH')) {
  exit;
}

if (!class_exists('SESLP_Helpers')) {
  final class SESLP_Helpers {
    /** Cached options to avoid repeated lookups */
    private static array $options_cache = [];

    /**
     * Return default plugin options with proper structure.
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
     * Return unified plugin options once per request.
     * Ensures a consistent array shape even if the option is missing or corrupted.
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

    /** Public accessor for plugin options (cached per-request) */
    public static function get_options(): array {
      return self::options();
    }

    /** Read any provider option from unified options array */
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

    /** Convenience: get provider client_id */
    public static function get_client_id(string $provider): string {
      return self::get_provider_option($provider, 'client_id', '');
    }

    /** Convenience: get provider client_secret */
    public static function get_client_secret(string $provider): string {
      return self::get_provider_option($provider, 'client_secret', '');
    }

    /** Get a sanitized config value from a provider config array */
    public static function get_config_string(array $config, string $key, string $default): string {
      $value = $config[$key] ?? $default;
      $value = is_string($value) ? $value : (string) $value;

      return sanitize_text_field($value);
    }

    /** Retrieve and sanitize scopes with a safe fallback */
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
     * This is intended for read-only UI messaging such as login error notices.
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