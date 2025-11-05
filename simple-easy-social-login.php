<?php
/**
 * Plugin Name: Social Login with GOOGLE, FACEBOOK, NAVER, KAKAO, LINE and LINKEDIN
 * Description: Boost your WordPress site with a simple, easy, and lightweight social login solution. Instantly connect users through Google, Facebook, Naver, Kakao, Line, and LinkedIn. While keeping only the essential features for speed and reliability, this plugin offers detailed setup guides and full documentation on the official website — making social login integration effortless for everyone.
 * Author: Selfcoding
 * Version: 1.0
 * Text Domain: se-social-login
 * Domain Path: /languages
 */

declare(strict_types=1);
if (!defined('ABSPATH')) exit;
// Check point

/** Early includes (require once if files exist) */
$__seslp_dir = plugin_dir_path(__FILE__);
foreach ([
  'vendor/init-freemius.php', // Freemius start
  'includes/constants.php',
  'includes/helpers.php',
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
] as $__rel) {
  $p = $__seslp_dir . $__rel;
  if (file_exists($p)) {
    require_once $p;
  }
}
unset($__rel, $p, $__seslp_dir);

/** Freemius bootstrap (externalized) */
if ( function_exists('simple_easy_social_login_freemius') ) {
  simple_easy_social_login_freemius()->set_basename(true, __FILE__);
}

/**
 * Main plugin bootstrap.
 * - Keeps global surface small (single class + helpers).
 * - Safe to activate with empty includes/templates.
 */
final class SESLP_Plugin {
  /** Singleton */
  private static ?SESLP_Plugin $instance = null;

  /** Delegate to global constants from includes/constants.php (single source of truth) */
  public const SLUG    = SESLP_SLUG;
  public const TD      = SESLP_TD;
  public const VERSION = SESLP_VERSION;

  /** Paths */
  public string $file;
  public string $dir;
  public string $url;

  /** Construct is private; use instance() */
  private function __construct() {
    $this->file = __FILE__;
    $this->dir  = plugin_dir_path($this->file);
    $this->url  = plugin_dir_url($this->file);

    add_action('plugins_loaded', [$this, 'load_textdomain']);
  }

  /** Public singleton accessor */
  public static function instance(): SESLP_Plugin {
    if (!self::$instance) self::$instance = new self();
    return self::$instance;
  }

  /** Activation/Deactivation */
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
          // 'weibo'    => ['client_id' => '', 'client_secret' => ''],
        ],
        'ui' => [
          'show_on_login'   => 1,
          'show_on_register'=> 1,
        ],
      ]);
    }
  }
  public static function deactivate(): void {
    // Keep options on deactivate; remove in uninstall.php if needed.
  }

  /** i18n */
  public function load_textdomain(): void {
    // Loads from /wp-content/languages/plugins/ as well as /languages in the plugin
    load_plugin_textdomain(self::TD, false, dirname(plugin_basename($this->file)) . '/languages');
  }

  /** Public helper for templates: returns the provider-specific auth URL */
  public function auth_url(string $provider): string {
    $provider = sanitize_key($provider);
    
    if ($provider === 'google' && class_exists('SESLP_Provider_Google')) {
      $g = new SESLP_Provider_Google();
      return $g->get_auth_url();
    }
    if ($provider === 'naver' && class_exists('SESLP_Provider_Naver')) {
      $n = new SESLP_Provider_Naver();
      return $n->get_auth_url();
    }
    if ($provider === 'facebook' && class_exists('SESLP_Provider_Facebook')) {
      $f = new SESLP_Provider_Facebook();
      return $f->get_auth_url();
    }
    if ($provider === 'kakao' && class_exists('SESLP_Provider_Kakao')) {
      $k = new SESLP_Provider_Kakao();
      return $k->get_auth_url();
    }
    if ($provider === 'line' && class_exists('SESLP_Provider_Line')) {
      $l = new SESLP_Provider_Line();
      return $l->get_auth_url();
    }
    // if ($provider === 'weibo' && class_exists('SESLP_Provider_Weibo')) {
    //   $w = new SESLP_Provider_Weibo();
    //   return $w->get_auth_url();
    // }
    if ($provider === 'linkedin' && class_exists('SESLP_Provider_Linkedin')) {
      $li = new SESLP_Provider_Linkedin();
      return $li->get_auth_url();
    }

    return add_query_arg(['social_login' => $provider], home_url('/'));
  }
}

/** Bootstrap */
add_action('plugins_loaded', static function () {
  SESLP_Plugin::instance();
  if (class_exists('SESLP_Settings')) { SESLP_Settings::init(); }
  if (class_exists('SESLP_Assets')) { (new SESLP_Assets())->register(); }
  if (class_exists('SESLP_UI')) { (new SESLP_UI())->register(); }
  if (class_exists('SESLP_Auth')) { (new SESLP_Auth())->register(); }
  if (class_exists('SESLP_Guides')) { add_action('admin_menu', ['SESLP_Guides', 'register_menu']); }
});

/** Lifecycle */
register_activation_hook(__FILE__, ['SESLP_Plugin', 'activate']);
register_deactivation_hook(__FILE__, ['SESLP_Plugin', 'deactivate']);