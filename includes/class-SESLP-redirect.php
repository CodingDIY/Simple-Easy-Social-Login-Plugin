<?php
/**
 * Redirect helper
 * - Computes the URL to send users to after social login
 */
declare(strict_types=1);
if (!defined('ABSPATH')) {
  exit;
}

final class SESLP_Redirect {
  /**
   * Determine post-login redirect URL based on plugin settings.
   * Default: front page.
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
   * Compute base redirect URL for a given mode.
   *
   * @param string   $mode
   * @param array    $opts
   * @param string   $fallback
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