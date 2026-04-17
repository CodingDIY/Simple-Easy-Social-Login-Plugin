<?php
/**
 * Social provider interface.
 *
 * Defines the contract that all OAuth provider implementations must follow
 * so the authentication flow can remain provider-agnostic.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
  exit;
}

/**
 * Contract for SESLP OAuth providers.
 *
 * Each provider must implement methods for:
 * - generating authorization URLs,
 * - handling token exchange,
 * - retrieving raw user data,
 * - normalizing user data into a unified structure.
 */
interface SESLP_Provider_Interface {
  /**
   * Build the authorization URL for the provider.
   *
   * @return string
   */
  public function get_auth_url(): string;

  /**
   * Return the OAuth callback URL for the provider.
   *
   * @return string
   */
  public function get_redirect_uri(): string;

  /**
   * Exchange an authorization code for an access token.
   *
   * Implementations should validate the CSRF state token before performing
   * the token request.
   *
   * @param string $code
   * @param string $state
   * @return array<string, mixed>
   */
  public function exchange_code(string $code, string $state): array;

  /**
   * Fetch raw user profile data from the provider.
   *
   * @param string $access_token
   * @return array<string, mixed>
   */
  public function fetch_userinfo(string $access_token): array;

  /**
   * Normalize provider-specific user data into a standard structure.
   *
   * Expected keys: id, email, name, picture
   *
   * @param array<string, mixed> $raw
   * @return array{id:string,email:string,name:string,picture:string}
   */
  public function normalize_userinfo(array $raw): array;
}