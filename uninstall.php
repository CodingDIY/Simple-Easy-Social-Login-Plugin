<?php
/**
 * Uninstall handler for Simple Easy Social Login
 *
 * Behavior:
 * - By default, we keep data (safe default). Only minimal cleanup (transients/options) is performed.
 * - If the admin explicitly opts into data removal, we delete plugin options, transients, user meta, and (optionally) custom tables.
 *
 * Opt‑in switches (any one of these enables full cleanup):
 *   1) Option in DB: option_name `seslp_uninstall_remove_data` set to 'yes'
 *   2) PHP constant:  const SESLP_UNINSTALL_REMOVE_DATA = true;
 *   3) Filter:        apply_filters('seslp_uninstall_remove_data', false) === true
 *
 * Deep clean (drops custom tables) requires any one of:
 *   - Option  `seslp_uninstall_deep_clean` == 'yes'
 *   - Constant SESLP_UNINSTALL_DEEP_CLEAN === true
 *   - Filter  `seslp_uninstall_deep_clean`
 *
 * Multisite aware: iterates all sites and cleans per‑site data.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
  exit; // Exit if accessed directly
}

// ----------------------------------------
// Safety: namespace/prefix used across the plugin
// ----------------------------------------
$seslp_prefix = 'seslp_';

/**
 * Determine if full cleanup is allowed.
 *
 * @return bool
 */
function seslp_should_remove_data(): bool {
  // Constant override
  if (defined('SESLP_UNINSTALL_REMOVE_DATA') && SESLP_UNINSTALL_REMOVE_DATA === true) {
    return true;
  }

  // Filter override (developers)
  if (apply_filters('seslp_uninstall_remove_data', false) === true) {
    return true;
  }

  // Check stored option (per‑site)
  $flag = get_option('seslp_uninstall_remove_data');
  return (is_string($flag) && strtolower($flag) === 'yes');
}

/**
 * Determine if deep clean (drop tables, etc.) is allowed.
 *
 * @return bool
 */
function seslp_should_deep_clean(): bool {
  if (defined('SESLP_UNINSTALL_DEEP_CLEAN') && SESLP_UNINSTALL_DEEP_CLEAN === true) {
    return true;
  }
  if (apply_filters('seslp_uninstall_deep_clean', false) === true) {
    return true;
  }
  $flag = get_option('seslp_uninstall_deep_clean');
  return (is_string($flag) && strtolower($flag) === 'yes');
}

/**
 * Clean data for a single blog/site.
 */
function seslp_cleanup_single_site(string $prefix = 'seslp_'): void {
  global $wpdb;

  // 1) Always: remove plugin transients/options that are clearly safe to drop
  //    (we limit to our prefix for safety)
  $like = $wpdb->esc_like($prefix) . '%';

  // options
  /* delete options starting with our prefix */
  $wpdb->query(
    $wpdb->prepare(
      "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
      $like
    )
  ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- WordPress core table name from $wpdb.

  // site transients stored as options may also match the prefix, covered above when named with our prefix

  // 2) Always unschedule cron hooks to prevent orphaned callbacks after uninstall
  if (function_exists('wp_clear_scheduled_hook')) {
    // Example hooks used by this plugin (update list if you add more)
    $hooks = [
      'seslp_cron_cleanup',
      'seslp_cron_sync',
    ];
    foreach ($hooks as $hook) {
      wp_clear_scheduled_hook($hook);
    }
  }

  // 3) Conditionally remove user meta & other data if admin opted in
  if (seslp_should_remove_data()) {
    // user meta
    $wpdb->query(
      $wpdb->prepare(
        "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
        $like
      )
    ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- WordPress core table name from $wpdb.

    // Deep clean: drop custom tables if they exist
    if (seslp_should_deep_clean()) {
      $maybe_tables = [
        $wpdb->prefix . 'seslp_login_logs',
      ];
      foreach ($maybe_tables as $table) {
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists === $table) {
          $wpdb->query("DROP TABLE IF EXISTS `{$table}`"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is built from the current site prefix and a fixed plugin suffix.
        }
      }
    }
  }
}

// ----------------------------------------
// Run cleanup: single or multisite
// ----------------------------------------
if (is_multisite()) {
  $seslp_site_ids = get_sites([ 'fields' => 'ids' ]);
  foreach ($seslp_site_ids as $blog_id) {
    switch_to_blog((int) $blog_id);
    seslp_cleanup_single_site($seslp_prefix);
    restore_current_blog();
  }
} else {
  seslp_cleanup_single_site($seslp_prefix);
}

// Optional: network‑level site options (if any were used)
if (is_multisite() && function_exists('delete_site_option') && seslp_should_remove_data()) {
  // delete_site_option('seslp_network_setting_example'); // uncomment if used
}