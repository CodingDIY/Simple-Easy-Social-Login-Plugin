<?php
/**
 * State manager
 * - Creates and validates per-provider OAuth state tokens
 * - Uses WP transients for temporary storage (10 minutes default)
 */

if (!defined('ABSPATH')) exit;

final class SESLP_State {
  /**
   * Create and store a state token for a given provider.
   *
   * @param string $provider
   * @return string Generated state token
   */
  public static function create(string $provider): string {
    $state = wp_generate_uuid4();
    set_transient('seslp_state_' . $provider . '_' . $state, time(), 10 * MINUTE_IN_SECONDS);

    SESLP_Logger::debug('State created', [
      'provider' => $provider,
      'state'    => SESLP_Logger::mask_generic($state, 4, 4),
      'ttl'      => '10min',
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
    $key = 'seslp_state_' . $provider . '_' . $state;
    $valid = (bool) get_transient($key);

    if ($valid) {
      delete_transient($key); // one-time use
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
}