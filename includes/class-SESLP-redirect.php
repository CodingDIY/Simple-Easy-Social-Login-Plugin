<?php
/**
 * Post-login redirect resolver.
 *
 * Responsible for:
 * - determining the correct redirect destination after social login,
 * - supporting multiple redirect modes (front, dashboard, profile, custom),
 * - validating and sanitizing custom redirect URLs,
 * - providing a safe fallback when invalid URLs are detected.
 */
declare(strict_types=1);
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Resolve redirect URLs after successful authentication.
 *
 * This class centralizes redirect logic to ensure consistent behavior
 * across all providers and login flows.
 */
final class SESLP_Redirect {
  /**
   * Determine the final redirect URL after login.
   *
   * Applies plugin settings, filter overrides, and WordPress validation
   * to ensure a safe and expected redirect destination.
   *
   * @param WP_User|null $user
   * @return string
   */
  public static function after_login_url(?WP_User $user = null): string {
    $opts = SESLP_Helpers::get_options();
    $mode = isset($opts['redirect']['mode']) ? (string) $opts['redirect']['mode'] : 'front';

    // Default fallback: front page
    $fallback = home_url('/');

    // Compute base URL from mode
    $url = self::compute_mode_url($mode, $opts, $fallback);

    // Allow other code to adjust redirect URL
    $url = (string) apply_filters('seslp_after_login_url', $url, $user, $mode, $opts);

    // Final safety check: ensure URL is a valid redirect, otherwise use fallback
    $url = wp_validate_redirect($url, $fallback);

    SESLP_Logger::debug('Redirect decision', [
      'mode'     => $mode,
      'user_id'  => $user ? (int) $user->ID : null,
      'url'      => $url,
      'fallback' => $fallback,
    ]);

    return $url;
  }

  /**
   * Compute the base redirect URL based on the selected mode.
   *
   * Supported modes:
   * - front: homepage
   * - dashboard: WordPress admin dashboard
   * - profile: user profile page
   * - custom: user-defined URL
   *
   * @param string $mode
   * @param array  $opts
   * @param string $fallback
   * @return string
   */
  private static function compute_mode_url(string $mode, array $opts, string $fallback): string {
    switch ($mode) {
      case 'dashboard':
        return admin_url();

      case 'profile':
        return admin_url('profile.php');

      case 'custom':
        $raw = trim((string) ($opts['redirect']['custom_url'] ?? ''));
        if ($raw === '') {
          return $fallback;
        }
        $safe = esc_url_raw($raw);
        return $safe !== '' ? $safe : $fallback;

      case 'front':
      default:
        return $fallback;
    }
  }
}