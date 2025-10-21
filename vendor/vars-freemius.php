<?php
/**
 * Freemius plan-based variables
 * Used globally for gating features in settings-page.php and others.
 */
if (!defined('ABSPATH')) exit;

// Initialize Freemius instance
$fs = function_exists('simple_easy_social_login_freemius') ? simple_easy_social_login_freemius() : null;

// Detect current plan
$is_free  = $fs ? (bool) $fs->is_free_plan() : true;
$is_pro   = $fs ? (bool) $fs->is_plan('pro') : false;
$is_max   = $fs ? (bool) $fs->is_plan('max') : false;

// Feature switches by plan
$can_pro_features = $is_pro || $is_max;
$can_max_features = $is_max;

// Provider availability per plan
$provider_allowed = [
  'google'   => true,                  // Free
  'facebook' => true,                  // Free
  'naver'    => $can_pro_features,     // Pro
  'kakao'    => $can_pro_features,     // Pro
  'line'     => $can_pro_features,     // Pro
  'weibo'    => $can_max_features,     // Max
];

// Provider list (filtered)
if (class_exists('SESLP_Providers_Registry')) {
  $providers_all = SESLP_Providers_Registry::list();
  $providers = array_values(array_filter($providers_all, function($p) use ($provider_allowed) {
    $p = strtolower((string) $p);
    return isset($provider_allowed[$p]) && $provider_allowed[$p] === true;
  }));
  unset($providers_all);
} else {
  $providers = [];
}