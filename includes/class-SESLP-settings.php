<?php
/**
 * Admin Settings module
 * - Extracted from main plugin class for clarity
 */

declare(strict_types=1);
if (!defined('ABSPATH')) exit;

final class SESLP_Settings {
  public static function init(): void {
    if (!is_admin()) return;
    add_action('admin_menu', [self::class, 'add_settings_menu']);
    add_action('admin_init', [self::class, 'register_settings']);
    add_action('admin_enqueue_scripts', [self::class, 'enqueue_admin_assets']);
  }

  public static function add_settings_menu(): void {
    // 1) Top-level menu
    add_menu_page(
      __( 'Simple Easy Social Login', SESLP_Plugin::TD ),  // Page title
      __( 'SE Social Login', SESLP_Plugin::TD ),           // Menu title (top-level label)
      'manage_options',                                    // Capability
      'seslp-settings',                                    // Menu slug
      [ self::class, 'render_settings_page' ],             // Callback
      'dashicons-unlock',                                  // Icon
      65                                                   // Position (below Settings, above Tools typically)
    );

    // WordPress automatically creates a first submenu that mirrors the top-level label.
    // Remove that default submenu and add our own with a clearer label “Setting”.
    remove_submenu_page( 'seslp-settings', 'seslp-settings' );

    add_submenu_page(
      'seslp-settings',                                             // Parent slug (this top-level menu)
      __( 'Simple Easy Social Login – Setting', SESLP_Plugin::TD ), // Page title for the tab
      __( 'Setting', SESLP_Plugin::TD ),                            // Submenu label
      'manage_options',
      'seslp-settings',                                             // Same slug → same page callback
      [ self::class, 'render_settings_page' ]
    );
  }

  public static function register_settings(): void {
    register_setting('seslp_group', 'seslp_options');

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

    add_settings_section('seslp_section_main', __('Providers', SESLP_Plugin::TD), function () {
      echo '<p>' . esc_html__('Enter OAuth credentials for each provider.', SESLP_Plugin::TD) . '</p>';
    }, 'seslp-settings');

    $providers = SESLP_Providers_Registry::list();
    // Remove deprecated Weibo, add LinkedIn
    $providers = array_diff($providers, ['weibo']);
    $providers[] = 'linkedin';
    
    foreach ($providers as $prov) {
      add_settings_field("seslp_{$prov}_client_id",
        sprintf(esc_html__('%s Client ID', SESLP_Plugin::TD), ucfirst($prov)),
        function () use ($prov) { self::render_input($prov, 'client_id'); },
        'seslp-settings',
        'seslp_section_main'
      );
      add_settings_field("seslp_{$prov}_client_secret",
        sprintf(esc_html__('%s Client Secret', SESLP_Plugin::TD), ucfirst($prov)),
        function () use ($prov) { self::render_input($prov, 'client_secret', true); },
        'seslp-settings',
        'seslp_section_main'
      );
    }
  }

  public static function sanitize_yes_no($val) {
    return (is_string($val) && strtolower($val) === 'yes') ? 'yes' : '';
  }

  private static function render_input(string $provider, string $key, bool $password = false): void {
    $opts = get_option('seslp_options', []);
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

  public static function enqueue_admin_assets(string $hook): void {
    // Load only on our settings screen (works for both top-level and submenu routes)
    if (!isset($_GET['page']) || $_GET['page'] !== 'seslp-settings') {
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

  public static function render_settings_page(): void {
    // Theme override: /your-theme/seslp/settings-page.php
    $theme_tpl = function_exists('locate_template') ? locate_template('seslp/settings-page.php', false, false) : '';
    if (!empty($theme_tpl) && file_exists($theme_tpl)) {
      include $theme_tpl; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingInclude
      return;
    }

    // Plugin template
    $plugin = SESLP_Plugin::instance();
    $tpl = $plugin->dir . 'templates/settings-page.php';
    if (file_exists($tpl)) {
      include $tpl; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingInclude
    }
  }
}