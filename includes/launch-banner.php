<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Render launch promotion banner on settings page.
 */
function seslp_render_launch_promo_banner(): void {
  // Promotion end date (YYYY-MM-DD)
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
      $title = sprintf(
        /* translators: %s: promotion end date text. */
        __( 'Launch Promotion (%s)', 'simple-easy-social-login-oauth-login' ),
        __( 'Valid until February 2026.', 'simple-easy-social-login-oauth-login' )
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
          __( 'Get <strong>%1$s OFF PRO features</strong> (%2$s).', 'simple-easy-social-login-oauth-login' ),
          esc_html($discount_text),
          esc_html($providers_text)
        )
      );
    ?>
    <br>
    <a href="<?php echo esc_url( SESLP_Helpers::get_upgrade_url() ); ?>" target="_blank" rel="noopener noreferrer">
      <?php echo esc_html__( 'Upgrade to PRO & Apply Coupon', 'simple-easy-social-login-oauth-login' ); ?>
    </a>
    <br>
  </p>
</div>
<?php
}