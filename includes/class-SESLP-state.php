<?php
/**
 * State manager
 * - Creates and validates per-provider OAuth state tokens
 * - Uses WP transients for temporary storage (10 minutes default)
 */

declare(strict_types=1);
if (!defined('ABSPATH')) {
  exit;
}

final class SESLP_State {
  private const KEY_PREFIX   = 'seslp_state_';
  private const DEFAULT_TTL  = 10 * MINUTE_IN_SECONDS;

  /**
   * Create and store a state token for a given provider.
   *
   * @param string $provider
   * @return string Generated state token
   */
  public static function create(string $provider): string {
    $state = self::generate_token();
    $ttl   = self::get_ttl($provider);

    self::store($provider, $state, $ttl);

    SESLP_Logger::debug('State created', [
      'provider' => $provider,
      'state'    => SESLP_Logger::mask_generic($state, 4, 4),
      'ttl'      => $ttl . 's',
    ]);

    return $state;
  }

  /**
   * Validate a state token for a given provider.
   *
   * @param string $provider
   * @param string $state
   * @return bool True if valid, false otherwise
   */
  public static function validate(string $provider, string $state): bool {
    if ($state === '') {
      SESLP_Logger::warning('State validation failed: empty', [
        'provider' => $provider,
      ]);
      return false;
    }

    $key   = self::build_key($provider, $state);
    $valid = (bool) get_transient($key);

    if ($valid) {
      // one-time use
      delete_transient($key);
      SESLP_Logger::debug('State validated', [
        'provider' => $provider,
        'state'    => SESLP_Logger::mask_generic($state, 4, 4),
      ]);
    } else {
      SESLP_Logger::warning('State validation failed: not found/expired', [
        'provider' => $provider,
        'state'    => SESLP_Logger::mask_generic($state, 4, 4),
      ]);
    }

    return $valid;
  }

  /**
   * Generate and persist an OAuth2 CSRF state token.
   *
   * @deprecated Use SESLP_State::create() instead. This method remains for backward compatibility.
   *
   * @param string $provider
   * @return string
   */
  public static function generate(string $provider): string {
    return self::create($provider);
  }

  /**
   * Store a state token as a transient.
   *
   * @param string $provider
   * @param string $state
   * @param int    $ttl
   */
  private static function store(string $provider, string $state, int $ttl): void {
    set_transient(self::build_key($provider, $state), time(), $ttl);
  }

  /**
   * Build the transient key for a provider/state pair.
   *
   * @param string $provider
   * @param string $state
   * @return string
   */
  private static function build_key(string $provider, string $state): string {
    return self::KEY_PREFIX . $provider . '_' . $state;
  }

  /**
   * Generate the raw state token string.
   *
   * @return string
   */
  private static function generate_token(): string {
    // Short, URL-safe random string (no special chars)
    return wp_generate_password(12, false);
  }

  /**
   * Get TTL (in seconds) for a given provider.
   * Allows customization via `seslp_state_ttl` filter.
   *
   * @param string $provider
   * @return int
   */
  private static function get_ttl(string $provider): int {
    $ttl = (int) apply_filters('seslp_state_ttl', self::DEFAULT_TTL, $provider);

    return ($ttl > 0) ? $ttl : self::DEFAULT_TTL;
  }
}