<?php
/**
 * Social login buttons template.
 *
 * Responsible for:
 * - rendering social login buttons for all configured providers,
 * - displaying inline error messages based on public error codes,
 * - conditionally hiding providers that are not properly configured,
 * - supporting multiple UI layouts (list / icons).
 *
 * Expected variables:
 * - SESLP_Plugin $this Main plugin instance.
 */
if (!defined('ABSPATH')) {
  exit;
}

// Retrieve all registered provider slugs.
$seslp_providers = SESLP_Providers_Registry::list();

// Base URL used for loading provider logo assets.
$seslp_base_url = SESLP_Plugin::instance()->url;

$seslp_opts = get_option('seslp_options', []);
$seslp_opts = is_array($seslp_opts) ? $seslp_opts : [];
$seslp_layout = isset($seslp_opts['ui']['layout']) && is_string($seslp_opts['ui']['layout'])
    ? sanitize_key($seslp_opts['ui']['layout'])
    : 'list';

// Helper: determine if a provider is fully configured (supports legacy option fallback).
$seslp_is_configured = static function (string $seslp_prov): bool {
  $seslp_prov = sanitize_key($seslp_prov);

  $id     = SESLP_Helpers::get_client_id($seslp_prov);
  $secret = SESLP_Helpers::get_client_secret($seslp_prov);

  // Fallback to legacy flat options (e.g., seslp_weibo_client_id / _client_secret)
  if ($id === '' || $secret === '') {
    $legacy_id     = trim((string) get_option('seslp_' . $seslp_prov . '_client_id', ''));
    $legacy_secret = trim((string) get_option('seslp_' . $seslp_prov . '_client_secret', ''));
    if ($id === '')     $id     = $legacy_id;
    if ($secret === '') $secret = $legacy_secret;
  }

  return ($id !== '' && $secret !== '');
};
?>
<div class="seslp-logins">
  <p><strong><?php esc_html_e('Social Login with:', 'simple-easy-social-login-oauth-login'); ?></strong></p>

  <?php
  // Map allowed public error codes to user-friendly inline messages.
  $seslp_friendly_map = [
    'email_exists'                   => __('This email is already registered on this site. Please sign in with the originally linked method (username/password or the same social provider).', 'simple-easy-social-login-oauth-login'),
    'invalid_state'                  => __('Your session has expired or the login attempt was invalid. Please try again.', 'simple-easy-social-login-oauth-login'),
    'invalid_nonce'                  => __('Your session has expired or the login attempt was invalid. Please try again.', 'simple-easy-social-login-oauth-login'),
    'token_exchange_failed'          => __('Failed to obtain access token from the provider. Please try again.', 'simple-easy-social-login-oauth-login'),
    'oauth_exception'                => __('A social login error occurred while communicating with the provider. Please try again.', 'simple-easy-social-login-oauth-login'),
    'oauth_failed'                   => __('Social login failed. Please try again.', 'simple-easy-social-login-oauth-login'),
    'invalid_provider'               => __('The requested social login provider is invalid.', 'simple-easy-social-login-oauth-login'),
    'provider_not_allowed'           => __('This social login provider is not allowed on this site.', 'simple-easy-social-login-oauth-login'),
    'config_missing'                 => __('This social login provider is not configured correctly. Please contact the site administrator.', 'simple-easy-social-login-oauth-login'),
    'email_missing'                  => __('We could not retrieve an email address from the provider. Please use another login method or contact the site administrator.', 'simple-easy-social-login-oauth-login'),
    'account_link_failed'            => __('We could not link your social account to this site. Please try again.', 'simple-easy-social-login-oauth-login'),
    'registration_disabled_by_plugin' => __('New account creation through social login is currently disabled on this site.', 'simple-easy-social-login-oauth-login'),
  ];

  $seslp_err = SESLP_Helpers::get_public_error_code();
  if ($seslp_err !== '' && !array_key_exists($seslp_err, $seslp_friendly_map)) {
    $seslp_err = '';
  }

  if ($seslp_err !== '' && isset($seslp_friendly_map[$seslp_err])) {
    echo '<div class="seslp-inline-error is-error"><small>'
      . esc_html($seslp_friendly_map[$seslp_err])
      . '</small></div>';
  }
  ?>

  <div class="seslp-buttons layout-<?php echo esc_attr($seslp_layout); ?>">
    <?php foreach ($seslp_providers as $seslp_prov) {
      // Check if provider is configured and class is available before rendering.
      $seslp_configured = $seslp_is_configured($seslp_prov) && class_exists('SESLP_Provider_' . ucfirst($seslp_prov));

      // Skip providers that are not configured or missing their implementation.
      if (!$seslp_configured) {
        continue;
      }

      // Build authentication URL and UI elements for rendering.
      $seslp_raw_url = (string) SESLP_Plugin::instance()->auth_url($seslp_prov);
      // Safety guard: skip rendering if URL is invalid or empty.
      if ($seslp_raw_url === '' || $seslp_raw_url === '#') {
        continue;
      }
      $seslp_href     = $seslp_raw_url;
      $seslp_img_url  = $seslp_base_url . 'assets/images/img-logo-' . $seslp_prov . '.png';
      $seslp_label    = sprintf(
        /* translators: %s: Social provider name (e.g., Google, Facebook). */
        esc_html__('%s Login', 'simple-easy-social-login-oauth-login'),
        ucfirst($seslp_prov)
      );
      ?>
    <a class="seslp-btn seslp-<?php echo esc_attr($seslp_prov); ?>" href="<?php echo esc_url($seslp_href); ?>">
      <img class="seslp-logo" src="<?php echo esc_url($seslp_img_url); ?>" alt="<?php echo esc_attr(ucfirst($seslp_prov)); ?>" />
      <?php if ($seslp_layout !== 'icons') { ?>
      <span class="seslp-label"><?php echo esc_html($seslp_label); ?></span>
      <?php } ?>
    </a>
    <?php } ?>
  </div>
</div>