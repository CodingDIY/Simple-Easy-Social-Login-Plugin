<?php
/**
 * Freemius plan-based variables
 * Used globally for gating features in settings-page.php and others.
 */

if (!defined('ABSPATH')) {
  exit;
}

// Initialize Freemius instance
$seslp_fs = function_exists('seslp') ? seslp() : null;

// Detect current plan
$seslp_is_free = !$seslp_fs || (bool) $seslp_fs->is_free_plan();
$seslp_is_pro  = $seslp_fs ? (bool) $seslp_fs->is_plan('pro') : false;
$seslp_is_max  = $seslp_fs ? (bool) $seslp_fs->is_plan('max') : false;

$seslp_current_plan = [
  'free' => $seslp_is_free,
  'pro'  => $seslp_is_pro,
  'max'  => $seslp_is_max,
];

// Capability flags used across settings templates
$seslp_can_pro_features = $seslp_is_pro || $seslp_is_max; // Pro and Max plans
$seslp_can_max_features = $seslp_is_max;                  // Max plan only

// Provider availability per plan
$seslp_provider_plan = [
  'google'   => 'free',
  'facebook' => 'free',
  'linkedin' => 'free',
  'naver'    => 'pro',
  'kakao'    => 'pro',
  'line'     => 'pro',
  // 'weibo'  => 'max', (deprecated)
];

$seslp_provider_allowed = [];
foreach ($seslp_provider_plan as $seslp_provider_key => $seslp_required_plan) {
  $seslp_required_plan = strtolower((string) $seslp_required_plan);

  switch ($seslp_required_plan) {
    case 'free':
      // Allows free features when any plan is activated
      $seslp_is_allowed = $seslp_is_free || $seslp_is_pro || $seslp_is_max;
      break;

    case 'pro':
      // Only allowed on Pro or higher
      $seslp_is_allowed = $seslp_is_pro || $seslp_is_max;
      break;

    case 'max':
      // Only allowed on max
      $seslp_is_allowed = $seslp_is_max;
      break;

    default:
      $seslp_is_allowed = false;
  }

  $seslp_provider_allowed[strtolower((string) $seslp_provider_key)] = $seslp_is_allowed;
}

$seslp_provider_allowed = apply_filters('seslp_provider_allowed', $seslp_provider_allowed, $seslp_current_plan);

// Provider list (filtered)
$seslp_providers = [];

if (class_exists('SESLP_Providers_Registry')) {
  $seslp_providers_all = SESLP_Providers_Registry::list();
  $seslp_providers = array_values(array_filter($seslp_providers_all, function ($seslp_provider_key) use ($seslp_provider_allowed) {
    $seslp_provider_key = strtolower((string) $seslp_provider_key);
    return isset($seslp_provider_allowed[$seslp_provider_key]) && true === $seslp_provider_allowed[$seslp_provider_key];
  }));
  unset($seslp_providers_all);
}