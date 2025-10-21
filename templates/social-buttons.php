<?php
/**
 * Social Login Buttons Template
 *
 * Displays social login buttons with logo + text.
 *
 * @var SESLP_Plugin $this
 */
if (!defined('ABSPATH')) exit;

// Providers
$providers = SESLP_Providers_Registry::list();

// Plugin base URL
$base_url = SESLP_Plugin::instance()->url;

$opts = get_option('seslp_options', []);
$layout = isset($opts['ui']['layout']) ? $opts['ui']['layout'] : 'list';

// Local helper: check if provider credentials exist.
$is_configured = function (string $prov): bool {
  $opts = get_option('seslp_options', []);
  // 1) unified options first: seslp_options[providers][{prov}][client_id|client_secret]
  $cfg = isset($opts['providers'][$prov]) && is_array($opts['providers'][$prov]) ? $opts['providers'][$prov] : [];

  $id     = isset($cfg['client_id'])     ? trim((string) $cfg['client_id'])     : '';
  $secret = isset($cfg['client_secret']) ? trim((string) $cfg['client_secret']) : '';

  // 2) fallback to legacy flat options (e.g., seslp_weibo_client_id / _client_secret)
  if ($id === '' || $secret === '') {
    $legacy_id     = trim((string) get_option('seslp_' . $prov . '_client_id', ''));
    $legacy_secret = trim((string) get_option('seslp_' . $prov . '_client_secret', ''));
    if ($id === '')     $id     = $legacy_id;
    if ($secret === '') $secret = $legacy_secret;
  }

  return ($id !== '' && $secret !== '');
};
?>
<div class="seslp-logins">
  <p><strong><?php esc_html_e('Social Login with:', SESLP_Plugin::TD); ?></strong></p>

  <?php
  // Inline error: map seslp_err values to friendly messages
  if (isset($_GET['seslp_err'])) {
    $err = sanitize_key((string) $_GET['seslp_err']);
    $friendly_map = [
      'email_exists'   => __('This email is already registered on this site. Please sign in with the originally linked method (username/password or the same social provider).', SESLP_Plugin::TD),
      'invalid_state'  => __('Your session has expired or the login attempt was invalid. Please try again.', SESLP_Plugin::TD),
      'unknown_error'  => __('An unknown error occurred. Please try again.', SESLP_Plugin::TD),
      'token_failed'   => __('Failed to obtain access token from the provider. Please try again.', SESLP_Plugin::TD),
      'profile_failed' => __('Failed to fetch your profile information from the provider. Please try again.', SESLP_Plugin::TD),
    ];

    if (isset($friendly_map[$err])) {
      echo '<div class="seslp-inline-error is-error"><small>'
        . esc_html($friendly_map[$err])
        . '</small></div>';
    }
  }
  ?>

  <div class="seslp-buttons layout-<?php echo esc_attr($layout); ?>">
    <?php foreach ($providers as $prov) {
      // Decide if provider is configured before asking for an auth URL
      $configured = $is_configured($prov) && class_exists('SESLP_Provider_' . ucfirst($prov));

      // If not configured (or provider class missing), do not render this provider at all.
      if (!$configured) {
        continue;
      }

      // Build URL and UI pieces
      $raw_url = (string) SESLP_Plugin::instance()->auth_url($prov);
      if ($raw_url === '' || $raw_url === '#') {
        // Safety: if an unexpected empty URL sneaks in, skip rendering
        continue;
      }
      $href     = esc_url($raw_url);
      $img_url  = esc_url($base_url . 'assets/images/img-logo-' . $prov . '.png');
      $label    = esc_html(ucfirst($prov) . ' Login');
      ?>
    <a class="seslp-btn seslp-<?php echo esc_attr($prov); ?>" href="<?php echo $href; ?>">
      <img class="seslp-logo" src="<?php echo $img_url; ?>" alt="<?php echo esc_attr(ucfirst($prov)); ?>" />
      <?php if ($layout !== 'icons') { ?>
      <span class="seslp-label"><?php echo $label; ?></span>
      <?php } ?>
    </a>
    <?php } ?>
  </div>
</div>