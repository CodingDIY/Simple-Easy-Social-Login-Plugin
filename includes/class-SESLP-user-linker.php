<?php
/**
 * User Linker
 * - Centralizes linking/creation of WP users from normalized provider profile
 * - Expected profile keys: id, email, name, picture
 */
declare(strict_types=1);

if (!defined('ABSPATH')) exit;

final class SESLP_User_Linker {
  /**
   * Link existing user by email or create a new one, then sign in.
   *
   * @param array<string,string> $profile {id,email,name,picture}
   * @param string $provider e.g. 'google', 'naver'
   * @return WP_User|null Signed-in user or null on failure
   */
  public function link_or_create_and_sign_in(array $profile, string $provider): ?WP_User {
    $email   = sanitize_email((string)($profile['email'] ?? ''));
    $pid     = sanitize_text_field((string)($profile['id'] ?? ''));
    $name    = sanitize_text_field((string)($profile['name'] ?? ''));
    $picture = esc_url_raw((string)($profile['picture'] ?? ''));
    $prov    = sanitize_key($provider);

    if ($email === '' || $pid === '') {
      SESLP_Logger::warning('Linker: missing email or provider id', [
        'email_present' => $email !== '' ? 1 : 0,
        'pid_present'   => $pid !== '' ? 1 : 0,
        'provider'      => $prov,
      ]);
      return null;
    }

    // Prepare flags and lookup existing user by email
    $created = false;
    $user    = get_user_by('email', $email);

    // Unified email-exists handling (legacy meta compatible)
    if ($user && $user->ID) {
      // --- Legacy meta compatibility (migrate once if old keys exist) ---
      $prov_meta = get_user_meta($user->ID, 'seslp_provider', true);
      if ($prov_meta === '' || $prov_meta === false) {
        $legacy_prov = get_user_meta($user->ID, 'social_provider', true);
        if (!empty($legacy_prov)) {
          $prov_meta = $legacy_prov;
          update_user_meta($user->ID, 'seslp_provider', $legacy_prov);
        }
      }

      $prov_id_meta = get_user_meta($user->ID, 'seslp_provider_id', true);
      if ($prov_id_meta === '' || $prov_id_meta === false) {
        $legacy_id = get_user_meta($user->ID, 'social_id', true);
        if (!empty($legacy_id)) {
          $prov_id_meta = $legacy_id;
          update_user_meta($user->ID, 'seslp_provider_id', $legacy_id);
        }
      }
      // -----------------------------------------------------------------

      // Fallback detection: if canonical provider meta is empty, try provider-specific clues
      if (empty($prov_meta)) {
        $by_specific = get_user_meta($user->ID, 'seslp_' . $prov . '_id', true);
        $by_last     = get_user_meta($user->ID, 'seslp_last_provider', true);

        // If provider-specific id exists and matches (or pid is unavailable), consider it linked
        if (!empty($by_specific) && ($pid === '' || $by_specific === $pid)) {
          $prov_meta = $prov; // treat as linked with current provider
          // backfill canonical keys for future logins
          update_user_meta($user->ID, 'seslp_provider', $prov);
          if (!get_user_meta($user->ID, 'seslp_provider_id', true) && $pid !== '') {
            update_user_meta($user->ID, 'seslp_provider_id', $pid);
          }
          SESLP_Logger::debug('Linker: inferred link via provider-specific meta', [
            'user_id' => (int) $user->ID,
            'prov'    => $prov,
          ]);
        } elseif (!empty($by_last) && $by_last === $prov) {
          // As a weaker signal, last successful provider also implies linkage
          $prov_meta = $prov;
          update_user_meta($user->ID, 'seslp_provider', $prov);
          if (!get_user_meta($user->ID, 'seslp_provider_id', true) && $pid !== '') {
            update_user_meta($user->ID, 'seslp_provider_id', $pid);
          }
          SESLP_Logger::debug('Linker: inferred link via last provider', [
            'user_id' => (int) $user->ID,
            'prov'    => $prov,
          ]);
        }
      }
      
      // Unified policy: if no provider is linked OR linked with a different provider → show one unified error
      if (empty($prov_meta) || $prov_meta !== $prov) {
        SESLP_Logger::info('Same email already registered with a different method', [
          'user_id'  => (int) $user->ID,
          'email'    => SESLP_Logger::mask_email($email),
          'existing' => (string) $prov_meta,   // may be empty
          'attempt'  => (string) $prov,
        ]);
        $login_url = add_query_arg([
          'seslp_err' => 'email_exists',
        ], wp_login_url());
        wp_safe_redirect($login_url);
        exit;
      }

      // Same provider as already linked → allow sign-in (ensure canonical keys exist)
      if (!get_user_meta($user->ID, 'seslp_provider', true)) {
        update_user_meta($user->ID, 'seslp_provider', $prov);
      }
      if (!get_user_meta($user->ID, 'seslp_provider_id', true)) {
        update_user_meta($user->ID, 'seslp_provider_id', $pid);
      }
      SESLP_Logger::debug('Linker: signing in user', [
        'user_id'  => (int) $user->ID,
        'provider' => (string) $prov,
        'created'  => 0,
      ]);
      wp_set_auth_cookie($user->ID);
      return $user;
    }

    if (!$user) {
      // Build username as `{localpart}_{provider}` and enforce uniqueness
      $local    = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', (string) current(explode('@', $email)));
      if ($local === '') { $local = 'user'; }
      $base     = strtolower($local . '_' . $prov);
      $username = sanitize_user($base, true);
      if ($username === '') { $username = 'user_' . $prov; }

      // Ensure uniqueness by appending incremental suffix if needed
      $try = $username;
      $i = 1;
      while (username_exists($try)) {
        $try = $username . '_' . $i;
        $i++;
      }
      $username = $try;

      $uid = wp_insert_user([
        'user_login'   => $username,
        'user_email'   => $email,
        'display_name' => $name !== '' ? $name : $username,
        'user_pass'    => wp_generate_password(24),
      ]);
      if (is_wp_error($uid)) {
        SESLP_Logger::error('Linker: user create failed', [
          'provider' => $prov,
          'email'    => $email,
          'error'    => $uid->get_error_message(),
        ]);
        return null;
      }
      $created = true;
      $user = get_user_by('id', (int)$uid);
      SESLP_Logger::info('Linker: user created', [
        'user_id'  => (int) $uid,
        'provider' => $prov,
        'email'    => $email,
      ]);
    }
    if (!$user || !$user->ID) {
      SESLP_Logger::error('Linker: user lookup failed after create/check', [
        'provider' => $prov,
        'email'    => $email,
      ]);
      return null;
    }

    // Store provider id (canonical + provider-specific) and avatar URL (no media sideload)
    update_user_meta($user->ID, 'seslp_provider', $prov);
    update_user_meta($user->ID, 'seslp_provider_id', $pid);
    update_user_meta($user->ID, 'seslp_' . $prov . '_id', $pid);
    if ($picture !== '') {
      update_user_meta($user->ID, 'seslp_avatar_url', $picture);
      // Do NOT sideload to Media Library by default
      delete_user_meta($user->ID, 'seslp_avatar_id');
    }
    update_user_meta($user->ID, 'seslp_last_provider', $prov);

    // Sign in
    SESLP_Logger::debug('Linker: signing in user', [
      'user_id'  => (int) $user->ID,
      'provider' => $prov,
      'created'  => $created ? 1 : 0,
    ]);
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID, true);

    return $user;
  }
}