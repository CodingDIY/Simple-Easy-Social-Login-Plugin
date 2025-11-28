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
     * Return unified plugin options once per request.
     * Ensures a consistent array shape even if the option is missing or corrupted.
     */
    private static function options(): array {
      if (self::$options_cache) {
        return self::$options_cache;
      }

      $raw = get_option(defined('SESLP_OPT_KEY') ? SESLP_OPT_KEY : 'seslp_options', []);
      self::$options_cache = is_array($raw) ? $raw : [];

      return self::$options_cache;
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
  }
}