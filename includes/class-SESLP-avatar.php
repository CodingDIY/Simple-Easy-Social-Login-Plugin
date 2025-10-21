<?php
/**
 * Avatar overrides
 * - Prefer SESLP user meta (remote URL) for avatar without storing media files
 */

declare(strict_types=1);
if (!defined('ABSPATH')) exit;

final class SESLP_Avatar {
  public static function init(): void {
    // URL-level override used by many themes and core get_avatar_url()
    add_filter('get_avatar_url', [self::class, 'filter_get_avatar_url'], 10, 3);
    // HTML-level short-circuit (for themes/plugins that ignore get_avatar_url)
    add_filter('pre_get_avatar', [self::class, 'filter_pre_get_avatar'], 10, 3);
  }

  /**
   * Return custom avatar URL from user meta when available.
   */
  public static function filter_get_avatar_url($url, $id_or_email, $args) {
    $user = self::resolve_user($id_or_email);
    if ($user && $user->ID) {
      $meta_url = get_user_meta($user->ID, 'seslp_avatar_url', true);
      if (!empty($meta_url)) {
        return esc_url($meta_url);
      }
      // Fallback to attachment ID if previously stored
      $att_id = (int) get_user_meta($user->ID, 'seslp_avatar_id', true);
      if ($att_id) {
        $img = wp_get_attachment_image_url($att_id, 'thumbnail');
        if ($img) return $img;
      }
    }
    return $url;
  }

  /**
   * Provide full <img> HTML when meta avatar exists.
   */
  public static function filter_pre_get_avatar($avatar, $id_or_email, $args) {
    $user = self::resolve_user($id_or_email);
    if ($user && $user->ID) {
      $meta_url = get_user_meta($user->ID, 'seslp_avatar_url', true);
      if (!empty($meta_url)) {
        $alt   = isset($args['alt']) ? esc_attr($args['alt']) : esc_attr($user->display_name);
        $class = isset($args['class']) ? esc_attr($args['class']) : 'avatar seslp-avatar';
        $size  = isset($args['size']) ? (int) $args['size'] : 96;
        $src   = esc_url($meta_url);
        return sprintf(
          '<img alt="%s" src="%s" class="%s" height="%d" width="%d" loading="lazy" />',
          $alt, $src, $class, $size, $size
        );
      }
      // Fallback to attachment ID if available
      $att_id = (int) get_user_meta($user->ID, 'seslp_avatar_id', true);
      if ($att_id) {
        $img = wp_get_attachment_image(
          $att_id,
          [$size, $size],
          false,
          [
            'class'   => isset($args['class']) ? $args['class'] : 'avatar seslp-avatar',
            'loading' => 'lazy',
          ]
        );
        if ($img) return $img;
      }
    }
    return $avatar; // fall back to default behavior
  }

  private static function resolve_user($id_or_email): ?WP_User {
    if ($id_or_email instanceof WP_User) return $id_or_email;
    if ($id_or_email instanceof WP_Post) return get_user_by('id', (int) $id_or_email->post_author);
    if ($id_or_email instanceof WP_Comment) return get_user_by('id', (int) $id_or_email->user_id);

    if (is_numeric($id_or_email)) return get_user_by('id', (int) $id_or_email);
    if (is_string($id_or_email)) {
      return get_user_by('email', $id_or_email) ?: get_user_by('login', $id_or_email);
    }

    return null;
  }
}

SESLP_Avatar::init();