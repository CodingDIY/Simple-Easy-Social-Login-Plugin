<?php
/**
 * Freemius plan-based variables
 * Used globally for gating features in settings-page.php and others.
 */

if (!defined('ABSPATH')) {
  exit;
}

// Initialize Freemius instance
$fs = function_exists('simple_easy_social_login_freemius') ? simple_easy_social_login_freemius() : null;

// Detect current plan
$is_free = !$fs || (bool) $fs->is_free_plan();
$is_pro  = $fs ? (bool) $fs->is_plan('pro') : false;
$is_max  = $fs ? (bool) $fs->is_plan('max') : false;

$current_plan = [
  'free' => $is_free,
  'pro'  => $is_pro,
  'max'  => $is_max,
];

// Capability flags used across settings templates
$can_pro_features = $is_pro || $is_max; // Pro and Max plans
$can_max_features = $is_max;            // Max plan only

// Provider availability per plan
$provider_plan = [
  'google'   => 'free',
  'facebook' => 'free',
  'linkedin' => 'free',
  'naver'    => 'pro',
  'kakao'    => 'pro',
  'line'     => 'pro',
  // 'weibo'  => 'max', (deprecated)
];

$provider_allowed = [];
foreach ($provider_plan as $provider => $required_plan) {
  $required_plan = strtolower((string) $required_plan);
  
  switch ($required_plan) {
    case 'free':
      // Allows free features when any plan is activated
      $is_allowed = $is_free || $is_pro || $is_max;
      break;

    case 'pro':
      // Only allowed on Pro or higher
      $is_allowed = $is_pro || $is_max;
      break;

    case 'max':
      // Only allowed on max
      $is_allowed = $is_max;
      break;

    default:
      $is_allowed = false;
  }
  $provider_allowed[strtolower((string) $provider)] = $is_allowed;
}

$provider_allowed = apply_filters('seslp_provider_allowed', $provider_allowed, $current_plan);

// Provider list (filtered)
$providers = [];

if (class_exists('SESLP_Providers_Registry')) {
  $providers_all = SESLP_Providers_Registry::list();
  $providers = array_values(array_filter($providers_all, function ($p) use ($provider_allowed) {
    $p = strtolower((string) $p);
    return isset($provider_allowed[$p]) && true === $provider_allowed[$p];
  }));
  unset($providers_all);
}