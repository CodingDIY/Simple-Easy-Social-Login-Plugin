<?php
// Freemius SDK init extracted from main plugin file.
// Code & comments in English only per project convention.

if ( ! defined('ABSPATH') ) exit;

// Avoid re-declare
if ( ! function_exists('simple_easy_social_login_freemius') ) {
  function simple_easy_social_login_freemius() {
    global $simple_easy_social_login_freemius;

    if ( ! isset( $simple_easy_social_login_freemius ) ) {
      // Load SDK from vendor
      require_once __DIR__ . '/freemius/start.php';

      // Dynamic init
      $simple_easy_social_login_freemius = fs_dynamic_init( array(
        'id'                  => '19985',
        'slug'                => 'simple-easy-social-login',
        'premium_slug'        => 'simple-easy-social-login-paid',
        'type'                => 'plugin',
        'public_key'          => 'pk_174a0fc7fb0d51d8b4b61397a31b3',
        'is_premium'          => true,
        'premium_suffix'      => 'Paid',
        'has_premium_version' => true,
        'has_addons'          => false,
        'has_paid_plans'      => true,
        'wp_org_gatekeeper'   => 'OA7#BoRiBNqdf52FvzEf!!074aRLPs8fspif$7K1#4u4Csys1fQlCecVcUTOs2mcpeVHi#C2j9d09fOTvbC0HloPT7fFee5WdS3G',

        // Admin menu configuration
        'menu' => array(
          'slug'       => 'seslp-settings',
          'first-path' => 'admin.php?page=seslp-settings',
          'support'    => false, // Hide "Support Forum"
          'contact'    => true,
          'account'    => true,
          // 'parent'   => array( 'slug' => 'options-general.php' ),
        ),
      ) );
    }

    return $simple_easy_social_login_freemius;
  }

  // Initialize and fire loaded hook
  simple_easy_social_login_freemius();
  do_action('simple_easy_social_login_freemius_loaded');
}