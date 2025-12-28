🌐 **Read this documentation in other languages:**

- 🇰🇷 [한국어](README.ko_KR.md)
- 🇯🇵 [日本語](README.ja.md)
- 🇨🇳 [简体中文](README.zh_CN.md)
- 🇹🇼 [繁體中文](README.zh_TW.md)
- 🇫🇷 [Français](README.fr_FR.md)
- 🇩🇪 [Deutsch](README.de_DE.md)
- 🇪🇸 [Español](README.es_ES.md)
- 🇧🇷 [Português (Brasil)](README.pt_BR.md)

---

# Simple Easy Social Login – OAuth Login

Simple Easy Social Login is a lightweight and user-friendly WordPress plugin that adds fast and seamless social login functionality to your website.

It supports **Google, Facebook, and LinkedIn (Free)**, as well as **Naver, Kakao, and Line (Premium)**, and is designed to work especially well for websites targeting users in Asia (Korea, Japan, China) as well as Europe and South America.

The plugin integrates smoothly with the WordPress login and registration pages, and also supports WooCommerce login and registration forms.  
Social profile avatars can be automatically synced to WordPress user profiles.

The plugin is built with an **extensible provider architecture**, allowing new OAuth providers to be added later as separate add-on plugins if needed.

---

## ✨ Features

- Google Login (Free)
- Facebook Login (Free)
- LinkedIn Login (Free)
- Naver Login (Premium)
- Kakao Login (Premium)
- Line Login (Premium)
- Automatic user avatar synchronization
- Auto-link existing WordPress users by email
- Custom redirect URLs after login, logout, and registration
- Simple and clean admin UI for provider configuration
- Shortcode support: [se_social_login]
- Automatic display on WordPress login and registration forms
- WooCommerce login and registration form support (optional)
- Lightweight structure following WordPress coding standards
- No unnecessary database tables created
- Extensible provider system supporting add-on plugins for new OAuth providers

---

## Debug Logging

SESLP includes a built-in debug logging system to help diagnose OAuth and login issues.

You can view detailed log explanations directly in the WordPress admin:
**SESLP → Guides → Debug Log & Troubleshooting**

Log files are generated at:

- `/wp-content/SESLP-debug.log`
- `/wp-content/debug.log` (when `WP_DEBUG_LOG` is enabled)

---

## 🚀 Installation

1. Upload the plugin to the `/wp-content/plugins/simple-easy-social-login/` directory.
2. Activate the plugin via **Plugins → Installed Plugins** in the WordPress admin.
3. Go to **Settings → Simple Easy Social Login**.
4. Enter the Client ID and Client Secret for each social login provider.
5. Save changes.
6. Verify that the social login buttons are displayed correctly on the frontend.

---

## ❓ Frequently Asked Questions

### Does this plugin work with WooCommerce?

Yes. It integrates with WooCommerce login and registration forms.

### WooCommerce login works, but redirect behavior is different. Is this expected?

Yes. When WooCommerce is active, users are typically redirected to the **My Account** page after login.  
You can customize the redirect URL in the plugin settings or via available filters.

### What should I check if social login does not work on a WooCommerce site?

Please verify the following:

- WooCommerce is updated to a recent stable version
- The social login provider is enabled in the plugin settings
- Client ID and Client Secret values are correct
- Redirect / Callback URLs are correctly registered in the provider's developer console
- Custom login or checkout templates do not remove default WooCommerce hooks
- Debug logging is enabled and `/wp-content/SESLP-debug.log` is reviewed

### Can I use only Google login?

Yes. Each provider can be enabled or disabled individually.

### When do I need a premium license?

A premium license is required for **Naver, Kakao, and Line** login.  
Google, Facebook, and LinkedIn are available for free.

### Is a shortcode available?

Yes. You can insert social login buttons anywhere using: [se_social_login]

### Are user avatars imported?

Yes. For supported providers such as Google and Facebook, profile images can be automatically imported and synced.

---

## 🖼 Screenshots

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

---

## 📝 Changelog

### 1.9.9

- Finalized screenshots and documentation for the public release
- Added comprehensive screenshot descriptions covering login flow, settings, guides, and troubleshooting
- Minor documentation cleanup and consistency improvements

### 1.9.8

- Fixed a fatal type error in `SESLP_Avatar::resolve_user()` by ensuring a `WP_User|null` return value
- Improved avatar fallback handling:
  - Safely fall back to WordPress core default avatar when a social profile image is missing or invalid
  - Prevent broken avatar images (e.g. LinkedIn profile image issues)
- Minor stability improvements related to avatar rendering

### 1.9.7

- Added Debug Logging section to README
- Integrated detailed debug log guide into Admin Guides (multilingual)
- Unified log file path documentation (`/wp-content/SESLP-debug.log`)
- Documentation cleanup and consistency improvements

### 1.9.6

- Improved settings page usability
- Added toggle for showing/hiding secret keys
- Fixed WordPress core style conflicts
- Improved Pro/Max plan detection logic

### 1.9.5

- Major refactoring
- Unified helpers and improved provider architecture
- Settings UI cleanup
- Improved stability and maintainability

### 1.9.3

- Updated translations for Guides
- Added shortcode display to the settings page

### 1.9.2

- Internal structure cleanup
- Added Guides loader class
- Template restructuring
- Improved settings and CSS loader stability

### 1.9.1

- Added Admin Guide page
- Markdown-based multilingual documentation rendering (Parsedown)
- UI styling improvements

### 1.9.0

- Preparation for large-scale refactoring
- Extended i18n helpers
- Safer formatting and logging improvements

### 1.7.23

- Translation updates

### 1.7.22

- Improved debug messages to show previously logged-in provider

### 1.7.21

- Display provider name in duplicate email error messages
- Auto-hide error messages after 10 seconds via JavaScript

### 1.7.19

- Prevent duplicate account creation with the same email
- Improved OAuth flow:
  - `get_access_token()`
  - `get_user_info()`
  - `create_or_link_user()`

### 1.7.18

- Removed tooltips from Google Client ID/Secret fields
- Code structure cleanup
- Removed "(Email required)" text from Line login button

### 1.7.17

- Fixed Line login issues:
  - Prevent duplicate users on re-login
  - Fixed `/complete-profile` page reappearing
  - Fixed "Invalid request" error by allowing email updates
- Unified debugging logs with `SESLP_Logger`

### 1.7.16

- Masked license keys in debug logs (e.g. abc\*\*\*\*123)
- Added guidance for checking `wp_options` during debugging
- Added admin notice when log writing fails

### 1.7.15

- Fixed debug log write failures
- Applied WordPress local timezone to timestamps
- Added debug logs when saving settings

### 1.7.5

- Applied latest security patches
- Performance optimizations and UX improvements

### 1.7.0

- Improved social login button synchronization
- Security enhancements and bug fixes

### 1.7.3

- Improved debugging system
- Added dedicated debug directory

### 1.6.0

- Restored license key section display via PHP when selecting Plus/Premium

### 1.5.0

- Registered `seslp_license_type` option
- Fixed issue where license type reset to Free on save

### 1.4.0

- Fixed admin `style.css` loading issue using `admin_enqueue_scripts`

### 1.3.0

- Improved radio button layout
- Moved inline CSS to `style.css`

### 1.2.0

- Added license type selection (Free / Plus / Premium)
- Improved settings UI alignment

### 1.1.0

- Added multilingual support and translation file loading
- Improved authentication logic

### 1.0.0

- Initial release
- Added Google, Facebook, Naver, Kakao, Line, and Weibo social login

---

## 📄 License

GPLv2 or later  
https://www.gnu.org/licenses/gpl-2.0.html
