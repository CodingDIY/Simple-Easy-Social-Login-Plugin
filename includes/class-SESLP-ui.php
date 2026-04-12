<?php
/**
 * UI module
 * - Shortcode and Login/Register buttons rendering
 */

declare(strict_types=1);
if (!defined('ABSPATH')) {
  exit;
}

final class SESLP_UI {
  /** Register UI hooks */
  public function register(): void {
    add_shortcode('se_social_login', [$this, 'render_buttons_shortcode']);
    add_action('login_form', [$this, 'render_buttons_on_login']);
    add_action('register_form', [$this, 'render_buttons_on_login']);
  }

  /** Shortcode: [se_social_login] */
  public function render_buttons_shortcode(array $atts = []): string {
    return $this->render_buttons_html();
  }

  /** Output on core login/register screens (conditioned by option) */
  public function render_buttons_on_login(): void {
    $opts         = SESLP_Helpers::get_options();
    $should_show  = (bool) ($opts['ui']['show_on_login'] ?? 1);

    // Allow filters to control whether buttons are shown on wp-login.php
    $should_show  = (bool) apply_filters('seslp_show_login_buttons', $should_show, $opts);

    if ($should_show) {
      echo wp_kses_post($this->render_buttons_html());
    }
  }

  /** Shared renderer that loads the template */
  private function render_buttons_html(): string {
    $plugin = SESLP_Plugin::instance();

    // Default template inside the plugin
    $default_template = $plugin->dir . 'templates/social-buttons.php';

    // Allow themes/other plugins to override the template path
    $template = (string) apply_filters('seslp_social_buttons_template', $default_template);

    // Fallback to default if override does not exist
    if (!file_exists($template)) {
      $template = $default_template;
    }

    // If still not found, bail out
    if (!file_exists($template)) {
      return '';
    }

    // Variables exposed to the template
    $providers = SESLP_Providers_Registry::list();
    $base_url  = $plugin->url; // for image paths

    // Helper closure if the template wants provider-specific auth URLs
    $auth_url = function (string $provider): string {
      return SESLP_Plugin::instance()->auth_url($provider);
    };

    ob_start();
    include $template; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingInclude

    return (string) ob_get_clean();
  }
}