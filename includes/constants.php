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
const SESLP_SLUG        = 'simple-easy-social-login';
// Text domain (keep in sync with plugin header + .pot)
const SESLP_TD          = 'se-social-login';
// Version (bump on release; use for cache-busting if needed)
const SESLP_VERSION     = '1.0.0';
// Facebook slug
const FB_SLUG           = 'facebook';
// Google slug
const GL_SLUG           = 'google';
// Naver slug
const NV_SLUG           = 'naver';
// Kakao slug
const KA_SLUG           = 'kakao';
// Line slug
const LN_SLUG           = 'line';
// Weibo slug
const WB_SLUG           = 'weibo';

// ==== Documents ====
const SESLP_DOCS_BASE   = 'https://selfcoding.app';

// ==== Options & settings ====
// Unified options array key (where providers/client_id/client_secret live)
const SESLP_OPT_KEY       = 'seslp_options';
// Settings page slug under wp-admin
const SESLP_SETTINGS_SLUG = 'seslp-settings';

// ==== Redirect modes (used in settings/options) ====
const SESLP_REDIRECT_FRONT     = 'front';     // Front page (home)
const SESLP_REDIRECT_DASHBOARD = 'dashboard'; // /wp-admin/
const SESLP_REDIRECT_PROFILE   = 'profile';   // Profile page URL
const SESLP_REDIRECT_CUSTOM    = 'custom';    // Custom URL (options['redirect']['custom_url'])

// ==== User meta / transient keys (prefix only to avoid collisions) ====
const SESLP_META_PREFIX      = 'seslp_';       // e.g., seslp_provider_google_uid
const SESLP_TRANSIENT_PREFIX = 'seslp_';       // e.g., seslp_state_xxx

// ==== Asset handles (optional, for consistency) ====
const SESLP_ASSET_ADMIN = 'seslp-admin-settings';
const SESLP_ASSET_FRONT = 'seslp-front';