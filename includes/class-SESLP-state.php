<?php
/**
 * OAuth state token manager.
 *
 * Responsible for:
 * - generating per-provider CSRF state tokens,
 * - storing tokens using WordPress transients,
 * - validating tokens on callback (one-time use),
 * - providing configurable expiration via filters.
 */

declare(strict_types=1);
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Handle creation and validation of OAuth2 state tokens.
 *
 * Ensures CSRF protection for cross-site OAuth redirects by issuing
 * short-lived, single-use tokens tied to a provider.
 */
final class SESLP_State {
  private const KEY_PREFIX   = 'seslp_state_';
  private const DEFAULT_TTL  = 10 * MINUTE_IN_SECONDS;

  /**
   * Create and persist a new state token for a provider.
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
   * Validate a state token for a provider.
   *
   * Tokens are single-use and removed upon successful validation.
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
   * Backward-compatible alias for create().
   *
   * @deprecated Use SESLP_State::create() instead.
   *
   * @param string $provider
   * @return string
   */
  public static function generate(string $provider): string {
    return self::create($provider);
  }

  /**
   * Store a state token using a transient key.
   *
   * @param string $provider
   * @param string $state
   * @param int    $ttl
   * @return void
   */
  private static function store(string $provider, string $state, int $ttl): void {
    set_transient(self::build_key($provider, $state), time(), $ttl);
  }

  /**
   * Build a unique transient key for the provider/state pair.
   *
   * @param string $provider
   * @param string $state
   * @return string
   */
  private static function build_key(string $provider, string $state): string {
    return self::KEY_PREFIX . $provider . '_' . $state;
  }

  /**
   * Generate a URL-safe random token string.
   *
   * @return string
   */
  private static function generate_token(): string {
    // Short, URL-safe random string (no special chars)
    return wp_generate_password(12, false);
  }

  /**
   * Get token time-to-live (TTL) in seconds.
   *
   * Allows customization via the `seslp_state_ttl` filter.
   *
   * @param string $provider
   * @return int
   */
  private static function get_ttl(string $provider): int {
    $ttl = (int) apply_filters('seslp_state_ttl', self::DEFAULT_TTL, $provider);

    return ($ttl > 0) ? $ttl : self::DEFAULT_TTL;
  }
}