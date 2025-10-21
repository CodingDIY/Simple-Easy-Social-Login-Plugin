<?php
 /*
  * Shared helper utilities for SESLP
  */

if (!defined('ABSPATH')) exit;

if (!class_exists('SESLP_Helpers')) {
  final class SESLP_Helpers {
    /** Read any provider option from unified options array */
    public static function get_provider_option(string $provider, string $key, string $default = ''): string {
      $opts = get_option('seslp_options', []);
      $val  = $opts['providers'][$provider][$key] ?? $default;
      $val  = is_string($val) ? $val : (string)$val;
      return trim($val);
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