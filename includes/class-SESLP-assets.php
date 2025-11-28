<?php
/**
 * Front-end assets module
 * - Registers & enqueues public CSS/JS with cache-busting
 */

declare(strict_types=1);
if (!defined('ABSPATH')) exit;

final class SESLP_Assets {
  private const STYLE_HANDLE = 'seslp-front';
  private const SCRIPT_HANDLE = 'seslp-front';

  /** Hook registrations */
  public function register(): void {
    add_action('init', [$this, 'register_assets']);
    add_action('wp_enqueue_scripts', [$this, 'enqueue_front']);
    add_action('login_enqueue_scripts', [$this, 'enqueue_front']);
  }

  /** Register styles/scripts (filemtime versioning) */
  public function register_assets(): void {
    $plugin   = SESLP_Plugin::instance();
    $css_rel  = 'assets/css/style.css';
    $js_rel   = 'assets/js/social-login.js';

    $css_path = $plugin->dir . $css_rel;
    $js_path  = $plugin->dir . $js_rel;

    $css_ver  = file_exists($css_path) ? (string) filemtime($css_path) : SESLP_Plugin::VERSION;
    $js_ver   = file_exists($js_path)  ? (string) filemtime($js_path)  : SESLP_Plugin::VERSION;

    wp_register_style(self::STYLE_HANDLE, $plugin->url . $css_rel, [], $css_ver);
    wp_register_script(self::SCRIPT_HANDLE, $plugin->url . $js_rel, ['jquery'], $js_ver, true);

    // Expose minimal bootstrap data
    wp_localize_script('seslp-front', 'SESLP', [
      'ajaxUrl' => admin_url('admin-ajax.php'),
      'version' => SESLP_Plugin::VERSION,
    ]);
  }

  /** Enqueue on front/login */
  public function enqueue_front(): void {
    $has_active_provider = $this->has_active_provider();

    // Allow filters to override the decision
    $should = (bool) apply_filters('seslp_should_enqueue_assets', $has_active_provider);

    if (!$should) {
      return;
    }

    wp_enqueue_style(self::STYLE_HANDLE);
    wp_enqueue_script(self::SCRIPT_HANDLE);
  }

  /** Check if at least one provider has client_id + client_secret configured */
  private function has_active_provider(): bool {
    if (!class_exists('SESLP_Providers_Registry')) {
      return true;
    }

    $providers = SESLP_Providers_Registry::all();

    foreach ($providers as $slug => $class) {
      $client_id     = SESLP_Helpers::get_provider_option($slug, 'client_id', '');
      $client_secret = SESLP_Helpers::get_provider_option($slug, 'client_secret', '');

      if ($client_id !== '' && $client_secret !== '') {
        return true;
      }
    }

    return false;
  }
}