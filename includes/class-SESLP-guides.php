<?php
// includes/class-SESLP-guides.php
if (!defined('ABSPATH')) exit;

/**
 * Admin "Guide" subpage controller.
 * - Renders localized Markdown via a template file: templates/guide-page.php
 * - Primary docs: /assets/md/{locale}.md (hyphen or underscore)
 * - Legacy docs supported: /assets/guide/{locale}.md
 * - Optional Parsedown: includes/vendor/Parsedown.php or assets/vendor/Parsedown.php
 */
class SESLP_Guides {
  private const GUIDE_DIR_PRIMARY = 'assets/md';
  private const GUIDE_DIR_LEGACY  = 'assets/guide';
  private const FALLBACK_LOCALE   = 'en-US';

  /** Register submenu under plugin top-level if available; otherwise under Settings. */
  public static function register_menu(): void {
    // Attach Guide under the main plugin menu
    $parent_slug = 'seslp-settings';
    if (class_exists('SESLP_Plugin') && defined('SESLP_SLUG')) {
      // SESLP_Plugin::SLUG delegates to SESLP_SLUG from constants.php
      $parent_slug = SESLP_Plugin::SLUG ?: $parent_slug;
    }

    add_submenu_page(
      $parent_slug,
      __('Guide', SESLP_Plugin::TD),
      __('Guide', SESLP_Plugin::TD),
      'manage_options',
      'seslp-guide',
      [self::class, 'render_guide_page']
    );
  }

  /** Entry point for the page. Loads template and passes data. */
  public static function render_guide_page(): void {
    if (!current_user_can('manage_options')) {
      wp_die(esc_html__('You do not have sufficient permissions to access this page.', SESLP_Plugin::TD));
    }

    // Reuse existing admin CSS for consistent admin look & feel.
    $plugin_file = SESLP_Plugin::instance()->file;
    wp_enqueue_style(
      'seslp-admin-settings',
      plugins_url('assets/css/admin-settings.css', $plugin_file),
      [],
      SESLP_Plugin::VERSION
    );

    $plugin_root = plugin_dir_path($plugin_file);
    $locale_full = get_user_locale() ?: get_locale(); // e.g., ko_KR
    $locale_norm = str_replace('_', '-', $locale_full); // ko-KR
    $lang_only   = strstr($locale_norm, '-', true) ?: $locale_norm; // ko

    // Build candidate file list (hyphen/underscore + primary/legacy dirs).
    $md_file = self::locate_markdown($plugin_root, $locale_norm, $lang_only);

    // Load md (or not-found message), convert to HTML.
    $markdown = $md_file ? (string) @file_get_contents($md_file) : self::not_found_message();
    $html     = self::markdown_to_html($markdown, $plugin_root);

    // Sanitize
    $allowed  = self::kses_allowed_tags();
    $html     = wp_kses($html, $allowed);

    // Load template
    $template = $plugin_root . 'templates/guide-page.php';
    if (!file_exists($template)) {
      // Fallback: simple inline output if template is missing.
      echo '<div class="wrap seslp-guide-wrap"><h1>'
         . esc_html__('SESLP Guide', SESLP_Plugin::TD)
         . '</h1><div class="seslp-guide-content">'
         . $html
         . '</div></div>';
      return;
    }

    // Provide data to template in scoped variables.
    $page_title = __('SESLP Guide', SESLP_Plugin::TD);
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
      $candidates[] = $root . self::GUIDE_DIR_LEGACY  . '/' . $loc . '.md';
    }
    foreach ($candidates as $path) {
      if (file_exists($path)) return $path;
    }
    return null;
  }

  /** Small info message as Markdown. */
  private static function not_found_message(): string {
    return "### " . esc_html__('Guide not found', SESLP_Plugin::TD) . "\n\n"
         . esc_html__('Please add a localized Markdown file under assets/md/{locale}.md', SESLP_Plugin::TD);
  }

  /** Markdown → HTML with optional Parsedown. */
  private static function markdown_to_html(string $md, string $root): string {
    foreach ([
      $root . 'includes/vendor/Parsedown.php',
      $root . 'assets/vendor/Parsedown.php',
    ] as $pd) {
      if (file_exists($pd)) {
        require_once $pd;
        if (class_exists('Parsedown')) {
          $p = new \Parsedown();
          // $p->setSafeMode(true);
          return $p->text($md);
        }
      }
    }
    return self::fallback_markdown($md);
  }

  /** Minimal fallback Markdown parser (basic features). */
  private static function fallback_markdown(string $md): string {
    $md = preg_replace_callback('/```([\s\S]*?)```/m', function($m){
      return "\n<pre><code>" . htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8') . "</code></pre>\n";
    }, $md);

    for ($i = 6; $i >= 1; $i--) {
      $pattern = '/^' . str_repeat('#', $i) . '\s*(.+)$/m';
      $md = preg_replace($pattern, '<h'.$i.'>$1</h'.$i.'>', $md);
    }

    $md = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $md);
    $md = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $md);
    $md = preg_replace('/`([^`]+)`/', '<code>$1</code>', $md);
    $md = preg_replace('/$begin:math:display$(.*?)$end:math:display$$begin:math:text$(https?:\\/\\/[^\\s)]+)$end:math:text$/', '<a href="$2" target="_blank" rel="noopener">$1</a>', $md);

    $md = preg_replace_callback('/(?:^\s*[-*]\s+.+\n?)+/m', function($m){
      $items = preg_replace('/^\s*[-*]\s+(.+)$/m', '<li>$1</li>', trim($m[0]));
      return "<ul>\n$items\n</ul>\n";
    }, $md);

    $md = preg_replace_callback('/(?:^\s*\d+\.\s+.+\n?)+/m', function($m){
      $items = preg_replace('/^\s*\d+\.\s+(.+)$/m', '<li>$1</li>', trim($m[0]));
      return "<ol>\n$items\n</ol>\n";
    }, $md);

    $parts = preg_split('/\n{2,}/', $md);
    $parts = array_map(function($p){
      if (preg_match('/^\s*<\/?(h\d|ul|ol|li|pre|table|thead|tbody|tr|th|td|blockquote)/i', $p)) return $p;
      return '<p>'.trim($p).'</p>';
    }, $parts);

    return implode("\n", $parts);
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