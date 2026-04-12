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
$seslp_raw_opts = get_option('seslp_options', []);
$seslp_opts     = is_array($seslp_raw_opts) ? $seslp_raw_opts : [];
$seslp_redir    = $seslp_opts['redirect'] ?? [];
$seslp_mode     = isset($seslp_redir['mode']) ? (string) $seslp_redir['mode'] : 'front';
$seslp_custom   = isset($seslp_redir['custom_url']) ? (string) $seslp_redir['custom_url'] : '';

// Base labels (fallback)
$seslp_base_labels = array(
	'id'     => __( 'Client ID', 'simple-easy-social-login-oauth-login' ),
	'secret' => __( 'Client Secret', 'simple-easy-social-login-oauth-login' ),
);

// Provider-specific overrides
$seslp_label_overrides = array(
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
$seslp_docs_base = defined('SESLP_DOCS_BASE') ? rtrim((string) SESLP_DOCS_BASE, '/') : '';
?>

<div class="wrap">
  <h1><?php echo esc_html__('Simple Easy Social Login', 'simple-easy-social-login-oauth-login'); ?></h1>

  <?php
    // Show settings updated / error messages on our custom settings page
    if (isset($_GET['settings-updated'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading settings API redirect flag.
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

      <?php foreach ($providers as $seslp_prov) { 
        // Resolve labels with overrides, fallback to base
        $seslp_id_label     = $seslp_label_overrides[$seslp_prov]['id']     ?? $seslp_base_labels['id'];
        $seslp_secret_label = $seslp_label_overrides[$seslp_prov]['secret'] ?? $seslp_base_labels['secret'];

        // Read current values via helpers to reuse option cache and normalization
        $seslp_id_val     = method_exists('SESLP_Helpers', 'get_client_id')
          ? SESLP_Helpers::get_client_id($seslp_prov)
          : '';
        $seslp_secret_val = method_exists('SESLP_Helpers', 'get_client_secret')
          ? SESLP_Helpers::get_client_secret($seslp_prov)
          : '';

        // Build input names that keep the unified structure
        $seslp_name_id     = "seslp_options[providers][{$seslp_prov}][client_id]";
        $seslp_name_secret = "seslp_options[providers][{$seslp_prov}][client_secret]";
      ?>

      <tbody class="<?php echo 'login_' . esc_html(ucfirst($seslp_prov)); ?>">
        <tr>
          <td colspan="2" class="seslp-no-padding-left">
            <h2 class="seslp-section-title">
              <?php echo esc_html( ucfirst( $seslp_prov ) ); ?>
              <?php esc_html_e( 'Login', 'simple-easy-social-login-oauth-login' ); ?>
            </h2>
          </td>
        </tr>
        <tr>
          <th scope="row">
            <label for="seslp_<?php echo esc_attr($seslp_prov); ?>_client_id">
              <?php echo esc_html($seslp_id_label); ?>
            </label>
          </th>
          <td>
            <input type="text" id="seslp_<?php echo esc_attr($seslp_prov); ?>_client_id"
              name="<?php echo esc_attr($seslp_name_id); ?>" value="<?php echo esc_attr($seslp_id_val); ?>" class="regular-text"
              autocomplete="off" />
          </td>
        </tr>
        <tr>
          <th scope="row">
            <label for="seslp_<?php echo esc_attr($seslp_prov); ?>_client_secret">
              <?php echo esc_html($seslp_secret_label); ?>
            </label>
          </th>
          <td>
            <div class="seslp-secret-wrapper">
              <input type="password" id="seslp_<?php echo esc_attr($seslp_prov); ?>_client_secret"
                name="<?php echo esc_attr($seslp_name_secret); ?>" value="<?php echo esc_attr($seslp_secret_val); ?>"
                class="regular-text" autocomplete="off" />
              <button type="button" class="button button-link seslp-secret-toggle"
                aria-label="<?php esc_attr_e('Show or hide secret', 'simple-easy-social-login-oauth-login'); ?>">
                <span class="dashicons dashicons-visibility"></span>
              </button>
            </div>
          </td>
        </tr>
        <?php if (!empty($seslp_docs_base)) { ?>
        <tr>
          <th scope="row"></th>
          <td>
            <?php
              // Build provider-specific docs URL using base domain constant
              $seslp_prov_slug = strtolower((string) $seslp_prov);

              /** Allow overrides for provider-specific documentation links */
              $seslp_doc_url = (string) apply_filters(
                'seslp_provider_docs_url',
                $seslp_docs_base . '/docs/plugins/se-social-login/' . $seslp_prov_slug,
                $seslp_prov,
                $seslp_docs_base
              );
            ?>
            <p class="description">
              <?php
                /* translators: %s: Documentation link. */
                printf(
                  esc_html__( 'Need help? Follow %s', 'simple-easy-social-login-oauth-login' ),
                  '<a href="' . esc_url($seslp_doc_url) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'this documentation page', 'simple-easy-social-login-oauth-login' ) . '</a>'
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
                  <?php checked($seslp_mode, 'dashboard'); ?> />
                <?php echo esc_html__('Dashboard (wp-admin)', 'simple-easy-social-login-oauth-login'); ?>
              </label>
              <label class="seslp-radio-label">
                <input type="radio" name="seslp_options[redirect][mode]" value="profile"
                  <?php checked($seslp_mode, 'profile'); ?> />
                <?php echo esc_html__('Profile page (wp-admin/profile.php)', 'simple-easy-social-login-oauth-login'); ?>
              </label>
              <label class="seslp-radio-label">
                <input type="radio" name="seslp_options[redirect][mode]" value="front"
                  <?php checked($seslp_mode, 'front'); ?> />
                <?php echo esc_html__('Front page (home) — Default', 'simple-easy-social-login-oauth-login'); ?>
              </label>
              <label class="seslp-radio-label">
                <input type="radio" name="seslp_options[redirect][mode]" value="custom"
                  <?php checked($seslp_mode, 'custom'); ?> />
                <?php echo esc_html__('Custom URL', 'simple-easy-social-login-oauth-login'); ?>
              </label>
              <input type="url" class="regular-text" name="seslp_options[redirect][custom_url]"
                value="<?php echo esc_attr($seslp_custom); ?>" placeholder="https://example.com/after-login" />
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
        /* translators: %s: Required plan name, e.g. Pro. */
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
            <?php $seslp_enabled = !empty($seslp_opts['debug']['enabled']) ? 1 : 0; ?>
            <label class="seslp-inline-label">
              <input type="radio" name="seslp_options[debug][enabled]" value="1" <?php checked($seslp_enabled, 1); ?> />
              <?php esc_html_e('On', 'simple-easy-social-login-oauth-login'); ?>
            </label>
            <label>
              <input type="radio" name="seslp_options[debug][enabled]" value="0" <?php checked($seslp_enabled, 0); ?> />
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
              $seslp_current = $seslp_opts['debug']['timezone'] ?? 'UTC';
              foreach ([
                'UTC'   => __('UTC', 'simple-easy-social-login-oauth-login'),
                'local' => __('WordPress Local', 'simple-easy-social-login-oauth-login'),
                'both'  => __('Both', 'simple-easy-social-login-oauth-login'),
              ] as $seslp_val => $seslp_label) {
                printf(
                  '<option value="%s" %s>%s</option>',
                  esc_attr($seslp_val),
                  selected($seslp_current, $seslp_val, false),
                  esc_html($seslp_label)
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
            <?php $seslp_layout = isset($seslp_opts['ui']['layout']) ? (string) $seslp_opts['ui']['layout'] : 'list'; ?>
            <fieldset>
              <label>
                <input type="radio" name="seslp_options[ui][layout]" value="list" <?php checked($seslp_layout, 'list'); ?> />
                <?php echo esc_html__('List (logo + text, vertical)', 'simple-easy-social-login-oauth-login'); ?>
              </label>
              <br />
              <label>
                <input type="radio" name="seslp_options[ui][layout]" value="icons"
                  <?php checked($seslp_layout, 'icons'); ?> />
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
        /* translators: %s: Required plan name, e.g. Pro. */
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
      $seslp_rm  = get_option('seslp_uninstall_remove_data'); // 'yes' or ''
      $seslp_deep = get_option('seslp_uninstall_deep_clean'); // 'yes' or ''
    ?>

    <table id="seslp-uninstall-table" class="form-table" role="presentation">
      <tbody>
        <tr>
          <th scope="row"><?php echo esc_html__('Data removal', 'simple-easy-social-login-oauth-login'); ?></th>
          <td>
            <input type="hidden" name="seslp_uninstall_remove_data" value="" />
            <label>
              <input type="checkbox" name="seslp_uninstall_remove_data" value="yes"
                <?php checked(is_string($seslp_rm) && strtolower($seslp_rm) === 'yes'); ?> data-seslp-role="remove-data" />
              <?php echo esc_html__('Delete plugin data on uninstall', 'simple-easy-social-login-oauth-login'); ?>
            </label>
            <p class="description">
              <?php echo esc_html__('If checked, plugin options, transients, and user meta created by this plugin will be removed when you uninstall.', 'simple-easy-social-login-oauth-login'); ?>
            </p>
          </td>
        </tr>
        <tr id="seslp-deep-row"
          class="<?php echo (is_string($seslp_rm) && strtolower($seslp_rm) === 'yes') ? '' : 'seslp-hidden'; ?>">
          <th scope="row"><?php echo esc_html__('Deep clean', 'simple-easy-social-login-oauth-login'); ?></th>
          <td>
            <input type="hidden" name="seslp_uninstall_deep_clean" value="" />
            <label>
              <input type="checkbox" name="seslp_uninstall_deep_clean" value="yes"
                <?php checked(is_string($seslp_deep) && strtolower($seslp_deep) === 'yes'); ?> data-seslp-role="deep-clean" />
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
        /* translators: %s: Required plan name, e.g. Pro. */
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