<?php
if (!defined('ABSPATH')) exit;

/**
 * Central constants definition for SESLP.
 *
 * Responsible for:
 * - defining plugin-wide identifiers (slug, version),
 * - providing shared provider slugs used across the codebase,
 * - declaring option keys and admin page slugs,
 * - keeping configuration values in a single, framework-agnostic location.
 *
 * Notes:
 * - Keep this file lightweight and free of runtime logic.
 * - UI strings should always use WordPress localization functions elsewhere.
 */

// ==== Global providers list ==== 
// NOTE:
// - Providers list is NOT defined here on purpose.
//   Use SESLP_Providers_Registry::list() to derive supported providers
//   from SESLP_Providers_Registry::all(). This keeps a single source of truth.
// const SESLP_PROVIDERS = ['google', 'facebook', 'naver', 'kakao', 'line', 'weibo'];

// ==== Plugin identity ====
// Slug used for assets enqueue prefixes, option names suffixes, etc.
const SESLP_SLUG = 'simple-easy-social-login';
// Version (bump on release; use for cache-busting if needed)
const SESLP_VERSION = '1.9.9';

/**
 * Provider slug constants.
 *
 * These identifiers must remain consistent across:
 * - provider classes,
 * - registry mappings,
 * - user meta storage,
 * - authentication routing.
 */
const SESLP_FB_SLUG = 'facebook';
const SESLP_GL_SLUG = 'google';
const SESLP_NV_SLUG = 'naver';
const SESLP_KA_SLUG = 'kakao';
const SESLP_LN_SLUG = 'line';
const SESLP_WB_SLUG = 'weibo';
const SESLP_LK_SLUG = 'linkedin';

/**
 * Option and settings identifiers.
 *
 * Used as keys for storing plugin configuration in the WordPress options table
 * and for identifying admin settings pages.
 */
// ==== Options & settings ====
// Unified options array key (where providers/client_id/client_secret live)
const SESLP_OPT_KEY = 'seslp_options';
// Settings page slug under wp-admin
const SESLP_SETTINGS_SLUG = 'seslp-settings';

// ==== Redirect modes (used in settings/options) ====