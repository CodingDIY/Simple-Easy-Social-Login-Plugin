<?php
/**
 * Plugin Name: Simple Easy Social Login – OAuth Login
 * Description: Boost your WordPress site with a simple, easy, and lightweight social login solution. Instantly connect users through Google, Facebook, Naver, Kakao, Line, and LinkedIn. While keeping only the essential features for speed and reliability, this plugin offers detailed setup guides and full documentation on the official website — making social login integration effortless for everyone.
 * Author: selfcoding
 * Version: 1.9.9
 * Text Domain: simple-easy-social-login-oauth-login
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

declare(strict_types=1);
if (!defined('ABSPATH')) {
  exit;
}

/** Early includes (require once if files exist) */
$seslp_dir = plugin_dir_path(__FILE__);
foreach ([
  'vendor/init-freemius.php', // Freemius start
  'includes/constants.php',
  'includes/helpers.php',
  'includes/cleanup.php',
  'includes/logger.php',
  'providers/class-SESLP-providers-registry.php',
  'vendor/vars-freemius.php', // freemius variables
  'providers/class-SESLP-provider-interface.php',
  'includes/class-SESLP-state.php',
  'providers/class-SESLP-provider-google.php',
  'providers/class-SESLP-provider-naver.php',
  'providers/class-SESLP-provider-facebook.php',
  'providers/class-SESLP-provider-kakao.php',
  'providers/class-SESLP-provider-line.php',
  // 'providers/class-SESLP-provider-weibo.php',
  'providers/class-SESLP-provider-linkedin.php',
  'includes/class-SESLP-user-linker.php',
  'includes/class-SESLP-avatar.php',
  'includes/class-SESLP-redirect.php',
  'includes/class-SESLP-assets.php',
  'includes/class-SESLP-settings.php',
  'includes/class-SESLP-ui.php',
  'includes/class-SESLP-auth.php',
  'includes/class-SESLP-guides.php',
] as $seslp_rel) {
  $seslp_p = $seslp_dir . $seslp_rel;
  if (file_exists($seslp_p)) {
    require_once $seslp_p;
  }
}
unset($seslp_rel, $seslp_p, $seslp_dir);

/** Freemius bootstrap (externalized) */
if (function_exists('simple_easy_social_login_freemius')) {
  simple_easy_social_login_freemius()->set_basename(true, __FILE__);
}

if (function_exists('simple_easy_social_login_freemius') && function_exists('seslp_uninstall_cleanup')) {
  simple_easy_social_login_freemius()->add_action('after_uninstall', 'seslp_uninstall_cleanup');
}

/**
 * Main plugin bootstrap container.
 *
 * Responsible for:
 * - storing core plugin paths and URLs,
 * - exposing the singleton instance,
 * - registering default options on activation,
 * - generating provider auth URLs.
 *
 * This class is intentionally lightweight and delegates
 * feature-specific logic to dedicated classes.
 */
final class SESLP_Plugin {
  /** Singleton */
  private static ?SESLP_Plugin $instance = null;

  /** Map of provider slugs to their concrete classes. */
  private const PROVIDER_CLASSES = [
    'google'   => 'SESLP_Provider_Google',
    'naver'    => 'SESLP_Provider_Naver',
    'facebook' => 'SESLP_Provider_Facebook',
    'kakao'    => 'SESLP_Provider_Kakao',
    'line'     => 'SESLP_Provider_Line',
    'linkedin' => 'SESLP_Provider_Linkedin',
  ];

  /** Delegate to global constants from includes/constants.php (single source of truth) */
  public const SLUG    = SESLP_SLUG;
  public const VERSION = SESLP_VERSION;

  /** Paths */
  public string $file;
  public string $dir;
  public string $url;

  /**
   * Create the plugin instance and initialize base paths.
   *
   * @return void
   */
  private function __construct() {
    $this->file = __FILE__;
    $this->dir  = plugin_dir_path($this->file);
    $this->url  = plugin_dir_url($this->file);
  }

  /**
   * Return the shared plugin instance.
   *
   * @return SESLP_Plugin
   */
  public static function instance(): SESLP_Plugin {
    if (!self::$instance) self::$instance = new self();
    return self::$instance;
  }

  /**
   * Run plugin activation tasks.
   *
   * Creates the default plugin options only when they do not already exist,
   * then stores a short-lived redirect flag so the administrator can be sent
   * to the settings page after activation.
   *
   * @return void
   */
  public static function activate(): void {
    // Reserved for future: create options, db tables, etc.
    if (!get_option('seslp_options')) {
      add_option('seslp_options', [
        'providers' => [
          'google'   => ['client_id' => '', 'client_secret' => ''],
          'facebook' => ['client_id' => '', 'client_secret' => ''],
          'naver'    => ['client_id' => '', 'client_secret' => ''],
          'kakao'    => ['client_id' => '', 'client_secret' => ''],
          'line'     => ['client_id' => '', 'client_secret' => ''],
          'linkedin' => ['client_id' => '', 'client_secret' => ''],
          // 'weibo'    => ['client_id' => '', 'client_secret' => ''],
        ],
        'ui' => [
          'show_on_login'   => 1,
          'show_on_register'=> 1,
        ],
      ]);
    }
    // Trigger redirect to settings page after activation
    set_transient('seslp_activation_redirect', true, 30);
  }

  /**
   * Run plugin deactivation tasks.
   *
   * Currently this method keeps saved options intact and performs
   * no cleanup on deactivation.
   *
   * @return void
   */
  public static function deactivate(): void {
    // Keep options on deactivate; remove in uninstall.php if needed.
  }

  /**
   * Build the authentication URL for a given provider.
   *
   * Falls back to a generic query-argument based URL when the provider
   * class is unavailable, and allows the final URL to be filtered.
   *
   * @param string $provider Provider slug.
   * @return string
   */
  public function auth_url(string $provider): string {
    $provider = sanitize_key($provider);
    $auth_url = esc_url_raw(add_query_arg(['social_login' => $provider], home_url('/')));
    $provider_class = self::PROVIDER_CLASSES[$provider] ?? null;

    if ($provider_class && class_exists($provider_class)) {
      $instance = new $provider_class();

      if ($instance instanceof SESLP_Provider_Interface) {
        /** @var string $auth_url */
        $auth_url = $instance->get_auth_url();
      }
    }

    return (string) apply_filters('seslp_auth_url', $auth_url, $provider);
  }
}

/**
 * Bootstrap the plugin after all plugins are loaded.
 *
 * Initializes the main plugin instance and registers
 * settings, assets, UI hooks, and authentication handlers.
 *
 * @return void
 */
function seslp_bootstrap(): void {
  SESLP_Plugin::instance();

  if (class_exists('SESLP_Settings')) {
    SESLP_Settings::init();
  }

  if (class_exists('SESLP_Assets')) {
    (new SESLP_Assets())->register();
  }

  if (class_exists('SESLP_UI')) {
    (new SESLP_UI())->register();
  }

  if (class_exists('SESLP_Auth')) {
    (new SESLP_Auth())->register();
  }
}
add_action('plugins_loaded', 'seslp_bootstrap');

/**
 * Register the plugin admin menu items.
 *
 * Delegates menu registration to the guides/settings UI class
 * when that class is available.
 *
 * @return void
 */
function seslp_register_admin_menu(): void {
  if (class_exists('SESLP_Guides')) {
    SESLP_Guides::register_menu();
  }
}
add_action('admin_menu', 'seslp_register_admin_menu', 99);

/**
 * Redirect administrators to the plugin settings page after activation.
 *
 * Runs only in the admin area, ignores AJAX requests,
 * and removes the redirect transient before redirecting.
 *
 * @return void
 */
function seslp_maybe_redirect_after_activation(): void {
  if (!is_admin() || !current_user_can('manage_options')) {
    return;
  }

  if (!get_transient('seslp_activation_redirect')) {
    return;
  }

  delete_transient('seslp_activation_redirect');

  if (wp_doing_ajax()) {
    return;
  }

  wp_safe_redirect(admin_url('admin.php?page=seslp-settings'));
  exit;
}
add_action('admin_init', 'seslp_maybe_redirect_after_activation');

/** Lifecycle */
register_activation_hook(__FILE__, ['SESLP_Plugin', 'activate']);
register_deactivation_hook(__FILE__, ['SESLP_Plugin', 'deactivate']);