<?php
if (!defined('ABSPATH')) exit;

/**
 * Central constants for Simple Easy Social Login (SES Login).
 * Keep this file minimal and framework-agnostic.
 * Comments in English, UI strings elsewhere should use localization functions.
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
// Text domain (keep in sync with plugin header + .pot)
const SESLP_TD = 'se-social-login';
// Version (bump on release; use for cache-busting if needed)
const SESLP_VERSION = '1.9.9';
// ==== Provider slugs (shared by registry and provider classes) ====
const FB_SLUG = 'facebook';
const GL_SLUG = 'google';
const NV_SLUG = 'naver';
const KA_SLUG = 'kakao';
const LN_SLUG = 'line';
const WB_SLUG = 'weibo';
const LK_SLUG = 'linkedin';

// ==== Documents ====
// const SESLP_DOCS_BASE = 'https://selfcoding.app';

// ==== Options & settings ====
// Unified options array key (where providers/client_id/client_secret live)
const SESLP_OPT_KEY = 'seslp_options';
// Settings page slug under wp-admin
const SESLP_SETTINGS_SLUG = 'seslp-settings';

// ==== Redirect modes (used in settings/options) ====