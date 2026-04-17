<?php
/**
 * Admin settings controller.
 *
 * Responsible for:
 * - registering the plugin settings menu and submenu,
 * - registering settings, sections, and provider credential fields,
 * - sanitizing option values before persistence,
 * - enqueueing admin assets for plugin settings screens,
 * - rendering the settings page template.
 */

declare(strict_types=1);
if (!defined('ABSPATH')) {
  exit;
}

require_once plugin_dir_path(__DIR__) . 'includes/launch-banner.php';

/**
 * Manage the SESLP admin settings experience.
 *
 * This class handles WordPress Settings API registration and keeps
 * settings-page responsibilities separate from the main plugin bootstrap.
 */
final class SESLP_Settings {
  /**
   * Register admin-only hooks for the settings module.
   *
   * @return void
   */
  public static function init(): void {
    if (!is_admin()) {
      return;
    }
    
    add_action('admin_menu', [self::class, 'add_settings_menu']);
    add_action('admin_init', [self::class, 'register_settings']);
    add_action('admin_enqueue_scripts', [self::class, 'enqueue_admin_assets']);
  }

  /**
   * Register the top-level admin menu and settings submenu.
   *
   * Replaces the default duplicated first submenu with a clearer label.
   *
   * @return void
   */
  public static function add_settings_menu(): void {
    $settings_slug = defined('SESLP_SETTINGS_SLUG') ? SESLP_SETTINGS_SLUG : 'seslp-settings';

    // Top-level menu
    add_menu_page(
      __( 'Simple Easy Social Login', 'simple-easy-social-login-oauth-login' ),  // Page title
      __( 'SE Social Login', 'simple-easy-social-login-oauth-login' ),           // Menu title (top-level label)
      'manage_options',                                    // Capability
      $settings_slug,                                      // Menu slug
      [ self::class, 'render_settings_page' ],             // Callback
      'dashicons-unlock',                                  // Icon
      65                                                   // Position (below Settings, above Tools typically)
    );

    // WordPress automatically creates a first submenu that mirrors the top-level label.
    // Remove that default submenu and add our own with a clearer label “Setting”.
    remove_submenu_page( 'seslp-settings', 'seslp-settings' );

    add_submenu_page(
      $settings_slug,                                               // Parent slug (this top-level menu)
      __( 'Simple Easy Social Login – Setting', 'simple-easy-social-login-oauth-login' ), // Page title for the tab
      __( 'Setting', 'simple-easy-social-login-oauth-login' ),                            // Submenu label
      'manage_options',
      $settings_slug,                                               // Same slug → same page callback
      [ self::class, 'render_settings_page' ]
    );
  }

  /**
   * Register plugin settings, sections, and provider credential fields.
   *
   * @return void
   */
  public static function register_settings(): void {
    register_setting('seslp_group', 'seslp_options', [
      'type'              => 'array',
      'sanitize_callback' => ['SESLP_Settings', 'sanitize_options'],
      'default'           => [],
    ]);

    register_setting('seslp_group', 'seslp_uninstall_remove_data', [
      'type'              => 'string',
      'sanitize_callback' => ['SESLP_Settings', 'sanitize_yes_no'],
      'default'           => '',
    ]);
    register_setting('seslp_group', 'seslp_uninstall_deep_clean', [
      'type'              => 'string',
      'sanitize_callback' => ['SESLP_Settings', 'sanitize_yes_no'],
      'default'           => '',
    ]);

    add_settings_section(
      'seslp_section_main',
      __('Providers', 'simple-easy-social-login-oauth-login'),
      function () {
        echo '<p>' . esc_html__('Enter OAuth credentials for each provider.', 'simple-easy-social-login-oauth-login') . '</p>';
      },
      'seslp-settings'
    );

    // Get provider keys for settings (minus weibo, plus linkedin) with filter hook.
    $providers = self::provider_keys();

    foreach ($providers as $prov) {
      add_settings_field(
        "seslp_{$prov}_client_id",
        /* translators: %s: Provider name (e.g., Google, Naver). */
        sprintf(esc_html__('%s Client ID', 'simple-easy-social-login-oauth-login'), ucfirst($prov)),
        function () use ($prov) {
          self::render_input($prov, 'client_id');
        },
        'seslp-settings',
        'seslp_section_main'
      );

      add_settings_field(
        "seslp_{$prov}_client_secret",
        /* translators: %s: Provider name (e.g., Google, Naver). */
        sprintf(esc_html__('%s Client Secret', 'simple-easy-social-login-oauth-login'), ucfirst($prov)),
        function () use ($prov) {
          self::render_input($prov, 'client_secret', true);
        },
        'seslp-settings',
        'seslp_section_main'
      );
    }
  }

  /**
   * Sanitize a yes/no style checkbox value.
   *
   * @param mixed $val
   * @return string
   */
  public static function sanitize_yes_no($val): string {
    return (is_string($val) && strtolower($val) === 'yes') ? 'yes' : '';
  }

  /**
   * Sanitize the full SESLP options array before saving.
   *
   * @param mixed $opts
   * @return array
   */
  public static function sanitize_options($opts): array {
    if (!is_array($opts)) {
      return [];
    }

    $sanitized = $opts;

    // General settings sanitize
    if (isset($sanitized['general']) && is_array($sanitized['general'])) {
      $sanitized['general']['auto_create_user'] = !empty($sanitized['general']['auto_create_user']);
    }

    // Sanitize provider credentials.
    if (isset($sanitized['providers']) && is_array($sanitized['providers'])) {
      foreach ($sanitized['providers'] as $provider => $fields) {
        if (!is_array($fields)) {
          continue;
        }

        $p = sanitize_key((string) $provider);

        foreach ($fields as $key => $value) {
          $k = sanitize_key((string) $key);

          // Default: treat provider fields as plain text.
          $sanitized['providers'][$p][$k] = sanitize_text_field((string) $value);
        }
      }
    }

    // Sanitize any URL-like fields if present.
    $url_keys = ['redirect_url', 'custom_url', 'url'];
    foreach ($url_keys as $url_key) {
      if (isset($sanitized[$url_key])) {
        $sanitized[$url_key] = esc_url_raw((string) $sanitized[$url_key]);
      }
    }

    return $sanitized;
  }

  /**
   * Render a provider credential input field.
   *
   * @param string $provider
   * @param string $key
   * @param bool   $password
   * @return void
   */
  private static function render_input(string $provider, string $key, bool $password = false): void {
    $opts = SESLP_Helpers::get_options();

    $val  = $opts['providers'][$provider][$key] ?? '';
    $name = "seslp_options[providers][{$provider}][{$key}]";
    $type = $password ? 'password' : 'text';

    printf(
      '<input type="%1$s" name="%2$s" value="%3$s" class="regular-text" autocomplete="off" />',
      esc_attr($type),
      esc_attr($name),
      esc_attr($val)
    );
  }

  /**
   * Enqueue CSS and JavaScript for SESLP admin pages.
   *
   * @param string $hook
   * @return void
   */
  public static function enqueue_admin_assets(string $hook): void {
    // Load on all SESLP admin pages: settings, pricing, account, etc.
    if (!isset($_GET['page'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading current admin page slug.
      return;
    }

    $page = sanitize_key(wp_unslash($_GET['page'])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading current admin page slug.

    // seslp-settings, seslp-settings-pricing, seslp-settings-account ...
    if (strpos($page, 'seslp-settings') !== 0) {
      return;
    }

    $plugin  = SESLP_Plugin::instance();
    $css_rel = 'assets/css/admin-settings.css';
    $js_rel  = 'assets/js/admin-settings.js';

    $css_path = $plugin->dir . $css_rel;
    $js_path  = $plugin->dir . $js_rel;

    $css_ver  = file_exists($css_path) ? (string) filemtime($css_path) : SESLP_Plugin::VERSION;
    $js_ver   = file_exists($js_path)  ? (string) filemtime($js_path)  : SESLP_Plugin::VERSION;

    wp_enqueue_style('seslp-admin', $plugin->url . $css_rel, [], $css_ver);
    wp_enqueue_script('seslp-admin', $plugin->url . $js_rel, ['jquery'], $js_ver, true);
  }

  /**
   * Return provider slugs used on settings screen.
   *
   * - Starts from registry list.
   * - Removes Weibo.
   * - Ensures LinkedIn is present.
   * - Exposes a filter hook for customizations.
   *
   * @return string[]
   */
  private static function provider_keys(): array {
    $providers = SESLP_Providers_Registry::list();

    // Remove Weibo from settings UI.
    $providers = array_values(array_diff($providers, ['weibo']));

    // Ensure LinkedIn is available even if not in registry list.
    if (!in_array('linkedin', $providers, true)) {
      $providers[] = 'linkedin';
    }

    /**
     * Filter the providers shown in the settings page.
     *
     * @param string[] $providers Provider slugs.
     * @return string[]
     */
    return apply_filters('seslp_settings_providers', $providers);
  }

  /**
   * Render the settings page template.
   *
   * Allows a theme override before falling back to the bundled plugin template.
   *
   * @return void
   */
  public static function render_settings_page(): void {
    // Theme override: /your-theme/seslp/settings-page.php
    $theme_tpl = function_exists('locate_template') ? locate_template('seslp/settings-page.php', false, false) : '';
    if (!empty($theme_tpl) && file_exists($theme_tpl)) {
      include $theme_tpl; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingInclude
      return;
    }

    // Plugin template
    $plugin = SESLP_Plugin::instance();
    $tpl    = $plugin->dir . 'templates/settings-page.php';
    if (file_exists($tpl)) {
      seslp_render_launch_promo_banner();
      include $tpl; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingInclude
    }
  }
}