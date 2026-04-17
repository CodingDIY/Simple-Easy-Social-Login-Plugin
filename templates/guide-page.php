<?php
/**
 * Admin guide page template.
 *
 * Expected variables (provided by SESLP_Guides controller):
 * - string $page_title Page title.
 * - string $guide_html Pre-sanitized HTML content.
 *
 * Notes:
 * - $guide_html is assumed to be sanitized before reaching this template.
 * - This template performs minimal escaping for safe output.
 */
if (!defined('ABSPATH')) {
  exit; // Prevent direct access.
}
?>
<div class="wrap seslp-guide-wrap">
  <h1><?php echo esc_html( $page_title ); ?></h1>
  <div class="seslp-guide-content">
    <?php
    // Output sanitized guide HTML or fallback message
    if (!empty($guide_html)) {
      echo wp_kses_post($guide_html);
    } else {
      echo '<p>' . esc_html__('Guide content is not available.', 'simple-easy-social-login-oauth-login') . '</p>';
    }
    ?>
  </div>
</div>