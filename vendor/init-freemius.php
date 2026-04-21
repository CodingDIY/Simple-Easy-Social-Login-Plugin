<?php
// Freemius SDK init extracted from main plugin file.
// Code & comments in English only per project convention.

if (!defined('ABSPATH')) {
  exit;
}

if (!function_exists('seslp_freemius_config')) {
  /**
   * Build Freemius configuration array.
   *
   * @return array<string, mixed>
   */
  function seslp_freemius_config() {
    $config = array(
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
      'wp_org_gatekeeper'   => 'OA7#BoRiBNqdf52FvzEf!!074aRLPs8fspif$7K1#4u4Csys1fQlCecVcUTOs2mcpeVHi#C2j9d09fOTvbC0HloPT7fFee5WdS3G',
      'menu'                => array(
        'slug'       => 'seslp-settings',
        'first-path' => 'admin.php?page=seslp-settings',
        'support'    => false, // Hide "Support Forum"
        'contact'    => true,
        'account'    => true,
        // 'parent'   => array( 'slug' => 'options-general.php' ),
      ),
    );

    /**
     * Allow overriding Freemius dynamic init arguments.
     *
     * @param array<string, mixed> $config
     */
    return apply_filters('seslp_freemius_config', $config);
  }
}

// Avoid re-declare.
if (!function_exists('simple_easy_social_login_freemius')) {
  /**
   * Initialize Freemius SDK instance.
   *
   * @return Freemius|FS_Site|mixed
   */
  function simple_easy_social_login_freemius() {
    global $simple_easy_social_login_freemius;

    if (!isset($simple_easy_social_login_freemius)) {
      require_once __DIR__ . '/freemius/start.php';

      $simple_easy_social_login_freemius = fs_dynamic_init( seslp_freemius_config() );
    }

    return $simple_easy_social_login_freemius;
  }
}

$simple_easy_social_login_freemius = simple_easy_social_login_freemius();
do_action('simple_easy_social_login_freemius_loaded', $simple_easy_social_login_freemius);