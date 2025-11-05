<?php
/**
 * Template: Guide Page
 * Variables provided by controller (SESLP_Guides::render_page):
 * - string $page_title : Page title
 * - string $guide_html : Already-sanitized HTML converted from Markdown
 * - ?string $guide_file: Absolute path to the loaded markdown (or null)
 */
if (!defined('ABSPATH')) exit;
?>
<div class="wrap seslp-guide-wrap">
  <h1><?php echo esc_html( $page_title ); ?></h1>

  <?php if (!empty($guide_file)) : ?>
  <p class="description" style="margin-top:4px;">
    <?php
      /* translators: %s is a file path */
      printf( esc_html__('Loaded from: %s', SESLP_Plugin::TD), esc_html($guide_file) );
      ?>
  </p>
  <?php endif; ?>

  <div class="seslp-guide-content">
    <?php echo $guide_html; ?>
  </div>
</div>