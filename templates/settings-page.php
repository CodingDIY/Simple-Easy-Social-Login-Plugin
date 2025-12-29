<?php
/**
 * Admin Settings Page Template (custom labels per provider)
 *
 * Renders provider-specific labels while keeping underlying option keys unified:
 * seslp_options[providers][{provider}][client_id|client_secret]
 *
 * @var SESLP_Plugin $this
 */
if (!defined('ABSPATH')) {
  exit;
}

// Get options array (normalized)
$raw_opts = get_option('seslp_options', []);
$opts     = is_array($raw_opts) ? $raw_opts : [];
$redir    = $opts['redirect'] ?? [];
$mode     = isset($redir['mode']) ? (string) $redir['mode'] : 'front';
$custom   = isset($redir['custom_url']) ? (string) $redir['custom_url'] : '';

// Base labels (fallback)
$base_labels = array(
	'id'     => __( 'Client ID', 'simple-easy-social-login-oauth-login' ),
	'secret' => __( 'Client Secret', 'simple-easy-social-login-oauth-login' ),
);

// Provider-specific overrides
$label_overrides = array(
	'google' => array(
		'id'     => __( 'Client ID', 'simple-easy-social-login-oauth-login' ),
		'secret' => __( 'Client Secret', 'simple-easy-social-login-oauth-login' ),
	),
	'facebook' => array(
		'id'     => __( 'App ID', 'simple-easy-social-login-oauth-login' ),
		'secret' => __( 'App Secret', 'simple-easy-social-login-oauth-login' ),
	),
	'linkedin' => array(
		'id'     => __( 'Client ID', 'simple-easy-social-login-oauth-login' ),
		'secret' => __( 'Client Secret', 'simple-easy-social-login-oauth-login' ),
	),
	'naver' => array(
		'id'     => __( 'Client ID', 'simple-easy-social-login-oauth-login' ),
		'secret' => __( 'Client Secret', 'simple-easy-social-login-oauth-login' ),
	),
	'kakao' => array(
		'id'     => __( 'REST API Key', 'simple-easy-social-login-oauth-login' ),
		'secret' => __( 'Client Secret', 'simple-easy-social-login-oauth-login' ),
	),
	'line' => array(
		'id'     => __( 'Channel ID', 'simple-easy-social-login-oauth-login' ),
		'secret' => __( 'Channel Secret', 'simple-easy-social-login-oauth-login' ),
	),
	'weibo' => array(
		'id'     => __( 'App Key', 'simple-easy-social-login-oauth-login' ),
		'secret' => __( 'App Secret', 'simple-easy-social-login-oauth-login' ),
	),
);

// Use global Freemius plan vars
global $is_free, $is_pro, $is_max, $can_pro_features, $can_max_features, $provider_allowed, $providers;

// Documents site url
$docs_base = defined('SESLP_DOCS_BASE') ? rtrim((string) SESLP_DOCS_BASE, '/') : '';
?>

<div class="wrap">
  <h1><?php echo esc_html__('Simple Easy Social Login', 'simple-easy-social-login-oauth-login'); ?></h1>

  <?php
    // Show settings updated / error messages on our custom settings page
    if (isset($_GET['settings-updated'])) {
      add_settings_error(
        'seslp_messages',
        'seslp_message',
        esc_html__('Settings saved.', 'simple-easy-social-login-oauth-login'),
        'updated'
      );
    }
    settings_errors('seslp_messages');
  ?>

  <form action="options.php" method="post">
    <?php
      // Output settings nonce/hidden fields for seslp_options
      settings_fields('seslp_group');
    ?>

    <table id="seslp-login-table" class="form-table" role="presentation">

      <?php foreach ($providers as $prov) { 
        // Resolve labels with overrides, fallback to base
        $id_label     = $label_overrides[$prov]['id']     ?? $base_labels['id'];
        $secret_label = $label_overrides[$prov]['secret'] ?? $base_labels['secret'];

        // Read current values via helpers to reuse option cache and normalization
        $id_val     = method_exists('SESLP_Helpers', 'get_client_id')
          ? SESLP_Helpers::get_client_id($prov)
          : '';
        $secret_val = method_exists('SESLP_Helpers', 'get_client_secret')
          ? SESLP_Helpers::get_client_secret($prov)
          : '';

        // Build input names that keep the unified structure
        $name_id     = "seslp_options[providers][{$prov}][client_id]";
        $name_secret = "seslp_options[providers][{$prov}][client_secret]";
      ?>

      <tbody class="<?php echo 'login_' . esc_html(ucfirst($prov)); ?>">
        <tr>
          <td colspan="2" class="seslp-no-padding-left">
            <h2 class="seslp-section-title">
              <?php echo esc_html( ucfirst( $prov ) ); ?>
              <?php esc_html_e( 'Login', 'simple-easy-social-login-oauth-login' ); ?>
            </h2>
          </td>
        </tr>
        <tr>
          <th scope="row">
            <label for="seslp_<?php echo esc_attr($prov); ?>_client_id">
              <?php echo esc_html($id_label); ?>
            </label>
          </th>
          <td>
            <input type="text" id="seslp_<?php echo esc_attr($prov); ?>_client_id"
              name="<?php echo esc_attr($name_id); ?>" value="<?php echo esc_attr($id_val); ?>" class="regular-text"
              autocomplete="off" />
          </td>
        </tr>
        <tr>
          <th scope="row">
            <label for="seslp_<?php echo esc_attr($prov); ?>_client_secret">
              <?php echo esc_html($secret_label); ?>
            </label>
          </th>
          <td>
            <div class="seslp-secret-wrapper">
              <input type="password" id="seslp_<?php echo esc_attr($prov); ?>_client_secret"
                name="<?php echo esc_attr($name_secret); ?>" value="<?php echo esc_attr($secret_val); ?>"
                class="regular-text" autocomplete="off" />
              <button type="button" class="button button-link seslp-secret-toggle"
                aria-label="<?php esc_attr_e('Show or hide secret', 'simple-easy-social-login-oauth-login'); ?>">
                <span class="dashicons dashicons-visibility"></span>
              </button>
            </div>
          </td>
        </tr>
        <?php if (!empty($docs_base)) { ?>
        <tr>
          <th scope="row"></th>
          <td>
            <?php
              // Build provider-specific docs URL using base domain constant
              $prov_slug = strtolower((string) $prov);

              /** Allow overrides for provider-specific documentation links */
              $doc_url = (string) apply_filters(
                'seslp_provider_docs_url',
                $docs_base . '/docs/plugins/se-social-login/' . $prov_slug,
                $prov,
                $docs_base
              );
            ?>
            <p class="description">
              <?php
                printf(
                  esc_html__( 'Need help? Follow %s', 'simple-easy-social-login-oauth-login' ),
                  '<a href="' . esc_url($doc_url) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'this documentation page', 'simple-easy-social-login-oauth-login' ) . '</a>'
                );
              ?>
            </p>
          </td>
        </tr>
        <?php } ?>
      </tbody>

      <?php } ?>

    </table>

    <br>

    <h2 class="seslp-section-title">
      <?php echo esc_html__('Post-login Redirect', 'simple-easy-social-login-oauth-login'); ?></h2>

    <?php if ($can_pro_features) { ?>

    <table id="seslp-redirect-table" class="form-table" role="presentation">
      <tbody>
        <tr>
          <th scope="row"><?php echo esc_html__('Redirect to', 'simple-easy-social-login-oauth-login'); ?></th>
          <td>
            <fieldset>
              <label class="seslp-radio-label">
                <input type="radio" name="seslp_options[redirect][mode]" value="dashboard"
                  <?php checked($mode, 'dashboard'); ?> />
                <?php echo esc_html__('Dashboard (wp-admin)', 'simple-easy-social-login-oauth-login'); ?>
              </label>
              <label class="seslp-radio-label">
                <input type="radio" name="seslp_options[redirect][mode]" value="profile"
                  <?php checked($mode, 'profile'); ?> />
                <?php echo esc_html__('Profile page (wp-admin/profile.php)', 'simple-easy-social-login-oauth-login'); ?>
              </label>
              <label class="seslp-radio-label">
                <input type="radio" name="seslp_options[redirect][mode]" value="front"
                  <?php checked($mode, 'front'); ?> />
                <?php echo esc_html__('Front page (home) — Default', 'simple-easy-social-login-oauth-login'); ?>
              </label>
              <label class="seslp-radio-label">
                <input type="radio" name="seslp_options[redirect][mode]" value="custom"
                  <?php checked($mode, 'custom'); ?> />
                <?php echo esc_html__('Custom URL', 'simple-easy-social-login-oauth-login'); ?>
              </label>
              <input type="url" class="regular-text" name="seslp_options[redirect][custom_url]"
                value="<?php echo esc_attr($custom); ?>" placeholder="https://example.com/after-login" />
              <p class="description">
                <?php esc_html_e("When 'Custom URL' is selected, users are redirected to this address after login.", 'simple-easy-social-login-oauth-login'); ?>
              </p>
            </fieldset>
          </td>
        </tr>
      </tbody>
    </table>

    <?php } else { ?>

    <p class="description">
      <?php
        printf(
          esc_html__( 'This feature is available on %s and above.', 'simple-easy-social-login-oauth-login' ),
          'Pro'
        );
      ?>
      <a href="<?php echo esc_url( admin_url('admin.php?page=seslp-settings-pricing') ); ?>">
        <?php echo esc_html__( 'See plans', 'simple-easy-social-login-oauth-login' ); ?>
      </a>
    </p>

    <?php } ?>

    <br>

    <h2 class="seslp-section-title"><?php echo esc_html__('Debug Logging', 'simple-easy-social-login-oauth-login'); ?>
    </h2>

    <table id="seslp-debug-table" class="form-table" role="presentation">
      <tbody>
        <!-- // Inside render_settings_page() table after UI section -->
        <tr>
          <th scope="row"><?php esc_html_e('Enable logging', 'simple-easy-social-login-oauth-login'); ?></th>
          <td>
            <?php $enabled = !empty($opts['debug']['enabled']) ? 1 : 0; ?>
            <label class="seslp-inline-label">
              <input type="radio" name="seslp_options[debug][enabled]" value="1" <?php checked($enabled, 1); ?> />
              <?php esc_html_e('On', 'simple-easy-social-login-oauth-login'); ?>
            </label>
            <label>
              <input type="radio" name="seslp_options[debug][enabled]" value="0" <?php checked($enabled, 0); ?> />
              <?php esc_html_e('Off', 'simple-easy-social-login-oauth-login'); ?>
            </label>
            <p class="description">
              <?php esc_html_e('Writes to wp-content/SESLP-debug.log when enabled.', 'simple-easy-social-login-oauth-login'); ?>
            </p>
          </td>
        </tr>
        <tr id="seslp-tz-row">
          <th scope="row"><?php esc_html_e('Logger Timezone', 'simple-easy-social-login-oauth-login'); ?></th>
          <td>
            <select name="seslp_options[debug][timezone]">
              <?php
              $current = $opts['debug']['timezone'] ?? 'UTC';
              foreach ([
                'UTC'   => __('UTC', 'simple-easy-social-login-oauth-login'),
                'local' => __('WordPress Local', 'simple-easy-social-login-oauth-login'),
                'both'  => __('Both', 'simple-easy-social-login-oauth-login'),
              ] as $val => $label) {
                printf(
                  '<option value="%s" %s>%s</option>',
                  esc_attr($val),
                  selected($current, $val, false),
                  esc_html($label)
                );
              }
              ?>
            </select>
            <p class="description">
              <?php esc_html_e('Choose timestamp format for debug logs.', 'simple-easy-social-login-oauth-login'); ?>
            </p>
          </td>
        </tr>
      </tbody>
    </table>

    <br>

    <h2 class="seslp-section-title"><?php echo esc_html__('UI', 'simple-easy-social-login-oauth-login'); ?></h2>

    <?php if ($can_pro_features) { ?>

    <table id="seslp-ui-table" class="form-table" role="presentation">
      <tbody>
        <tr>
          <th scope="row"><?php echo esc_html__('Login buttons layout', 'simple-easy-social-login-oauth-login'); ?></th>
          <td>
            <?php $layout = isset($opts['ui']['layout']) ? (string) $opts['ui']['layout'] : 'list'; ?>
            <fieldset>
              <label>
                <input type="radio" name="seslp_options[ui][layout]" value="list" <?php checked($layout, 'list'); ?> />
                <?php echo esc_html__('List (logo + text, vertical)', 'simple-easy-social-login-oauth-login'); ?>
              </label>
              <br />
              <label>
                <input type="radio" name="seslp_options[ui][layout]" value="icons"
                  <?php checked($layout, 'icons'); ?> />
                <?php echo esc_html__('Icons only (horizontal)', 'simple-easy-social-login-oauth-login'); ?>
              </label>
              <p class="description">
                <?php echo esc_html__('When icons-only is selected, only provider logos are shown in a horizontal row.', 'simple-easy-social-login-oauth-login'); ?>
              </p>
            </fieldset>
          </td>
        </tr>
      </tbody>
    </table>

    <?php } else { ?>

    <p class="description">
      <?php
          printf(
            esc_html__( 'This section is available on %s and above.', 'simple-easy-social-login-oauth-login' ),
            'Pro'
          );
        ?>
      <a href="<?php echo esc_url( admin_url('admin.php?page=seslp-settings-pricing') ); ?>">
        <?php echo esc_html__( 'See plans', 'simple-easy-social-login-oauth-login' ); ?>
      </a>
    </p>

    <?php } ?>

    <br>

    <h2 class="seslp-section-title"><?php echo esc_html__('Shortcode', 'simple-easy-social-login-oauth-login'); ?></h2>

    <table id="seslp-shortcode-table" class="form-table" role="presentation">
      <tbody>
        <tr>
          <th scope="row"><?php echo esc_html__('Shortcode', 'simple-easy-social-login-oauth-login'); ?></th>
          <td>
            <code>[se_social_login]</code>
          </td>
        </tr>
      </tbody>
    </table>

    <br>

    <hr>

    <h2 class="seslp-section-title">
      <?php echo esc_html__('Uninstall Options', 'simple-easy-social-login-oauth-login'); ?></h2>

    <?php if ($can_pro_features) {
      // Read current flags
      $rm  = get_option('seslp_uninstall_remove_data'); // 'yes' or ''
      $deep = get_option('seslp_uninstall_deep_clean'); // 'yes' or ''
    ?>

    <table id="seslp-uninstall-table" class="form-table" role="presentation">
      <tbody>
        <tr>
          <th scope="row"><?php echo esc_html__('Data removal', 'simple-easy-social-login-oauth-login'); ?></th>
          <td>
            <input type="hidden" name="seslp_uninstall_remove_data" value="" />
            <label>
              <input type="checkbox" name="seslp_uninstall_remove_data" value="yes"
                <?php checked(is_string($rm) && strtolower($rm) === 'yes'); ?> data-seslp-role="remove-data" />
              <?php echo esc_html__('Delete plugin data on uninstall', 'simple-easy-social-login-oauth-login'); ?>
            </label>
            <p class="description">
              <?php echo esc_html__('If checked, plugin options, transients, and user meta created by this plugin will be removed when you uninstall.', 'simple-easy-social-login-oauth-login'); ?>
            </p>
          </td>
        </tr>
        <tr id="seslp-deep-row"
          class="<?php echo (is_string($rm) && strtolower($rm) === 'yes') ? '' : 'seslp-hidden'; ?>">
          <th scope="row"><?php echo esc_html__('Deep clean', 'simple-easy-social-login-oauth-login'); ?></th>
          <td>
            <input type="hidden" name="seslp_uninstall_deep_clean" value="" />
            <label>
              <input type="checkbox" name="seslp_uninstall_deep_clean" value="yes"
                <?php checked(is_string($deep) && strtolower($deep) === 'yes'); ?> data-seslp-role="deep-clean" />
              <?php echo esc_html__('Also drop custom tables / logs', 'simple-easy-social-login-oauth-login'); ?>
            </label>
            <p class="description">
              <?php echo esc_html__('Drops tables like seslp_login_logs if present. Irreversible.', 'simple-easy-social-login-oauth-login'); ?>
            </p>
          </td>
        </tr>
      </tbody>
    </table>

    <?php } else { ?>

    <p class="description">
      <?php
        printf(
          esc_html__( 'This section is available on %s and above.', 'simple-easy-social-login-oauth-login' ),
          'Pro'
        );
      ?>
      <a href="<?php echo esc_url( admin_url('admin.php?page=seslp-settings-pricing') ); ?>">
        <?php echo esc_html__( 'See plans', 'simple-easy-social-login-oauth-login' ); ?>
      </a>
    </p>

    <?php } ?>

    <?php submit_button( esc_html__( 'Save Changes', 'simple-easy-social-login-oauth-login' ) ); ?>
  </form>
</div>