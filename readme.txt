=== Simple Easy Social Login – OAuth Login ===
Contributors: selfcoding
Tags: social login, oauth login, google login, naver login, woocommerce
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.9.9
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add Google, Facebook, LinkedIn, Naver, Kakao, and Line social login to WordPress and WooCommerce.

== Description ==
Simple Easy Social Login is a lightweight WordPress plugin that allows you to add fast and easy social login functionality to your website.

It supports Google, Facebook, and LinkedIn (Free), as well as Naver, Kakao, and Line (Premium), and is designed to work especially well for websites targeting users in Asia (Korea, Japan, China) as well as Europe and South America.

The plugin integrates seamlessly with the WordPress login and registration pages, and also works smoothly with WooCommerce login and registration forms.
User social profile images (avatars) can be automatically synchronized with WordPress user profiles.

The plugin is built on an extensible Provider-based architecture, which allows new OAuth Providers to be added later as separate Add-on plugins if needed.

Full documentation is available on GitHub:
https://github.com/CodingDIY/Simple-Easy-Social-Login-Plugin

== Features ==
* Google Login (Free)
* Facebook Login (Free)
* LinkedIn Login (Free)
* Naver Login (Premium)
* Kakao Login (Premium)
* Line Login (Premium)
* Automatic user avatar synchronization
* Automatically link existing WordPress accounts by email
* Custom redirect URLs after login, logout, and registration
* Simple admin UI for configuring each Provider
* Shortcode support: `[se_social_login]`
* Automatically displayed on WordPress login and registration forms
* WooCommerce login and registration form support (optional)
* Lightweight structure following WordPress coding standards
* No unnecessary database tables are created
* Extensible Provider architecture supporting Add-on plugins for new OAuth Providers

== Installation ==
1. Upload the plugin to the `/wp-content/plugins/simple-easy-social-login/` directory.
2. Activate the plugin via **Plugins → Installed Plugins** in the WordPress admin.
3. Go to **Settings → Simple Easy Social Login**.
4. Enter the Client ID and Client Secret for each social login Provider.
5. Save changes.
6. Verify that the social login buttons are displayed correctly on the frontend.

== Frequently Asked Questions ==

= Does this plugin work with WooCommerce? =
Yes. It integrates with WooCommerce login and registration forms.

= WooCommerce login works, but redirect behavior is different. Is this expected? =
Yes. When WooCommerce is active, users are typically redirected to the My Account page after login.
You can customize the redirect URL in the plugin settings or via available filters.

= What should I check if social login does not work on a WooCommerce site? =
Please verify the following:
* WooCommerce is updated to a recent stable version
* The social login Provider is enabled in the plugin settings
* Client ID and Client Secret values are correct
* Redirect / Callback URLs are correctly registered in the Provider's developer console
* Custom login or checkout templates do not remove default WooCommerce hooks
* Debug logging is enabled to review `/wp-content/SESLP-debug.log`

= Can I use only Google login? =
Yes. Each Provider can be enabled or disabled individually.

= When do I need a premium license? =
A premium license is required to use Naver, Kakao, and Line login.
Google, Facebook, and LinkedIn are available for free.

= Is a shortcode available? =
Yes. You can insert social login buttons anywhere using the shortcode:
`[se_social_login]`

= Are user avatars imported automatically? =
For supported Providers such as Google and Facebook, profile images are automatically imported.

== Screenshots ==
1. Social login buttons displayed on the WordPress login page (list layout).
2. Icon-only social login buttons layout on the login screen.
3. Post-login redirect options (dashboard, profile, front page, or custom URL).
4. Debug logging, UI layout options, shortcode, and uninstall settings.
5. Built-in setup guide explaining OAuth redirect rules and common requirements.
6. Step-by-step Google OAuth consent screen and client setup guide.
7. Admin settings for Google, Facebook, and LinkedIn login credentials.
8. Admin settings for Naver, Kakao, and LINE login providers.
9. Unified redirect URI rules used across all supported providers.
10. Debug log location and troubleshooting overview.
11. Common OAuth errors, solutions, and debug log locations for troubleshooting.

== Changelog ==

= 1.9.9 =
* Finalized screenshots and documentation for public release.

= 1.9.8 =
* Fixed a fatal type error in SESLP_Avatar::resolve_user() by ensuring a WP_User or null return value
* Improved avatar fallback handling to safely use WordPress core default avatars
* Prevented broken avatar images when social profile images are missing or invalid
* Minor stability improvements related to avatar rendering

= 1.9.7 =
* Added Debug Logging section to README
* Integrated detailed debug log guide into Admin Guides (multilingual)
* Unified log file path documentation (/wp-content/SESLP-debug.log)
* Documentation cleanup and consistency improvements

= 1.9.6 =
* Improved settings page usability
* Added toggle to show/hide secret keys
* Fixed WordPress core style conflicts
* Improved Pro/Max plan detection logic

= 1.9.5 =
* Major refactoring
* Unified helpers and improved Provider architecture
* Settings UI cleanup
* Improved stability and maintainability

= 1.9.3 =
* Updated translations for Guides
* Added shortcode display to the settings page

= 1.9.2 =
* Internal structure cleanup
* Added Guides loader class
* Template restructuring
* Improved settings and CSS loader stability

= 1.9.1 =
* Added Admin Guide page
* Markdown-based multilingual documentation rendering (Parsedown)
* UI styling improvements

= 1.9.0 =
* Preparation for large-scale refactoring
* Extended i18n helpers
* Safer formatting and logging improvements

= 1.7.23 =
* Translation updates

= 1.7.22 =
* Improved debug messages to show previously logged-in Provider

= 1.7.21 =
* Displayed Provider name in duplicate email error messages
* Auto-hide error messages after 10 seconds via JavaScript

= 1.7.19 =
* Prevented duplicate account creation with the same email
* Improved OAuth flow

= 1.7.18 =
* Removed tooltips from Google Client ID/Secret fields
* Code structure cleanup
* Removed "(Email required)" text from Line login button

= 1.7.17 =
* Fixed Line login issues and unified debug logging

= 1.7.16 =
* Masked license keys in debug logs
* Added admin notices for log write failures

= 1.7.15 =
* Fixed debug log write failures
* Applied WordPress local timezone to timestamps

= 1.7.5 =
* Applied latest security patches
* Performance and UX improvements

= 1.7.0 =
* Improved social login button synchronization
* Security enhancements and bug fixes

= 1.6.0 =
* Restored license key section display logic

= 1.5.0 =
* Registered license type option
* Fixed license reset issue on save

= 1.4.0 =
* Fixed admin style.css loading issues

= 1.3.0 =
* UI improvements and CSS cleanup

= 1.2.0 =
* Added license type selection (Free / Plus / Premium)

= 1.1.0 =
* Added multilingual support
* Improved authentication logic

= 1.0.0 =
* Initial release
* Added Google, Facebook, Naver, Kakao, Line, and Weibo login

== Upgrade Notice ==

= 1.9.9 =
This update improves stability, usability, and security. We recommend updating to the latest version.