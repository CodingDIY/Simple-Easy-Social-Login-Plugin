<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
  exit;
}

/**
 * Admin "Guide" subpage controller.
 * - Renders localized Markdown via a template file: templates/guide-page.php
 * - Primary docs: /assets/md/{locale}.md (hyphen or underscore)
 * - Optional Parsedown: assets/md/Parsedown.php
 */
class SESLP_Guides {
  private const GUIDE_DIR_PRIMARY = 'assets/md';
  private const FALLBACK_LOCALE   = 'en-US';

  /** Register submenu under plugin top-level if available; otherwise under Settings. */
  public static function register_menu(): void {
    // Attach Guide under the main plugin menu
    $parent_slug = defined('SESLP_SETTINGS_SLUG') ? SESLP_SETTINGS_SLUG : 'seslp-settings';

    add_submenu_page(
      $parent_slug,
      __('Guide', 'simple-easy-social-login-oauth-login'),
      __('Guide', 'simple-easy-social-login-oauth-login'),
      'manage_options',
      'seslp-guide',
      [self::class, 'render_guide_page']
    );
  }

  /** Entry point for the page. Loads template and passes data. */
  public static function render_guide_page(): void {
    if (!current_user_can('manage_options')) {
      wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'simple-easy-social-login-oauth-login'));
    }

    // Reuse admin CSS with consistent handle & cache-busting (same as Settings).
    $plugin   = SESLP_Plugin::instance();
    $css_rel  = 'assets/css/admin-settings.css';
    $css_path = $plugin->dir . $css_rel;
    $css_ver  = file_exists($css_path) ? (string) filemtime($css_path) : SESLP_Plugin::VERSION;
    wp_enqueue_style('seslp-admin', $plugin->url . $css_rel, [], $css_ver);

    // Enqueue admin JS (needed for <details> accordion)
    $js_rel  = 'assets/js/admin-settings.js';
    $js_path = $plugin->dir . $js_rel;
    $js_ver  = file_exists($js_path) ? (string) filemtime($js_path) : SESLP_Plugin::VERSION;
    wp_enqueue_script('seslp-admin-js', $plugin->url . $js_rel, [], $js_ver, true);

    $plugin_root = $plugin->dir;
    $locale_full = get_user_locale() ?: get_locale(); // e.g., ko_KR
    $locale_norm = str_replace('_', '-', $locale_full); // ko-KR
    $lang_only   = strstr($locale_norm, '-', true) ?: $locale_norm; // ko

    // Build candidate file list (hyphen/underscore + primary/legacy dirs).
    $md_file = self::locate_markdown($plugin_root, $locale_norm, $lang_only);

    // Load md (or not-found message), convert to HTML.
    $markdown = $md_file ? (string) @file_get_contents($md_file) : self::not_found_message();
    $html     = self::markdown_to_html($markdown, $plugin_root);

    // Force guide links to open in a new tab with safe rel attribute.
    $html = preg_replace_callback(
      '/<a\s+([^>]*href="[^"]+"[^>]*)>/i',
      static function (array $m): string {
        $attrs = $m[1];

        if (stripos($attrs, 'target=') === false) {
          $attrs .= ' target="_blank"';
        }
        if (stripos($attrs, 'rel=') === false) {
          $attrs .= ' rel="noopener"';
        }

        return '<a ' . trim($attrs) . '>'; 
      },
      $html
    );

    // Sanitize
    $allowed  = self::kses_allowed_tags();
    $html     = wp_kses($html, $allowed);

    // Load template
    $template = $plugin_root . 'templates/guide-page.php';
    if (!file_exists($template)) {
      // Fallback: simple inline output if template is missing.
      echo '<div class="wrap seslp-guide-wrap"><h1>'
         . esc_html__('SESLP Guide', 'simple-easy-social-login-oauth-login')
         . '</h1><div class="seslp-guide-content">'
         . wp_kses($html, $allowed)
         . '</div></div>';
      return;
    }

    // Provide data to template in scoped variables.
    $page_title = __('SESLP Guide', 'simple-easy-social-login-oauth-login');
    $guide_html = $html;
    $guide_file = $md_file;

    // Isolate scope
    include $template;
  }

  /** Find the best matching Markdown file by locale. */
  private static function locate_markdown(string $root, string $locale_norm, string $lang_only): ?string {
    $candidates = [];
    foreach ([$locale_norm, str_replace('-', '_', $locale_norm), $lang_only, self::FALLBACK_LOCALE] as $loc) {
      $candidates[] = $root . self::GUIDE_DIR_PRIMARY . '/' . $loc . '.md';
    }
    foreach ($candidates as $path) {
      if (file_exists($path)) return $path;
    }
    return null;
  }

  /** Small info message as Markdown. */
  private static function not_found_message(): string {
    return "### " . esc_html__('Guide not found', 'simple-easy-social-login-oauth-login') . "\n\n"
         . esc_html__('Please add a localized Markdown file under assets/md/{locale}.md', 'simple-easy-social-login-oauth-login');
  }

  /** Markdown → HTML using Parsedown located under assets/md/Parsedown.php (no fallback). */
  private static function markdown_to_html(string $md, string $plugin_root): string {
    $parsedown_path = $plugin_root . 'assets/md/Parsedown.php';

    if (file_exists($parsedown_path)) {
      require_once $parsedown_path;

      if (class_exists('SESLP_Parsedown')) {
        $seslp_pd = new SESLP_Parsedown();
        $seslp_pd->setBreaksEnabled(true);

        return $seslp_pd->text($md);
      }
    }

    return wpautop(esc_html($md));
  }

  /** Allowed tags for admin output. */
  private static function kses_allowed_tags(): array {
    return [
      'h1'=>[], 'h2'=>[], 'h3'=>[], 'h4'=>[], 'h5'=>[], 'h6'=>[],
      'p'=>['class'=>[]], 'br'=>[], 'hr'=>[],
      'ul'=>['class'=>[]], 'ol'=>['class'=>[]], 'li'=>[],
      'strong'=>[], 'em'=>[], 'b'=>[], 'i'=>[], 'u'=>[],
      'a'=>['href'=>[], 'target'=>[], 'rel'=>[], 'class'=>[]],
      'code'=>['class'=>[]], 'pre'=>['class'=>[]],
      'blockquote'=>['cite'=>[]], 'span'=>['class'=>[]],
      'table'=>['class'=>[]], 'thead'=>[], 'tbody'=>[], 'tr'=>[], 'th'=>[], 'td'=>['colspan'=>[], 'rowspan'=>[], 'class'=>[]],
      'details'=>['open'=>[]], 'summary'=>['class'=>[]],
    ];
  }
}