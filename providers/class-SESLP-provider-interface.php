<?php
/**
 * Provider interface
 * - All social providers must implement these methods.
 */

if (!defined('ABSPATH')) exit;

interface SESLP_Provider_Interface {
  /**
   * Build the authorization URL for this provider.
   *
   * @return string
   */
  public function get_auth_url(): string;

  /**
   * Get the redirect/callback URI for this provider.
   *
   * @return string
   */
  public function get_redirect_uri(): string;

  /**
   * Exchange authorization code for access token (and optionally refresh token).
   *
   * @param string $code
   * @param string $state
   * @return array<string, mixed> Raw token response
   */
  public function exchange_code(string $code, string $state): array;

  /**
   * Fetch raw userinfo from the provider.
   *
   * @param string $access_token
   * @return array<string, mixed>
   */
  public function fetch_userinfo(string $access_token): array;

  /**
   * Normalize raw userinfo into a standard structure.
   *
   * Expected keys: id, email, name, picture
   *
   * @param array<string, mixed> $raw
   * @return array<string, string>
   */
  public function normalize_userinfo(array $raw): array;
}