<?php
/**
 * Redirect helper
 * - Computes the URL to send users to after social login
 */
declare(strict_types=1);
if (!defined('ABSPATH')) exit;

final class SESLP_Redirect {
  /**
   * Determine post-login redirect URL based on plugin settings.
   * Default: front page.
   */
  public static function after_login_url(?WP_User $user = null): string {
    $opts = get_option('seslp_options', []);
    $mode = isset($opts['redirect']['mode']) ? (string) $opts['redirect']['mode'] : 'front';

    // Default to front page
    $url = home_url('/');

    switch ($mode) {
      case 'dashboard':
        $url = admin_url();
        break;

      case 'profile':
        $url = admin_url('profile.php');
        break;

      case 'custom':
        $raw  = trim((string)($opts['redirect']['custom_url'] ?? ''));
        $safe = $raw !== '' ? esc_url_raw($raw) : '';
        $url  = $safe !== '' ? $safe : home_url('/');
        break;

      case 'front':
      default:
        $url = home_url('/');
        break;
    }

    SESLP_Logger::debug('Redirect decision', [
      'mode'    => $mode,
      'user_id' => $user ? (int)$user->ID : null,
      'url'     => $url,
    ]);

    return $url;
  }
}