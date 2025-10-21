<?php
/**
 * Front-end assets module
 * - Registers & enqueues public CSS/JS with cache-busting
 */

declare(strict_types=1);
if (!defined('ABSPATH')) exit;

final class SESLP_Assets {
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

    wp_register_style('seslp-front', $plugin->url . $css_rel, [], $css_ver);
    wp_register_script('seslp-front', $plugin->url . $js_rel, ['jquery'], $js_ver, true);

    // Expose minimal bootstrap data
    wp_localize_script('seslp-front', 'SESLP', [
      'ajaxUrl' => admin_url('admin-ajax.php'),
      'version' => SESLP_Plugin::VERSION,
    ]);
  }

  /** Enqueue on front/login */
  public function enqueue_front(): void {
    wp_enqueue_style('seslp-front');
    wp_enqueue_script('seslp-front');
  }
}