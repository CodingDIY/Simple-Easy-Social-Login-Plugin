<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
  exit;
}

/**
 * Admin "Guide" subpage controller.
 * - Renders localized HTML guide files via a template file: templates/guide-page.php
 * - Primary docs: /guides/{locale}.html
 */
class SESLP_Guides {
  private const GUIDE_DIR_PRIMARY = 'guides';
  private const FALLBACK_LOCALE   = 'en_US';

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
    $lang_only   = strstr($locale_full, '_', true) ?: $locale_full; // ko

    // Build candidate file list using locale-specific HTML guides.
    $guide_file = self::locate_guide_file($plugin_root, $locale_full, $lang_only);

    // Load HTML guide file (or fallback not-found message).
    $html = $guide_file ? (string) @file_get_contents($guide_file) : self::not_found_message_html();

    $html = self::replace_guide_placeholders($html);
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

    // Isolate scope
    include $template;
  }

  /** Replace guide placeholders with the current site URL and host. */
  private static function replace_guide_placeholders(string $html): string {
    $site_url  = home_url('/');
    $site_url  = untrailingslashit($site_url);
    $site_host = wp_parse_url($site_url, PHP_URL_HOST);
    $site_host = is_string($site_host) ? $site_host : '';

    $replacements = [
      '{your-domain}'       => $site_url,
      '{domain}'            => $site_host ?: 'example.com',
      'https://example.com' => $site_url,
      'http://example.com'  => $site_url,
      'example.com'         => $site_host ?: 'example.com',
    ];

    return strtr($html, $replacements);
  }

  /** Find the best matching HTML guide file by locale. */
  private static function locate_guide_file(string $root, string $locale_full, string $lang_only): ?string {
    $candidates = [];

    foreach ([$locale_full, $lang_only, self::FALLBACK_LOCALE] as $loc) {
      $candidates[] = $root . self::GUIDE_DIR_PRIMARY . '/' . $loc . '.html';
    }

    foreach ($candidates as $path) {
      if (file_exists($path)) {
        return $path;
      }
    }

    return null;
  }

  /** Small info message as HTML. */
  private static function not_found_message_html(): string {
    return '<h3>'
         . esc_html__('Guide not found', 'simple-easy-social-login-oauth-login')
         . '</h3><p>'
         . esc_html__('Please add a localized HTML guide file under guides/{locale}.html', 'simple-easy-social-login-oauth-login')
         . '</p>';
  }

  /** Allowed tags for admin output. */
  private static function kses_allowed_tags(): array {
        return [
      'div' => ['class' => []],
      'h1' => ['class' => []],
      'h2' => ['class' => []],
      'h3' => ['class' => []],
      'h4' => ['class' => []],
      'h5' => ['class' => []],
      'h6' => ['class' => []],
      'p'  => ['class' => []],
      'br' => [],
      'hr' => [],
      'ul' => ['class' => []],
      'ol' => ['class' => []],
      'li' => ['class' => []],
      'strong' => [],
      'em' => [],
      'b' => [],
      'i' => [],
      'u' => [],
      'a' => ['href' => [], 'target' => [], 'rel' => [], 'class' => []],
      'code' => ['class' => []],
      'pre' => ['class' => []],
      'blockquote' => ['cite' => [], 'class' => []],
      'span' => ['class' => []],
      'table' => ['class' => []],
      'thead' => [],
      'tbody' => [],
      'tr' => [],
      'th' => ['class' => [], 'colspan' => [], 'rowspan' => []],
      'td' => ['class' => [], 'colspan' => [], 'rowspan' => []],
      'details' => ['open' => [], 'class' => []],
      'summary' => ['class' => []],
      'input' => ['type' => [], 'disabled' => [], 'checked' => [], 'class' => []],
    ];
  }
}