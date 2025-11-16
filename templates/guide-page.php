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
  <div class="seslp-guide-content">
    <?php echo $guide_html; ?>
  </div>
</div>