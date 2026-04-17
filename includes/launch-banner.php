<?php
/**
 * Promotional banner renderer.
 *
 * Responsible for:
 * - displaying time-limited promotional notices in the admin UI,
 * - supporting configurable promotion end dates,
 * - skipping output for Pro users and expired campaigns,
 * - ensuring banner text remains localization-ready.
 */
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Render the launch promotion banner on the plugin settings page.
 *
 * The banner is shown only while the promotion is active and only for
 * users who do not already have Pro access.
 *
 * @return void
 */
function seslp_render_launch_promo_banner(): void {
  // Promotion end date in YYYY-MM-DD format.
  $promo_end_date = '2026-02-28';
  $today = current_time('Y-m-d');
  $is_pro_user = isset($GLOBALS['can_pro_features']) && true === (bool) $GLOBALS['can_pro_features'];

  if ($today > $promo_end_date || $is_pro_user) {
    return;
  }
  ?>
<div class="notice notice-info seslp-launch-banner">
  <p class="seslp-launch-title">
    🎉 <?php
      $valid_until = sprintf(
        /* translators: %s: promotion end date (YYYY-MM-DD). */
        __('Valid until %s.', 'simple-easy-social-login-oauth-login'),
        esc_html($promo_end_date)
      );

      $title = sprintf(
        /* translators: %s: promotion validity text. */
        __('Launch Promotion (%s)', 'simple-easy-social-login-oauth-login'),
        $valid_until
      );

      echo esc_html($title);
    ?>
  </p>
  <p>
    <?php
      $providers_text = 'Naver, Kakao, Line';
      $discount_text  = '30%';

      echo wp_kses_post(
        sprintf(
          /* translators: 1: discount text (e.g. 30%), 2: list of PRO providers. */
          __('Get <strong>%1$s OFF PRO features</strong> (%2$s).', 'simple-easy-social-login-oauth-login'),
          esc_html($discount_text),
          esc_html($providers_text)
        )
      );
    ?>
    <br>
    <a href="<?php echo esc_url(SESLP_Helpers::get_upgrade_url()); ?>" target="_blank" rel="noopener noreferrer">
      <?php echo esc_html__('Upgrade to PRO & Apply Coupon', 'simple-easy-social-login-oauth-login'); ?>
    </a>
    <br>
  </p>
</div>
<?php
}