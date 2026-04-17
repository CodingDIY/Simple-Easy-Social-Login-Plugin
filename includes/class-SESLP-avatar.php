<?php
/**
 * Avatar override handler.
 *
 * Responsible for:
 * - preferring remote avatar URLs stored in user meta,
 * - avoiding local media storage for social avatars,
 * - providing safe fallbacks when remote images are missing or broken,
 * - ensuring compatibility with themes using either URL or HTML avatar filters.
 */

declare(strict_types=1);
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Customize WordPress avatar output using SESLP user metadata.
 *
 * Hooks into both URL-level and HTML-level avatar filters to ensure
 * consistent rendering across themes and plugins.
 */
final class SESLP_Avatar {
  /**
   * Register avatar override filters.
   *
   * @return void
   */
  public static function init(): void {
    // URL-level override used by many themes and core get_avatar_url()
    add_filter('get_avatar_url', [self::class, 'filter_get_avatar_url'], 10, 3);
    // HTML-level short-circuit (for themes/plugins that ignore get_avatar_url)
    add_filter('pre_get_avatar', [self::class, 'filter_pre_get_avatar'], 10, 3);
  }

  /**
   * Filter avatar URL to use SESLP meta when available.
   *
   * Falls back to attachment image or default WordPress avatar when needed.
   *
   * @param string $url
   * @param mixed  $id_or_email
   * @param array  $args
   * @return string
   */
  public static function filter_get_avatar_url($url, $id_or_email, $args) {
    $user = self::resolve_user($id_or_email);
    if ($user && $user->ID) {
      $meta = self::get_avatar_meta($user->ID);

      if (!empty($meta['url'])) {
        $src = esc_url($meta['url']);
        if ($src !== '') {
          return $src;
        }

        $fallback = self::get_fallback_avatar_url(isset($args['size']) ? (int) $args['size'] : 96);
        if ($fallback !== '') {
          return $fallback;
        }
      }

      if ($meta['id']) {
        $img = wp_get_attachment_image_url($meta['id'], 'thumbnail');
        if ($img) {
          return $img;
        }
      }
    }

    // If WordPress produced an empty URL, try the plugin fallback.
    if (!is_string($url) || $url === '') {
      $fallback = self::get_fallback_avatar_url(isset($args['size']) ? (int) $args['size'] : 96);
      if ($fallback !== '') {
        return $fallback;
      }
    }

    return $url;
  }

  /**
   * Provide full <img> HTML for avatar rendering when SESLP meta exists.
   *
   * Adds an onerror fallback when a remote avatar URL is used.
   *
   * @param string $avatar
   * @param mixed  $id_or_email
   * @param array  $args
   * @return string
   */
  public static function filter_pre_get_avatar($avatar, $id_or_email, $args) {
    $user = self::resolve_user($id_or_email);
    if ($user && $user->ID) {
      $meta       = self::get_avatar_meta($user->ID);
      $size       = isset($args['size']) ? max(1, (int) $args['size']) : 96;
      $alt        = isset($args['alt']) ? $args['alt'] : $user->display_name;
      $class_attr = self::prepare_class_attr(isset($args['class']) ? $args['class'] : null);

      $fallback_url = self::get_fallback_avatar_url($size);

      if (!empty($meta['url'])) {
        $src = esc_url($meta['url']);

        // Add onerror fallback only when we have a real fallback URL.
        if ($fallback_url !== '') {
          $fallback = esc_url($fallback_url);
          return sprintf(
            '<img alt="%s" src="%s" class="%s" height="%d" width="%d" loading="lazy" onerror="this.onerror=null;this.src=\'%s\';" />',
            esc_attr($alt),
            $src,
            esc_attr($class_attr),
            $size,
            $size,
            $fallback
          );
        }

        return sprintf(
          '<img alt="%s" src="%s" class="%s" height="%d" width="%d" loading="lazy" />',
          esc_attr($alt),
          $src,
          esc_attr($class_attr),
          $size,
          $size
        );
      }

      if ($meta['id']) {
        $img = wp_get_attachment_image(
          $meta['id'],
          [$size, $size],
          false,
          [
            'class'   => $class_attr,
            'loading' => 'lazy',
          ]
        );
        if ($img) {
          return $img;
        }
      }
    }

    return $avatar; // fall back to default behavior
  }

  /**
   * Return a safe fallback avatar URL.
   *
   * Uses WordPress core default avatar (Gravatar mystery person).
   *
   * @param int $size
   * @return string
   */
  private static function get_fallback_avatar_url(int $size = 96): string {
    $size = max(1, (int) $size);

    // Use core default avatar.
    return (string) get_avatar_url(0, ['size' => $size]);
  }

  /**
   * Resolve a WP_User object from various input types.
   *
   * Supports WP_User, WP_Post, WP_Comment, user ID, email, or login.
   *
   * @param mixed $id_or_email
   * @return WP_User|null
   */
  private static function resolve_user($id_or_email): ?WP_User {
    if ($id_or_email instanceof WP_User) {
      return $id_or_email;
    }
    if ($id_or_email instanceof WP_Post) {
      return get_user_by('id', (int) $id_or_email->post_author) ?: null;
    }
    if ($id_or_email instanceof WP_Comment) {
      return get_user_by('id', (int) $id_or_email->user_id) ?: null;
    }

    if (is_numeric($id_or_email)) {
      return get_user_by('id', (int) $id_or_email) ?: null;
    }
    if (is_string($id_or_email)) {
      return get_user_by('email', $id_or_email) ?: (get_user_by('login', $id_or_email) ?: null);
    }

    return null;
  }

  /**
   * Retrieve stored avatar metadata for a user.
   *
   * @param int $user_id
   * @return array{url:string,id:int}
   */
  private static function get_avatar_meta(int $user_id): array {
    return [
      'url' => (string) get_user_meta($user_id, 'seslp_avatar_url', true),
      'id'  => (int) get_user_meta($user_id, 'seslp_avatar_id', true),
    ];
  }

  /**
   * Normalize avatar CSS class attribute.
   *
   * Accepts string or array input and returns a sanitized class string.
   *
   * @param mixed $class
   * @return string
   */
  private static function prepare_class_attr($class): string {
    if (is_array($class)) {
      $clean = array_map('sanitize_html_class', $class);
      $clean = array_filter($clean, 'strlen');
      if (!empty($clean)) {
        return implode(' ', $clean);
      }
    } elseif (is_string($class) && $class !== '') {
      $parts = preg_split('/\s+/', $class, -1, PREG_SPLIT_NO_EMPTY) ?: [];
      if ($parts) {
        $clean = array_map('sanitize_html_class', $parts);
        $clean = array_filter($clean, 'strlen');
        if (!empty($clean)) {
          return implode(' ', $clean);
        }
      }
    }

    return 'avatar seslp-avatar';
  }
}

SESLP_Avatar::init();