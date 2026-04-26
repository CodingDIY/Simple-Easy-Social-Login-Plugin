<?php
// Freemius SDK init extracted from main plugin file.
// Code & comments in English only per project convention.

if (!defined('ABSPATH')) {
  exit;
}

// Redirect Freemius translation loading to plugin's own languages/ folder.
// Survives Freemius SDK updates — files stay in languages/, not vendor/freemius/languages/.
add_filter('load_textdomain_mofile', function (string $mofile, string $domain): string {
  if ($domain === 'freemius') {
    $custom = plugin_dir_path(__DIR__) . 'languages/freemius-' . determine_locale() . '.mo';
    if (file_exists($custom)) {
      return $custom;
    }
  }
  return $mofile;
}, 10, 2);

if (!function_exists('seslp')) {
  /**
   * Initialize and return the Freemius SDK instance.
   *
   * NOTE: fs_dynamic_init() requires the config array to be passed
   * inline (not via a variable or function call) so that Freemius's
   * deploy parser can correctly identify and strip premium-only code
   * when generating the free WP.org build.
   *
   * @return Freemius|FS_Site|mixed
   */
  function seslp() {
    global $seslp;

    if (!isset($seslp)) {
      require_once __DIR__ . '/freemius/start.php';

      $seslp = fs_dynamic_init( array(
        'id'                  => '19985',
        'slug'                => 'simple-easy-social-login',
        'premium_slug'        => 'simple-easy-social-login-paid',
        'type'                => 'plugin',
        'public_key'          => 'pk_174a0fc7fb0d51d8b4b61397a31b3',
        'is_premium'          => false,
        'premium_suffix'      => 'Paid',
        'has_premium_version' => true,
        'has_addons'          => false,
        'has_paid_plans'      => true,
        'wp_org_gatekeeper'   => '%%WP_ORG_GATEKEEPER%%',
        'menu'                => array(
          'slug'       => 'seslp-settings',
          'first-path' => 'admin.php?page=seslp-settings',
          'support'    => false, // Hide "Support Forum"
          'contact'    => true,
          'account'    => true,
        ),
      ) );
    }

    return $seslp;
  }
}

$seslp = seslp();
do_action('seslp_loaded', $seslp);
