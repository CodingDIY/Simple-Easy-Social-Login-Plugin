<?php
/**
 * SESLP Logger
 * - Writes debug info to wp-content/SESLP-debug.log (when enabled)
 */

declare(strict_types=1);
if (!defined('ABSPATH')) exit;

final class SESLP_Logger {
  private const FILE = 'SESLP-debug.log';
  private const OPTION_FALLBACK = 'seslp_options';

  /** Cache plugin options per-request */
  private static array $options_cache = [];

  /**
   * Write a log entry (no-op when disabled)
   *
   * @param string $level   error|warning|info|debug (not enforced)
   * @param string $message human-friendly message
   * @param array  $context optional structured context (json-encoded)
   */
  public static function log(string $level, string $message, array $context = []): void {
    $opts  = self::options();
    $debug = self::debug_settings($opts);

    // Honor enable switch (default: off)
    if (empty($debug['enabled'])) return;
    
    // Timezone mode (default UTC)
    $tz_mode   = self::normalize_timezone_mode($debug['timezone'] ?? 'UTC');
    $now_utc   = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    $now_local = new DateTimeImmutable('now', wp_timezone());
    $timestamp = self::format_timestamp($tz_mode, $now_utc, $now_local);

    // Sanitize/Mask sensitive values in context before writing
    $context = self::safe_context($context);

    // Build one-line entry
    $line = sprintf(
      "[%s] [%s] %s %s\n",
      $timestamp,
      strtoupper($level),
      $message,
      $context ? wp_json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : ''
    );

    $path = WP_CONTENT_DIR . '/' . self::FILE;
    $fs   = self::filesystem();

    if (!$fs) {
      return;
    }

    $existing = '';
    if ($fs->exists($path)) {
      $existing = $fs->get_contents($path);
      if (!is_string($existing)) {
        $existing = '';
      }
    }

    $fs->put_contents($path, $existing . $line, FS_CHMOD_FILE);
  }

  /**
   * Initialize and return the WordPress filesystem instance.
   */
  private static function filesystem() {
    global $wp_filesystem;

    if ($wp_filesystem instanceof WP_Filesystem_Base) {
      return $wp_filesystem;
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';

    if (!WP_Filesystem()) {
      return null;
    }

    return $wp_filesystem instanceof WP_Filesystem_Base ? $wp_filesystem : null;
  }

  public static function debug(string $msg, array $ctx = []): void {
    self::log('debug', $msg, $ctx);
  }
  public static function info(string $msg, array $ctx = []): void {
    self::log('info', $msg, $ctx);
  }
  public static function warning(string $msg, array $ctx = []): void {
    self::log('warning', $msg, $ctx);
  }
  public static function error(string $msg, array $ctx = []): void {
    self::log('error', $msg, $ctx);
  }

  /**
   * Recursively mask sensitive values in context arrays.
   */
  private static function safe_context($context) {
    if (!is_array($context)) return $context;
    $out = [];
    foreach ($context as $k => $v) {
      $lk = is_string($k) ? strtolower($k) : $k;
      if (is_array($v)) {
        $out[$k] = self::safe_context($v);
        continue;
      }
      if (!is_string($v)) { $out[$k] = $v; continue; }

      switch (true) {
        case (strpos($lk, 'email') !== false || $lk === 'mail'):
          $out[$k] = self::mask_email($v);
          break;
        case (strpos($lk, 'client_id') !== false || strpos($lk, 'user_id') !== false || $lk === 'id'):
          $out[$k] = self::mask_id($v);
          break;
        case (strpos($lk, 'client_secret') !== false || strpos($lk, 'secret') !== false):
          $out[$k] = self::mask_secret($v);
          break;
        case (strpos($lk, 'access_token') !== false || strpos($lk, 'refresh_token') !== false || $lk === 'token'):
          $out[$k] = self::mask_token($v);
          break;
        case ($lk === 'state' || $lk === 'code'):
          $out[$k] = self::mask_generic($v, 3, 2);
          break;
        default:
          $out[$k] = $v;
      }
    }
    return $out;
  }

  /** Generic masker: keep N left/right chars, mask the middle */
  public static function mask_generic(string $s, int $keepLeft = 3, int $keepRight = 2, string $maskChar = '*'): string {
    $len = strlen($s);
    if ($len <= ($keepLeft + $keepRight)) {
      return str_repeat($maskChar, max(0, $len));
    }
    $left  = substr($s, 0, $keepLeft);
    $right = substr($s, -$keepRight);
    return $left . str_repeat($maskChar, max(0, $len - $keepLeft - $keepRight)) . $right;
  }

  /** Email masker (simple and safe):
   * - Local part: keep first char, mask rest
   * - Domain: keep first label's first char, mask rest, keep other labels as-is
   */
  public static function mask_email(string $email): string {
    if (!preg_match('/^([^@]+)@(.+)$/', $email, $m)) {
      return self::mask_generic($email, 2, 2);
    }
    $local  = $m[1];
    $domain = $m[2];

    // Mask local part: keep first char only
    $localMasked = substr($local, 0, 1) . str_repeat('*', max(0, strlen($local) - 1));

    // Split domain into labels
    $labels = explode('.', $domain);
    $first  = array_shift($labels);
    $firstMasked = substr($first, 0, 1) . str_repeat('*', max(0, strlen($first) - 1));

    $domainMasked = $firstMasked;
    if (!empty($labels)) {
      $domainMasked .= '.' . implode('.', $labels);
    }

    return $localMasked . '@' . $domainMasked;
  }

  /** ID masker */
  public static function mask_id(string $id): string {
    return self::mask_generic($id, 3, 2);
  }

  /** Secret masker */
  public static function mask_secret(string $s): string {
    return self::mask_generic($s, 2, 2);
  }

  /** Token masker */
  public static function mask_token(string $s): string {
    return self::mask_generic($s, 3, 2);
  }

  /**
   * Load plugin options once and normalize the result.
   */
  private static function options(): array {
    if (self::$options_cache) {
      return self::$options_cache;
    }

    $key = defined('SESLP_OPT_KEY') ? SESLP_OPT_KEY : self::OPTION_FALLBACK;
    $raw = get_option($key, []);

    self::$options_cache = is_array($raw) ? $raw : [];

    return self::$options_cache;
  }

  /**
   * Extract debug settings as a normalized array.
   */
  private static function debug_settings(array $opts): array {
    $debug = $opts['debug'] ?? [];

    return is_array($debug) ? $debug : [];
  }

  /**
   * Normalize timezone mode to supported values.
   */
  private static function normalize_timezone_mode(string $mode): string {
    $mode = sanitize_key($mode);

    if (in_array($mode, ['local', 'both'], true)) {
      return $mode;
    }

    return 'utc';
  }

  /**
   * Build the timestamp string according to the configured mode.
   */
  private static function format_timestamp(string $mode, DateTimeImmutable $nowUtc, DateTimeImmutable $nowLocal): string {
    if ($mode === 'local') {
      return $nowLocal->format('Y-m-d H:i:s T');
    }

    if ($mode === 'both') {
      return sprintf(
        '%s UTC | %s %s',
        $nowUtc->format('Y-m-d H:i:s'),
        $nowLocal->format('Y-m-d H:i:s'),
        $nowLocal->format('T')
      );
    }

    return $nowUtc->format('Y-m-d H:i:s') . ' UTC';
  }
}