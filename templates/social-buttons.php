<?php
/**
 * Social Login Buttons Template
 *
 * Displays social login buttons with logo + text.
 *
 * @var SESLP_Plugin $this
 */
if (!defined('ABSPATH')) {
  exit;
}

// Providers
$seslp_providers = SESLP_Providers_Registry::list();

// Plugin base URL
$seslp_base_url = SESLP_Plugin::instance()->url;

$seslp_opts = get_option('seslp_options', []);
$seslp_opts = is_array($seslp_opts) ? $seslp_opts : [];
$seslp_layout = isset($seslp_opts['ui']['layout']) && is_string($seslp_opts['ui']['layout'])
    ? sanitize_key($seslp_opts['ui']['layout'])
    : 'list';

// Local helper: check if provider credentials exist (uses cached helpers first, legacy fallback second).
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
  // Inline error: map an allowed seslp_err query flag to a friendly message.
  $seslp_friendly_map = [
    'email_exists'   => __('This email is already registered on this site. Please sign in with the originally linked method (username/password or the same social provider).', 'simple-easy-social-login-oauth-login'),
    'invalid_state'  => __('Your session has expired or the login attempt was invalid. Please try again.', 'simple-easy-social-login-oauth-login'),
    'invalid_nonce'  => __('Your session has expired or the login attempt was invalid. Please try again.', 'simple-easy-social-login-oauth-login'),
    'unknown_error'  => __('An unknown error occurred. Please try again.', 'simple-easy-social-login-oauth-login'),
    'token_failed'   => __('Failed to obtain access token from the provider. Please try again.', 'simple-easy-social-login-oauth-login'),
    'profile_failed' => __('Failed to fetch your profile information from the provider. Please try again.', 'simple-easy-social-login-oauth-login'),
  ];

  $seslp_err = '';
  if (isset($_GET['seslp_err'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only redirect flag; validated against a strict allowlist before use.
    $seslp_err_candidate = sanitize_key(wp_unslash($_GET['seslp_err'])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only redirect flag; validated against a strict allowlist before use.
    if (array_key_exists($seslp_err_candidate, $seslp_friendly_map)) {
      $seslp_err = $seslp_err_candidate;
    }
  }

  if ($seslp_err !== '') {
    echo '<div class="seslp-inline-error is-error"><small>'
      . esc_html($seslp_friendly_map[$seslp_err])
      . '</small></div>';
  }
  ?>

  <div class="seslp-buttons layout-<?php echo esc_attr($seslp_layout); ?>">
    <?php foreach ($seslp_providers as $seslp_prov) {
      // Decide if provider is configured before asking for an auth URL
      $seslp_configured = $seslp_is_configured($seslp_prov) && class_exists('SESLP_Provider_' . ucfirst($seslp_prov));

      // If not configured (or provider class missing), do not render this provider at all.
      if (!$seslp_configured) {
        continue;
      }

      // Build URL and UI pieces
      $seslp_raw_url = (string) SESLP_Plugin::instance()->auth_url($seslp_prov);
      if ($seslp_raw_url === '' || $seslp_raw_url === '#') {
        // Safety: if an unexpected empty URL sneaks in, skip rendering
        continue;
      }
      $seslp_href     = $seslp_raw_url;
      $seslp_img_url  = $seslp_base_url . 'assets/images/img-logo-' . $seslp_prov . '.png';
      $seslp_label    = ucfirst($seslp_prov) . ' Login';
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