<?php
/**
 * Provider interface
 * - All social providers must implement these methods.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
  exit;
}

interface SESLP_Provider_Interface {
  /**
   * Build the authorization URL for this provider.
   *
   * @return string Authorization URL.
   */
  public function get_auth_url(): string;

  /**
   * Get the redirect/callback URI for this provider.
   *
   * @return string Redirect/callback URI.
   */
  public function get_redirect_uri(): string;

  /**
   * Exchange authorization code for access token (and optionally refresh token).
   *
   * @param string $code  Authorization code issued by the provider.
   * @param string $state CSRF state value that should be validated.
   * @return array<string, mixed> Raw token response.
   */
  public function exchange_code(string $code, string $state): array;

  /**
   * Fetch raw userinfo from the provider.
   *
   * @param string $access_token Access token from the provider.
   * @return array<string, mixed> User info payload from the provider API.
   */
  public function fetch_userinfo(string $access_token): array;

  /**
   * Normalize raw userinfo into a standard structure.
   *
   * Expected keys: id, email, name, picture
   *
   * @param array<string, mixed> $raw Raw user info response.
   * @return array<string, string> Standardized user profile fields.
   */
  public function normalize_userinfo(array $raw): array;
}