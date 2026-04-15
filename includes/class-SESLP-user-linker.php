<?php
/**
 * User Linker
 * - Centralizes linking/creation of WP users from normalized provider profile
 * - Expected profile keys: id, email, name, picture
 */
declare(strict_types=1);

if (!defined('ABSPATH')) {
  exit;
}

final class SESLP_User_Linker {
  /**
   * Link an existing user by email or create a new subscriber when registration is allowed, then sign in.
   *
   * @param array<string,string> $profile {id,email,name,picture}
   * @param string               $provider e.g. 'google', 'naver'
   * @return WP_User|null Signed-in user or null on failure
   */
  public function link_or_create_and_sign_in(array $profile, string $provider): ?WP_User {
    $data    = $this->normalize_profile($profile, $provider);
    $email   = $data['email'];
    $pid     = $data['id'];
    $name    = $data['name'];
    $picture = $data['picture'];
    $prov    = $data['provider'];

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
      $prov_meta = $this->migrate_and_infer_provider_meta((int) $user->ID, $prov, $pid);

      // Unified policy: if no provider is linked OR linked with a different provider → show one unified error
      if ($prov_meta === '' || $prov_meta !== $prov) {
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
    } else {
      // No user found → create a new one
      if (!get_option('users_can_register')) {
        SESLP_Logger::warning('Linker: user creation blocked because registration is disabled', [
          'provider' => $prov,
          'email'    => SESLP_Logger::mask_email($email),
        ]);
        return null;
      }

      $opts        = SESLP_Helpers::get_options();
      $auto_create = (bool) ($opts['general']['auto_create_user'] ?? true);

      if (!$auto_create) {
        SESLP_Logger::warning('Linker: user creation disabled by SESLP setting', [
          'provider' => $prov,
          'email'    => SESLP_Logger::mask_email($email),
        ]);

        // Redirect to login page.
        $login_url = add_query_arg([
          'seslp_err' => 'registration_disabled_by_plugin',
        ], wp_login_url());

        wp_safe_redirect($login_url);
        exit;
      }

      $username = $this->build_unique_username($email, $prov);

      $uid = wp_insert_user([
        'user_login'   => $username,
        'user_email'   => $email,
        'display_name' => $name !== '' ? $name : $username,
        'user_pass'    => wp_generate_password(24),
        'role'         => 'subscriber',
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
      $user    = get_user_by('id', (int) $uid);

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

    // Store provider id (canonical + provider-specific)
    $this->ensure_provider_metadata((int) $user->ID, $prov, $pid);

    // Store avatar URL (no media sideload)
    if ($picture !== '') {
      update_user_meta($user->ID, 'seslp_avatar_url', $picture);
      // Do NOT sideload to Media Library by default
      delete_user_meta($user->ID, 'seslp_avatar_id');
    }

    update_user_meta($user->ID, 'seslp_last_provider', $prov);

    // Sign in
    SESLP_Logger::debug('Linker: signing in user', [
      'user_id'  => (int) $user->ID,
      'provider' => (string) $prov,
      'created'  => $created ? 1 : 0,
    ]);

    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID, false);

    /**
     * Fires after a SESLP user is linked or created and signed in.
     *
     * @param WP_User $user    The linked/created user.
     * @param string  $provider Provider slug.
     * @param bool    $created Whether the user was created during this request.
     */
    do_action('wp_login', $user->user_login, $user);

    do_action('seslp_user_linked', $user, $prov, $created);

    return $user;
  }

  /**
   * Normalize incoming profile fields and provider.
   *
   * @param array<string,string> $profile
   * @param string               $provider
   * @return array{email:string,id:string,name:string,picture:string,provider:string}
   */
  private function normalize_profile(array $profile, string $provider): array {
    return [
      'email'    => sanitize_email((string) ($profile['email'] ?? '')),
      'id'       => sanitize_text_field((string) ($profile['id'] ?? '')),
      'name'     => sanitize_text_field((string) ($profile['name'] ?? '')),
      'picture'  => esc_url_raw((string) ($profile['picture'] ?? '')),
      'provider' => sanitize_key($provider),
    ];
  }

  /**
   * Migrate legacy meta keys and infer provider linkage when canonical keys are missing.
   *
   * - Migrates:
   *   - social_provider -> seslp_provider
   *   - social_id       -> seslp_provider_id
   * - If canonical provider meta is empty, tries provider-specific clues:
   *   - seslp_{provider}_id
   *   - seslp_last_provider
   *
   * @param int    $user_id
   * @param string $provider
   * @param string $pid
   * @return string Canonical provider slug if linked, otherwise empty string.
   */
  private function migrate_and_infer_provider_meta(int $user_id, string $provider, string $pid): string {
    $prov_meta = get_user_meta($user_id, 'seslp_provider', true);
    if ($prov_meta === '' || $prov_meta === false) {
      $prov_meta = '';
    }

    // Legacy provider migration
    if ($prov_meta === '') {
      $legacy_prov = get_user_meta($user_id, 'social_provider', true);
      if (!empty($legacy_prov)) {
        $prov_meta = (string) $legacy_prov;
        update_user_meta($user_id, 'seslp_provider', $legacy_prov);
      }
    }

    // Legacy provider id migration
    $prov_id_meta = get_user_meta($user_id, 'seslp_provider_id', true);
    if ($prov_id_meta === '' || $prov_id_meta === false) {
      $legacy_id = get_user_meta($user_id, 'social_id', true);
      if (!empty($legacy_id)) {
        $prov_id_meta = (string) $legacy_id;
        update_user_meta($user_id, 'seslp_provider_id', $legacy_id);
      }
    }

    // Fallback detection: if canonical provider meta is empty, try provider-specific clues
    if ($prov_meta === '') {
      $by_specific = get_user_meta($user_id, 'seslp_' . $provider . '_id', true);
      $by_last     = get_user_meta($user_id, 'seslp_last_provider', true);

      // If provider-specific id exists and matches (or pid is unavailable), consider it linked
      if (!empty($by_specific) && ($pid === '' || $by_specific === $pid)) {
        $prov_meta = $provider; // treat as linked with current provider

        // backfill canonical keys for future logins
        $this->ensure_provider_metadata($user_id, $provider, $pid);

        SESLP_Logger::debug('Linker: inferred link via provider-specific meta', [
          'user_id' => $user_id,
          'prov'    => $provider,
        ]);
      } elseif (!empty($by_last) && $by_last === $provider) {
        // As a weaker signal, last successful provider also implies linkage
        $prov_meta = $provider;

        $this->ensure_provider_metadata($user_id, $provider, $pid);

        SESLP_Logger::debug('Linker: inferred link via last provider', [
          'user_id' => $user_id,
          'prov'    => $provider,
        ]);
      }
    }

    return (string) $prov_meta;
  }

  /**
   * Ensure canonical provider metadata exists for the given user.
   *
   * @param int    $user_id
   * @param string $provider
   * @param string $pid
   * @return void
   */
  private function ensure_provider_metadata(int $user_id, string $provider, string $pid): void {
    if (!get_user_meta($user_id, 'seslp_provider', true)) {
      update_user_meta($user_id, 'seslp_provider', $provider);
    }

    if (!get_user_meta($user_id, 'seslp_provider_id', true) && $pid !== '') {
      update_user_meta($user_id, 'seslp_provider_id', $pid);
    }

    // Always maintain provider-specific meta as well
    update_user_meta($user_id, 'seslp_' . $provider . '_id', $pid);
  }

  /**
   * Build a unique username derived from email and provider.
   *
   * @param string $email
   * @param string $provider
   * @return string
   */
  private function build_unique_username(string $email, string $provider): string {
    $local = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', (string) current(explode('@', $email)));
    if ($local === '') {
      $local = 'user';
    }

    $base     = strtolower($local . '_' . $provider);
    $username = sanitize_user($base, true);
    if ($username === '') {
      $username = 'user_' . $provider;
    }

    // Ensure uniqueness by appending incremental suffix if needed
    $try = $username;
    $i   = 1;

    while (username_exists($try)) {
      $try = $username . '_' . $i;
      $i++;
    }

    return $try;
  }
}