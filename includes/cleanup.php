<?php

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

if (!function_exists('seslp_should_remove_data')) {
  /**
   * Determine if full cleanup is allowed.
   */
  function seslp_should_remove_data(): bool {
    if (defined('SESLP_UNINSTALL_REMOVE_DATA') && SESLP_UNINSTALL_REMOVE_DATA === true) {
      return true;
    }

    if (apply_filters('seslp_uninstall_remove_data', false) === true) {
      return true;
    }

    $seslp_flag = get_option('seslp_uninstall_remove_data');
    return is_string($seslp_flag) && strtolower($seslp_flag) === 'yes';
  }
}

if (!function_exists('seslp_should_deep_clean')) {
  /**
   * Determine if deep clean is allowed.
   */
  function seslp_should_deep_clean(): bool {
    if (defined('SESLP_UNINSTALL_DEEP_CLEAN') && SESLP_UNINSTALL_DEEP_CLEAN === true) {
      return true;
    }

    if (apply_filters('seslp_uninstall_deep_clean', false) === true) {
      return true;
    }

    $seslp_flag = get_option('seslp_uninstall_deep_clean');
    return is_string($seslp_flag) && strtolower($seslp_flag) === 'yes';
  }
}

if (!function_exists('seslp_cleanup_single_site')) {
  /**
   * Clean plugin data for a single site.
   */
  function seslp_cleanup_single_site(string $seslp_prefix = 'seslp_'): void {
    global $wpdb;

    $seslp_like = $wpdb->esc_like($seslp_prefix) . '%';

    $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Cleanup requires direct deletion of plugin-owned options.
      $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
        $seslp_like
      )
    ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- WordPress core table name from $wpdb.

    if (function_exists('wp_clear_scheduled_hook')) {
      foreach (['seslp_cron_cleanup', 'seslp_cron_sync'] as $seslp_hook) {
        wp_clear_scheduled_hook($seslp_hook);
      }
    }

    if (seslp_should_remove_data()) {
      $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Cleanup requires direct deletion of plugin-owned user meta.
        $wpdb->prepare(
          "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
          $seslp_like
        )
      ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- WordPress core table name from $wpdb.

      if (seslp_should_deep_clean()) {
        $seslp_maybe_tables = [
          $wpdb->prefix . 'seslp_login_logs',
        ];

        foreach ($seslp_maybe_tables as $seslp_table) {
          $seslp_exists = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Cleanup checks whether the plugin table exists before dropping it.
            $wpdb->prepare('SHOW TABLES LIKE %s', $seslp_table)
          );

          if ($seslp_exists === $seslp_table) {
            $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange -- Cleanup intentionally drops a plugin-owned table built from the current site prefix and fixed suffix.
              "DROP TABLE IF EXISTS `{$seslp_table}`"
            ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is built from the current site prefix and fixed plugin suffix.
          }
        }
      }
    }
  }
}

if (!function_exists('seslp_uninstall_cleanup')) {
  /**
   * Run uninstall cleanup for single-site or multisite.
   */
  function seslp_uninstall_cleanup(): void {
    $seslp_prefix = 'seslp_';

    if (is_multisite()) {
      $seslp_site_ids = get_sites(['fields' => 'ids']);

      foreach ($seslp_site_ids as $seslp_blog_id) {
        switch_to_blog((int) $seslp_blog_id);
        seslp_cleanup_single_site($seslp_prefix);
        restore_current_blog();
      }
    } else {
      seslp_cleanup_single_site($seslp_prefix);
    }

    if (is_multisite() && function_exists('delete_site_option') && seslp_should_remove_data()) {
      // delete_site_option('seslp_network_setting_example');
    }
  }
}