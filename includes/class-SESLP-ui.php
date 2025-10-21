<?php
/**
 * UI module
 * - Shortcode and Login/Register buttons rendering
 */

declare(strict_types=1);
if (!defined('ABSPATH')) exit;

final class SESLP_UI {
  /** Register UI hooks */
  public function register(): void {
    add_shortcode('se_social_login', [$this, 'render_buttons_shortcode']);
    add_action('login_form',  [$this, 'render_buttons_on_login']);
    add_action('register_form', [$this, 'render_buttons_on_login']);
  }

  /** Shortcode: [se_social_login] */
  public function render_buttons_shortcode(array $atts = []): string {
    return $this->render_buttons_html();
  }

  /** Output on core login/register screens (conditioned by option) */
  public function render_buttons_on_login(): void {
    $opts = get_option('seslp_options', []);
    $show = (bool)($opts['ui']['show_on_login'] ?? 1);
    if ($show) echo $this->render_buttons_html();
  }

  /** Shared renderer that loads the template */
  private function render_buttons_html(): string {
    $plugin = SESLP_Plugin::instance();
    $tpl = $plugin->dir . 'templates/social-buttons.php';
    if (file_exists($tpl)) {
      // Variables exposed to the template
      $providers = SESLP_Providers_Registry::list();
      $base_url  = $plugin->url; // for image paths
      // Helper closure if the template wants provider-specific auth URLs
      $auth_url = function(string $provider): string {
        return SESLP_Plugin::instance()->auth_url($provider);
      };

      ob_start();
      include $tpl; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingInclude
      return (string) ob_get_clean();
    }
    return '';
  }
}