<?php
/**
 * Authentication router and OAuth callback handler.
 *
 * Responsible for:
 * - routing public social login callback requests,
 * - validating callback state before provider handling,
 * - dispatching provider-specific OAuth callback handlers,
 * - completing sign-in after profile retrieval.
 */

declare(strict_types=1);
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Handle public OAuth callback routing for supported providers.
 *
 * This class keeps callback flow logic separate from the main plugin
 * bootstrap so provider authentication handling remains isolated.
 */
final class SESLP_Auth {
  /**
   * Register the public authentication router hook.
   *
   * @return void
   */
  public function register(): void {
    add_action('template_redirect', [$this, 'maybe_route_auth']);
  }

  /**
   * Route incoming OAuth callback requests to the correct provider handler.
   *
   * This method intentionally reads callback query parameters from `$_GET`
   * because OAuth providers redirect back to WordPress with read-only query
   * values. A standard WordPress form nonce is not used for this cross-site
   * redirect flow. Callback integrity is enforced through the provider state
   * validation layer before any login action is completed.
   *
   * @return void
   */
  public function maybe_route_auth(): void {
    if (empty($_GET['social_login'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public OAuth callback route. CSRF protection is enforced later via SESLP_State::validate().
      return;
    }
    $provider = sanitize_key(wp_unslash($_GET['social_login'])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public OAuth callback route. CSRF protection is enforced later via SESLP_State::validate().

    SESLP_Logger::debug('Auth route triggered', [
      'provider' => $provider,
      'has_code' => isset($_GET['code']) ? 1 : 0, // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public OAuth callback route. Read-only callback flag.
    ]);

    // ==================== SECURITY: Early Nonce/State Validation ====================
    // Only validate when it's a callback (contains 'code')
    if (isset($_GET['code'])) {
      $state = isset($_GET['state']) ? sanitize_text_field(wp_unslash($_GET['state'])) : '';

      // 1) WordPress nonce check (primary)
      if (empty($state) || !wp_verify_nonce($state, 'seslp_oauth_state')) {
        SESLP_Logger::warning('Nonce verification failed in auth router', [
          'provider' => $provider,
          'state_present' => !empty($state)
        ]);
        wp_safe_redirect(wp_login_url(add_query_arg('seslp_err', 'invalid_nonce', home_url('/'))));
        exit;
      }

      // 2) Additional custom state validation (existing logic)
      if (!SESLP_State::validate($provider, $state)) {
        SESLP_Logger::warning('Invalid state (custom validation)', ['provider' => $provider, 'state' => $state]);
        wp_safe_redirect(wp_login_url(add_query_arg('seslp_err', 'invalid_state', home_url('/'))));
        exit;
      }
    }
    // =============================================================================

    // Only handle callbacks here (start flow links go directly to provider auth URLs)
    if (!isset($_GET['code'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public OAuth callback route. Read-only callback flag.
      return;
    }

    $handlers = $this->get_callback_handlers();
    $handler  = $handlers[$provider] ?? null;
    if (is_callable($handler)) {
      call_user_func($handler);
      return;
    }

    SESLP_Logger::warning('Unknown provider callback ignored', ['provider' => $provider]);
  }

  /**
   * Return the map of provider slugs to callback handlers.
   *
   * @return array<string, callable>
   */
  private function get_callback_handlers(): array {
    $handlers = [
      'google'   => [$this, 'handle_google_callback'],
      'linkedin' => [$this, 'handle_linkedin_callback'],
      'facebook' => [$this, 'handle_facebook_callback'],
      'naver'    => [$this, 'handle_naver_callback'],
      'kakao'    => [$this, 'handle_kakao_callback'],
      'line'     => [$this, 'handle_line_callback'],
      'weibo'    => [$this, 'handle_weibo_callback'],
    ];

    /**
     * Allow customizing callback handlers per provider.
     *
     * @param array<string, callable> $handlers
     */
    return apply_filters('seslp_auth_callback_handlers', $handlers);
  }

  /**
   * Handle the Google OAuth callback flow.
   *
   * Exchanges the authorization code for an access token, fetches the user
   * profile, then links or signs in the matching WordPress user.
   *
   * @return void
   */
  private function handle_google_callback(): void {
    // Validate state & code
    $state = isset($_GET['state']) ? sanitize_text_field(wp_unslash($_GET['state'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth provider callback parameter. State validation is enforced below.
    $code = isset($_GET['code']) ? sanitize_text_field(wp_unslash($_GET['code'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth provider callback parameter. State validation is enforced below.
    
    SESLP_Logger::debug('Google callback received', [
      'state' => $state,
      'code_present' => isset($_GET['code']) ? 1 : 0, // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth provider callback parameter. State validation is enforced below.
    ]);

    if ($code === '') {
      SESLP_Logger::warning('Missing code (google)');
      wp_safe_redirect(wp_login_url(add_query_arg('seslp_err', 'missing_code', home_url('/'))));
      exit;
    }

    $opts = SESLP_Helpers::get_options();
    $cid  = trim((string)($opts['providers']['google']['client_id'] ?? ''));
    $sec  = trim((string)($opts['providers']['google']['client_secret'] ?? ''));

    if ($cid === '' || $sec === '') {
      wp_safe_redirect(wp_login_url(add_query_arg('seslp_err', 'google_keys_missing', home_url('/'))));
      exit;
    }

    // Exchange code for tokens
    $token_resp = wp_remote_post('https://oauth2.googleapis.com/token', [
      'timeout' => 15,
      'body' => [
        'code'          => $code,
        'client_id'     => $cid,
        'client_secret' => $sec,
        'redirect_uri'  => (new SESLP_Provider_Google())->get_redirect_uri(),
        'grant_type'    => 'authorization_code',
      ],
    ]);

    if (is_wp_error($token_resp)) {
      SESLP_Logger::error('Token request failed (google)', [
        'error' => $token_resp->get_error_message(),
      ]);
      wp_safe_redirect(wp_login_url(add_query_arg('seslp_err', 'token_request_failed', home_url('/'))));
      exit;
    }
    $token_body = json_decode(wp_remote_retrieve_body($token_resp), true);
    SESLP_Logger::debug('Token response (google)', [
      'has_access_token' => isset($token_body['access_token']) ? 1 : 0,
    ]);
    $access     = (string)($token_body['access_token'] ?? '');
    if ($access === '') {
      SESLP_Logger::error('No access_token in response (google)');
      wp_safe_redirect(wp_login_url(add_query_arg('seslp_err', 'no_access_token', home_url('/'))));
      exit;
    }

    // Fetch userinfo
    $ui_resp = wp_remote_get('https://www.googleapis.com/oauth2/v3/userinfo', [
      'headers' => ['Authorization' => 'Bearer ' . $access],
      'timeout' => 15,
    ]);
    if (is_wp_error($ui_resp)) {
      SESLP_Logger::error('Userinfo request failed (google)', [
        'error' => $ui_resp->get_error_message(),
      ]);
      wp_safe_redirect(wp_login_url(add_query_arg('seslp_err', 'userinfo_failed', home_url('/'))));
      exit;
    }
    $ui = json_decode(wp_remote_retrieve_body($ui_resp), true);
    $email = sanitize_email((string)($ui['email'] ?? ''));
    $sub   = sanitize_text_field((string)($ui['sub'] ?? ''));
    $name  = sanitize_text_field((string)($ui['name'] ?? ''));

    if ($email === '' || $sub === '') {
      SESLP_Logger::warning('Invalid userinfo (google)', [
        'email_present' => $email !== '' ? 1 : 0,
        'sub_present'   => $sub !== '' ? 1 : 0,
      ]);
      wp_safe_redirect(wp_login_url(add_query_arg('seslp_err', 'invalid_userinfo', home_url('/'))));
      exit;
    }

    // Link or create WP user
    $profile = [
      'id'      => $sub,
      'email'   => $email,
      'name'    => $name,
      'picture' => (string)($ui['picture'] ?? ''),
    ];
    $user = (new SESLP_User_Linker())->link_or_create_and_sign_in($profile, 'google');
    if ($user && $user->ID) {
      SESLP_Logger::info('Login success (google)', [
        'user_id' => (int) $user->ID,
        'email'   => $email,
      ]);
      wp_safe_redirect( SESLP_Redirect::after_login_url($user) );
      exit;
    }
    SESLP_Logger::error('Unknown error after google callback');
    wp_safe_redirect(wp_login_url(add_query_arg('seslp_err', 'unknown_error', home_url('/'))));
    exit;
  }
  
  /**
   * Handle the Facebook OAuth callback flow.
   *
   * Delegates token exchange and profile normalization to the Facebook
   * provider class, then links or signs in the matching WordPress user.
   *
   * @return void
   */
  private function handle_facebook_callback(): void {
    // Validate state & code
    $state = isset($_GET['state']) ? sanitize_text_field(wp_unslash($_GET['state'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth provider callback parameter. State validation is enforced below.
    $code  = isset($_GET['code'])  ? sanitize_text_field(wp_unslash($_GET['code']))  : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth provider callback parameter. State validation is enforced below.

    SESLP_Logger::debug('Facebook callback received', [
      'state'        => $state !== '' ? substr($state, 0, 3) . str_repeat('*', max(0, strlen($state) - 6)) . substr($state, -3) : '',
      'code_present' => $code !== '' ? 1 : 0,
    ]);

    if ($code === '') {
      SESLP_Logger::warning('Missing code (facebook)');
      wp_safe_redirect( add_query_arg('seslp_err', 'missing_code', wp_login_url()) );
      exit;
    }

    if (!class_exists('SESLP_Provider_Facebook')) {
      wp_safe_redirect( add_query_arg('seslp_err', 'unknown_error', wp_login_url()) );
      exit;
    }

    $fb    = new SESLP_Provider_Facebook();
    $token = $fb->exchange_code($code, $state);
    SESLP_Logger::debug('Token response (facebook)', [ 'has_access_token' => (int) !empty($token['access_token']) ]);

    $access = (string)($token['access_token'] ?? '');
    if ($access === '') {
      wp_safe_redirect( add_query_arg('seslp_err', 'no_access_token', wp_login_url()) );
      exit;
    }

    $raw = $fb->fetch_userinfo($access);
    $ui  = $fb->normalize_userinfo($raw);

    $email = sanitize_email((string)($ui['email'] ?? ''));
    $fid   = sanitize_text_field((string)($ui['id'] ?? ''));
    $name  = sanitize_text_field((string)($ui['name'] ?? ''));

    if ($email === '' || $fid === '') {
      SESLP_Logger::warning('Invalid userinfo (facebook)', [
        'email_present' => $email !== '' ? 1 : 0,
        'id_present'    => $fid   !== '' ? 1 : 0,
      ]);
      wp_safe_redirect( add_query_arg('seslp_err', 'invalid_userinfo', wp_login_url()) );
      exit;
    }

    $profile = [
      'id'      => $fid,
      'email'   => $email,
      'name'    => $name,
      'picture' => (string)($ui['picture'] ?? ''),
    ];

    $user = (new SESLP_User_Linker())->link_or_create_and_sign_in($profile, 'facebook');
    if ($user && isset($user->ID)) {
      SESLP_Logger::info('Login success (facebook)', [
        'user_id' => (int) $user->ID,
        'email'   => SESLP_Logger::mask_email($email),
      ]);
      wp_safe_redirect( SESLP_Redirect::after_login_url($user) );
      exit;
    }

    SESLP_Logger::error('Unknown error after facebook callback');
    wp_safe_redirect( add_query_arg('seslp_err', 'unknown_error', wp_login_url()) );
    exit;
  }

  /**
   * Handle the Naver OAuth callback flow.
   *
   * Exchanges the authorization code for an access token, fetches the user
   * profile, then links or signs in the matching WordPress user.
   *
   * @return void
   */
  private function handle_naver_callback(): void {
    // Validate state & code
    $state = isset($_GET['state']) ? sanitize_text_field(wp_unslash($_GET['state'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth provider callback parameter. State validation is enforced below.
    $code = isset($_GET['code']) ? sanitize_text_field(wp_unslash($_GET['code'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth provider callback parameter. State validation is enforced below.
    
    SESLP_Logger::debug('Naver callback received', [
      'state' => $state,
      'code_present' => isset($_GET['code']) ? 1 : 0, // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth provider callback parameter. State validation is enforced below.
    ]);

    if ($code === '') {
      SESLP_Logger::warning('Missing code (naver)');
      wp_safe_redirect(wp_login_url(add_query_arg('seslp_err', 'missing_code', home_url('/'))));
      exit;
    }

    // Credentials
    $opts = SESLP_Helpers::get_options();
    $cid  = trim((string)($opts['providers']['naver']['client_id'] ?? ''));
    $sec  = trim((string)($opts['providers']['naver']['client_secret'] ?? ''));
    if ($cid === '' || $sec === '') {
      wp_safe_redirect(wp_login_url(add_query_arg('seslp_err', 'naver_keys_missing', home_url('/'))));
      exit;
    }

    // Exchange code -> token
    $token_resp = wp_remote_post('https://nid.naver.com/oauth2.0/token', [
      'timeout' => 15,
      'body' => [
        'grant_type'    => 'authorization_code',
        'client_id'     => $cid,
        'client_secret' => $sec,
        'code'          => $code,
        'state'         => $state,
        'redirect_uri'  => (new SESLP_Provider_Naver())->get_redirect_uri(),
      ],
    ]);
    if (is_wp_error($token_resp)) {
      SESLP_Logger::error('Token request failed (naver)', [
        'error' => $token_resp->get_error_message(),
      ]);
      wp_safe_redirect(wp_login_url(add_query_arg('seslp_err', 'token_request_failed', home_url('/'))));
      exit;
    }
    $token_body = json_decode(wp_remote_retrieve_body($token_resp), true);
    SESLP_Logger::debug('Token response (naver)', [
      'has_access_token' => isset($token_body['access_token']) ? 1 : 0,
    ]);
    $access     = (string)($token_body['access_token'] ?? '');
    if ($access === '') {
      SESLP_Logger::error('No access_token in response (naver)');
      wp_safe_redirect(wp_login_url(add_query_arg('seslp_err', 'no_access_token', home_url('/'))));
      exit;
    }

    // Fetch user info
    $ui_resp = wp_remote_get('https://openapi.naver.com/v1/nid/me', [
      'headers' => ['Authorization' => 'Bearer ' . $access],
      'timeout' => 15,
    ]);
    if (is_wp_error($ui_resp)) {
      SESLP_Logger::error('Userinfo request failed (naver)', [
        'error' => $ui_resp->get_error_message(),
      ]);
      wp_safe_redirect(wp_login_url(add_query_arg('seslp_err', 'userinfo_failed', home_url('/'))));
      exit;
    }
    $ui_raw = json_decode(wp_remote_retrieve_body($ui_resp), true);
    $resp   = is_array($ui_raw) ? ($ui_raw['response'] ?? []) : [];

    $email = sanitize_email((string)($resp['email'] ?? ''));
    $nid   = sanitize_text_field((string)($resp['id'] ?? ''));
    $name  = sanitize_text_field((string)($resp['nickname'] ?? ($resp['name'] ?? '')));

    if ($email === '' || $nid === '') {
      SESLP_Logger::warning('Invalid userinfo (naver)', [
        'email_present' => $email !== '' ? 1 : 0,
        'id_present'    => $nid !== '' ? 1 : 0,
      ]);
      wp_safe_redirect(wp_login_url(add_query_arg('seslp_err', 'invalid_userinfo', home_url('/'))));
      exit;
    }

    // Link or create WP user
    $profile = [
      'id'      => $nid,
      'email'   => $email,
      'name'    => $name,
      'picture' => (string)($resp['profile_image'] ?? ''),
    ];
    $user = (new SESLP_User_Linker())->link_or_create_and_sign_in($profile, 'naver');
    if ($user && $user->ID) {
      SESLP_Logger::info('Login success (naver)', [
        'user_id' => (int) $user->ID,
        'email'   => $email,
      ]);
      wp_safe_redirect( SESLP_Redirect::after_login_url($user) );
      exit;
    }
    SESLP_Logger::error('Unknown error after naver callback');
    wp_safe_redirect(wp_login_url(add_query_arg('seslp_err', 'unknown_error', home_url('/'))));
    exit;
  }

  /**
   * Handle the Kakao OAuth callback flow.
   *
   * Delegates token exchange and profile normalization to the Kakao provider
   * class, then links or signs in the matching WordPress user.
   *
   * @return void
   */
  private function handle_kakao_callback(): void {
    // Validate state & code
    $state = isset($_GET['state']) ? sanitize_text_field(wp_unslash($_GET['state'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth provider callback parameter. State validation is enforced below.
    $code  = isset($_GET['code'])  ? sanitize_text_field(wp_unslash($_GET['code']))  : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth provider callback parameter. State validation is enforced below.

    SESLP_Logger::debug('Kakao callback received', [
      'state'        => $state !== '' ? substr($state, 0, 3) . str_repeat('*', max(0, strlen($state) - 6)) . substr($state, -3) : '',
      'code_present' => $code !== '' ? 1 : 0,
    ]);

    if ($code === '') {
      SESLP_Logger::warning('Missing code (kakao)');
      wp_safe_redirect( add_query_arg('seslp_err', 'missing_code', wp_login_url()) );
      exit;
    }

    if (!class_exists('SESLP_Provider_Kakao')) {
      wp_safe_redirect( add_query_arg('seslp_err', 'unknown_error', wp_login_url()) );
      exit;
    }

    $kk    = new SESLP_Provider_Kakao();
    $token = $kk->exchange_code($code, $state);
    SESLP_Logger::debug('Token response (kakao)', [ 'has_access_token' => (int) !empty($token['access_token']) ]);

    $access = (string)($token['access_token'] ?? '');
    if ($access === '') {
      wp_safe_redirect( add_query_arg('seslp_err', 'no_access_token', wp_login_url()) );
      exit;
    }

    $raw = $kk->fetch_userinfo($access);
    $ui  = $kk->normalize_userinfo($raw);

    $email = sanitize_email((string)($ui['email'] ?? ''));
    $kid   = sanitize_text_field((string)($ui['id'] ?? ''));
    $name  = sanitize_text_field((string)($ui['name'] ?? ''));

    if ($email === '' || $kid === '') {
      SESLP_Logger::warning('Invalid userinfo (kakao)', [
        'email_present' => $email !== '' ? 1 : 0,
        'id_present'    => $kid   !== '' ? 1 : 0,
      ]);
      wp_safe_redirect( add_query_arg('seslp_err', 'invalid_userinfo', wp_login_url()) );
      exit;
    }

    $profile = [
      'id'      => $kid,
      'email'   => $email,
      'name'    => $name,
      'picture' => (string)($ui['picture'] ?? ''),
    ];

    $user = (new SESLP_User_Linker())->link_or_create_and_sign_in($profile, 'kakao');
    if ($user && isset($user->ID)) {
      SESLP_Logger::info('Login success (kakao)', [
        'user_id' => (int) $user->ID,
        'email'   => SESLP_Logger::mask_email($email),
      ]);
      wp_safe_redirect( SESLP_Redirect::after_login_url($user) );
      exit;
    }

    SESLP_Logger::error('Unknown error after kakao callback');
    wp_safe_redirect( add_query_arg('seslp_err', 'unknown_error', wp_login_url()) );
    exit;
  }

  /**
   * Handle the LINE OAuth callback flow.
   *
   * Delegates token exchange and profile normalization to the LINE provider
   * class, then links or signs in the matching WordPress user.
   *
   * @return void
   */
  private function handle_line_callback(): void {
    // Validate state & code
    $state = isset($_GET['state']) ? sanitize_text_field(wp_unslash($_GET['state'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth provider callback parameter. State validation is enforced below.
    $code  = isset($_GET['code'])  ? sanitize_text_field(wp_unslash($_GET['code']))  : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth provider callback parameter. State validation is enforced below.

    SESLP_Logger::debug('Line callback received', [
      'state'        => $state !== '' ? substr($state, 0, 3) . str_repeat('*', max(0, strlen($state) - 6)) . substr($state, -3) : '',
      'code_present' => $code !== '' ? 1 : 0,
    ]);

    if ($code === '') {
      SESLP_Logger::warning('Missing code (line)');
      wp_safe_redirect( add_query_arg('seslp_err', 'missing_code', wp_login_url()) );
      exit;
    }

    if (!class_exists('SESLP_Provider_Line')) {
      wp_safe_redirect( add_query_arg('seslp_err', 'unknown_error', wp_login_url()) );
      exit;
    }

    $ln    = new SESLP_Provider_Line();
    $token = $ln->exchange_code($code, $state);
    SESLP_Logger::debug('Token response (line)', [ 'has_access_token' => (int) !empty($token['access_token']) ]);

    $access = (string)($token['access_token'] ?? '');
    if ($access === '') {
      wp_safe_redirect( add_query_arg('seslp_err', 'no_access_token', wp_login_url()) );
      exit;
    }

    $raw = $ln->fetch_userinfo($access);
    $ui  = $ln->normalize_userinfo($raw);

    $email = sanitize_email((string)($ui['email'] ?? ''));
    $lid   = sanitize_text_field((string)($ui['id'] ?? ''));
    $name  = sanitize_text_field((string)($ui['name'] ?? ''));

    // If email missing but id_token is present, try verifying id_token
    if ($email === '' && !empty($token['id_token'])) {
      $email = $ln->fetch_email_from_id_token((string)$token['id_token']);
    }

    if ($email === '' || $lid === '') {
      SESLP_Logger::warning('Invalid userinfo (line)', [
        'email_present' => $email !== '' ? 1 : 0,
        'id_present'    => $lid   !== '' ? 1 : 0,
      ]);
      wp_safe_redirect( add_query_arg('seslp_err', 'invalid_userinfo', wp_login_url()) );
      exit;
    }

    $profile = [
      'id'      => $lid,
      'email'   => $email,
      'name'    => $name,
      'picture' => (string)($ui['picture'] ?? ''),
    ];

    $user = (new SESLP_User_Linker())->link_or_create_and_sign_in($profile, 'line');
    if ($user && isset($user->ID)) {
      SESLP_Logger::info('Login success (line)', [
        'user_id' => (int) $user->ID,
        'email'   => SESLP_Logger::mask_email($email),
      ]);
      wp_safe_redirect( SESLP_Redirect::after_login_url($user) );
      exit;
    }

    SESLP_Logger::error('Unknown error after line callback');
    wp_safe_redirect( add_query_arg('seslp_err', 'unknown_error', wp_login_url()) );
    exit;
  }

  /**
   * Handle legacy Weibo callback requests safely.
   *
   * The Weibo provider has been removed, so this method redirects old or
   * bookmarked callback URLs back to the login screen with a friendly notice.
   *
   * @return void
   */
  private function handle_weibo_callback(): void {
    // Weibo provider has been deprecated/removed due to ICP constraints.
    // If this legacy path is reached (old bookmarks/links), redirect safely with a friendly flag.
    SESLP_Logger::info('Weibo callback reached after provider removal; redirecting safely.');

    $redirect = add_query_arg(
      [
        'seslp_notice' => 'provider_removed',
        'provider'     => 'weibo',
      ],
      wp_login_url()
    );

    wp_safe_redirect($redirect);
    exit;
  }

  /**
   * Handle the LinkedIn OAuth callback flow.
   *
   * Delegates token exchange and profile retrieval to the LinkedIn provider
   * class, then links or signs in the matching WordPress user.
   *
   * @return void
   */
  private function handle_linkedin_callback(): void {
    // Verify state & code
    $state = isset($_GET['state']) ? sanitize_text_field(wp_unslash($_GET['state'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth provider callback parameter. State validation is enforced below.
    $code = isset($_GET['code']) ? sanitize_text_field(wp_unslash($_GET['code'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth provider callback parameter. State validation is enforced below.
    
    SESLP_Logger::debug('LinkedIn callback received', [
      'state'        => $state,
      'code_present' => isset($_GET['code']) ? 1 : 0, // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth provider callback parameter. State validation is enforced below.
    ]);

    if ($code === '') {
      SESLP_Logger::warning('Missing code (linkedin)');
      wp_safe_redirect(wp_login_url(add_query_arg('seslp_err', 'missing_code', home_url('/'))));
      exit;
    }

    if (!class_exists('SESLP_Provider_Linkedin')) {
      SESLP_Logger::error('LinkedIn provider class missing');
      wp_safe_redirect(wp_login_url(add_query_arg('seslp_err', 'provider_missing', home_url('/'))));
      exit;
    }

    $prov   = new SESLP_Provider_Linkedin();
    $tokens = $prov->exchange_code($code, $state); // pass state to satisfy the interface

    if (!empty($tokens['error'])) {
      SESLP_Logger::error('LinkedIn token exchange failed', $tokens);
      wp_safe_redirect(wp_login_url(add_query_arg('seslp_err', 'token_exchange_failed', home_url('/'))));
      exit;
    }

    $access_token = (string)($tokens['access_token'] ?? '');
    $profile      = $prov->fetch_userinfo($access_token);

    if (!empty($profile['error'])) {
      SESLP_Logger::error('LinkedIn userinfo fetch failed', $profile);
      wp_safe_redirect(wp_login_url(add_query_arg('seslp_err', 'userinfo_failed', home_url('/'))));
      exit;
    }

    $email = $profile['email'] ?? '';
    $user  = (new SESLP_User_Linker())->link_or_create_and_sign_in($profile, 'linkedin');

    if ($user && isset($user->ID)) {
      SESLP_Logger::info('Login success (linkedin)', [
        'user_id' => (int)$user->ID,
        'email'   => SESLP_Logger::mask_email($email),
      ]);
      wp_safe_redirect(SESLP_Redirect::after_login_url($user));
      exit;
    }

    SESLP_Logger::error('Unknown error after linkedin callback');
    wp_safe_redirect(add_query_arg('seslp_err', 'unknown_error', wp_login_url()));
    exit;
  }
}