> This document explains how to configure each social login provider(Google, Facebook, LinkedIn, Naver, Kakao, LINE) in the **Simple Easy Social Login (SESLP)** plugin.
> All sign-ins are based on **OAuth 2.0 / OpenID Connect (OIDC)**.  
> You must create an app (client) in each provider’s console and enter the **Client ID / Client Secret** in SESLP.

---

## 🔧 Common Setup Guide

#### 1) **Redirect URI rule:**

`https://{your-domain}/?social_login={provider}`

Examples:

- Google → `https://example.com/?social_login=google`
- Facebook → `https://example.com/?social_login=facebook`
- LinkedIn → `https://example.com/?social_login=linkedin`
- Naver → `https://example.com/?social_login=naver`
- Kakao → `https://example.com/?social_login=kakao`
- LINE → `https://example.com/?social_login=line`

#### 2) **HTTPS required.**

Most providers require HTTPS and will reject `http://` redirects.

#### 3) **Exact matching**

The Redirect URI in the console must match **100%** with what SESLP sends  
 (protocol, subdomain, path, trailing slash, and query string).

#### 4) **Email may be unavailable**

Some providers allow users to deny email sharing. SESLP can fall back to stable provider IDs to link accounts.

#### 5) **Where to check logs**

- `/wp-content/seslp-logs/seslp-debug.log`
- `/wp-content/debug.log` (`WP_DEBUG_LOG = true`)

---

## 🌍 Provider Guides

> Expand each provider below and paste the English guide content you’ve prepared for that provider.

---

<details open>
  <summary><strong>Google</strong></summary>

> - **Recommended scopes:** `openid email profile`
> - **Redirect URI rule:** `https://{domain}/?social_login=google`

---

#### 1) Preparation (Mandatory Checklist)

(1) **HTTPS recommended/essential** (Use trusted development certificates for local environments).

(2) Redirect URI must **exactly match 100%** the value registered in the console. Ex) `https://example.com/?social_login=google`

(3) In test mode, only **test users** can log in (up to 100 users).

(4) When using app homepage/privacy policy/terms URLs, **app domains (Authorized domains)** registration and **ownership verification** may be required.

#### 2) Project/Consent Screen Setup

(1) Access **Google Cloud Console**.
[https://console.cloud.google.com/apis/credentials](https://console.cloud.google.com/apis/credentials)

(2) Select project at top → **Create new project** (if needed).

(3) Sidebar: go to **APIs & Services → OAuth consent screen**.

(4) Select **User Type**: usually **External**.

(5) Enter **App Information**: App name, user support email, (optional) logo.

(6) **App domain** section

- Enter app homepage URL, privacy policy URL, terms of service URL
- Add **root domain (e.g., example.com)** to **Authorized domains** → **Save**
- If needed, perform **domain ownership verification** via Search Console.

(7) Configure **Scopes**

- **Recommended:** `openid`, `email`, `profile`
- Sensitive/restricted scopes may require review before going live.

(8) Add **Test users** (emails allowed to log in in test mode).

(9) **Save**.

> Note: Using only the basic scopes (`openid email profile`) often allows operation (publishing) **without review**.

#### 3) Create OAuth Client (Web Application)

(1) Sidebar: **APIs & Services → Credentials**.

(2) Top: **+ Create Credentials → OAuth client ID**.

(3) Application type: `Web application`.

(4) Enter a distinguishable **Name** (e.g., `SESLP – Front`).

(5) Add **Authorized redirect URIs**

- `https://{domain}/?social_login=google`

(6) Click **Create**, then copy the displayed **Client ID / Client Secret**.

> (Optional) Authorized JavaScript origins are usually unnecessary for this plugin using code grant.

#### 4) WordPress (Plugin) Setup

(1) WP Admin → **SESLP Settings → Google** tab.

(2) Paste **Client ID / Client Secret** → **Save**.

(3) Test with the **Google login button** on the site frontend.

#### 5) Switch from Test to Production

(1) Check **OAuth consent screen → Publishing status**.

(2) To switch from test to production:

- Verify app info (logo/app domain/policies/terms) is accurate.
- Remove unnecessary scopes, keep only needed scopes.
- Submit review request if using sensitive scopes.

(3) After switching to production, all Google accounts can log in.

#### 6) Common Errors & Solutions

(1) **redirect_uri_mismatch**

→ Occurs if the Redirect URI registered in the console and the actual request URI differ even slightly (including protocol, subdomain, slash, query). Fix to match exactly.

(2) **access_denied / disallowed_useragent**

→ Browser/in-app environment restrictions. Retry in a regular browser.

(3) **invalid_client / unauthorized_client**

→ Client ID/Secret typo or app status (deleted/disabled). Reissue/recheck credentials.

(4) **Email is empty**

→ Check if `email` scope is included, consent screen exposure, and account email visibility/security settings. Clearly explain email permission usage in the consent screen.

> **Check logs:**
>
> - `wp-content/seslp-logs/seslp-debug.log` (plugin debug ON)
> - `wp-content/debug.log` (WP_DEBUG, WP_DEBUG_LOG = true)

#### 7) Summary Checklist

- [ ] OAuth consent screen: set app info/domain/policies/terms/scopes/test users
- [ ] Credentials: create **Web Application** client
- [ ] Register Redirect URI: `https://{domain}/?social_login=google`
- [ ] SESLP: save Client ID/Secret and test login
- [ ] Change publishing status when going live (submit review if needed)

</details>

---

<details>
  <summary><strong>Facebook</strong></summary>

> - **Redirect URI:** `https://{domain}/?social_login=facebook`
> - **Requested Permissions (Recommended):** `public_profile`, `email`
> - **Facebook does not use** `openid`.

---

#### 1) Create App and Add Product

(1) Go to **Meta for Developers** → Log in

(2) Click **Create App** → Select a general type (e.g., Consumer) → Create app

(3) In the left sidebar, add **Facebook Login** from **Products**

(4) Go to **Settings** → Check the following items:

- **Client OAuth Login:** ON
- **Web OAuth Login:** ON
- **Valid OAuth Redirect URIs:**
- Add `https://{domain}/?social_login=facebook`
- (Optional) **Enforce HTTPS:** Recommended by default

#### 2) Basic App Settings (App Settings → Basic)

(1) **App Domains:** `example.com` (the domain of the app’s policy/terms/homepage URL)

(2) **Privacy Policy URL:** Publicly accessible policy page

(3) **Terms of Service URL:** Publicly accessible terms page

(4) **User Data Deletion:** Provide a guideline URL or a data deletion endpoint

(5) **Category / App Icon:** Set appropriately, then **Save**

#### 3) Scopes (Permissions) and App Review

(1) The basic permissions required for standard login are **`public_profile`**; the optional email is **`email`**

(2) In most cases, **`email` can be used without review**, but there may be exceptions depending on region/account

(3) **Advanced permissions** such as for pages/ads require **App Review** and **Business Verification**

#### 4) Switch Mode (Development → Live)

- At the top or in the app settings area, switch **App Mode: Development → Live**

#### 5) Checklist before switching to Live

- [ ] Prepare Privacy Policy / Terms / Data Deletion URL
- [ ] Enter Valid OAuth Redirect URIs accurately
- [ ] Remove unnecessary permissions, request only required ones
- [ ] (If needed) Complete App Review/Business Verification

#### 6) WordPress Settings (SESLP)

(1) WP Admin → **SESLP Settings → Facebook**

(2) Enter **App ID / App Secret** → Save

(3) Test with the **Facebook login button** on the frontend

#### 7) Troubleshooting

(1) **Can't Load URL / redirect_uri error**

→ Make sure the **exact same URI** is registered in **Valid OAuth Redirect URIs** (including protocol, subdomain, slash, query string)

(2) **email null**

→ The user has not registered an email with Facebook or it is private. Prepare **ID-based account linking logic**, and clearly explain the email permission usage in the consent screen

(3) **Permission-related errors**

→ If the requested scope exceeds the basic range, **App Review/Business Verification** is required

(4) **Cannot switch to Live**

→ If the policy/terms/data deletion guideline URL is **missing or not public**. You must provide a public URL

</details>

---

<details>
  <summary><strong>LinkedIn</strong></summary>

> - **Redirect URI:** `https://{domain}/?social_login=linkedin`
> - **Required Setting:** Enable OpenID Connect (OIDC)
> - **Recommended Scopes:** `openid`, `profile`, `email`
> - LinkedIn is **phasing out** legacy scopes (`r_liteprofile`, `r_emailaddress`).
> - New apps **must use OIDC standard scopes**.

---

#### 1) Create an Application

(1) Go to **LinkedIn Developers Console**

→ [https://www.linkedin.com/developers/apps](https://www.linkedin.com/developers/apps)

(2) Log in with LinkedIn account

(3) Click **Create app**

(4) Fill in required fields:

- **App name:** e.g., `MySite LinkedIn Login`
- **LinkedIn Page:** Select or “None”
- **App logo:** 100×100+ PNG/JPG
- **Privacy Policy URL / Business Email:** Valid and public

(5) Click **Create app**

> **Development Mode** by default → immediate testing of `openid`, `profile`, `email` login **without publishing**

#### 2) Enable OpenID Connect (OIDC)

(1) Go to **Products** tab

(2) Find **Sign In with LinkedIn using OpenID Connect**

(3) Click **Add product** → Approved instantly

(4) OIDC settings appear in **Auth** tab

> **OIDC Scopes Required**
>
> - `openid` → ID token
> - `profile` → Name, photo, headline
> - `email` → Email address

#### 3) OAuth 2.0 Settings (Auth Tab)

(1) Navigate to **Auth → OAuth 2.0 settings**

(2) Add to **Redirect URLs**:

→ `https://{domain}/?social_login=linkedin`

(3) **Exact match required** (protocol, subdomain, slash, query)

(4) Register multiple if needed:

- Local: `https://localhost:3000/?social_login=linkedin`
- Staging: `https://staging.example.com/?social_login=linkedin`
- Production: `https://example.com/?social_login=linkedin`

(5) Click **Save**

#### 4) Get Client ID / Client Secret

(1) In **Auth** tab, find:

- **Client ID**
- **Client Secret**

(2) WordPress Admin → **SESLP Settings → LinkedIn**

(3) Paste both → **Save**

(4) Test with **LinkedIn login button** on frontend

> **Security:**
>
> - Never expose Client Secret
> - Use **Regenerate secret** if compromised

#### 5) Scopes Explained

| Scope     | Description                    | Note         |
| --------- | ------------------------------ | ------------ |
| `openid`  | Returns OIDC standard ID token | **Required** |
| `profile` | Name, photo, headline, etc.    | **Required** |
| `email`   | Email address                  | **Required** |

> **Legacy scopes (`r_liteprofile`, `r_emailaddress`)**
>
> - **Deprecated after 2024**
> - **Not available for new apps**

#### 6) Troubleshooting

(1) **redirect_uri_mismatch**

→ URIs differ even slightly → ensure **100% match**

(2) **invalid_client**

→ Wrong ID/Secret or app inactive → recheck or regenerate

(3) **email NULL**

→ User denied or `email` scope missing → explain usage in consent screen

(4) **insufficient_scope**

→ Requested scope not approved → verify OIDC enabled

(5) **OIDC not enabled**

→ Missing **Sign In with LinkedIn using OpenID Connect** in Products

> **Logs:**
>
> - `/wp-content/seslp-logs/seslp-debug.log`
> - `/wp-content/debug.log`

#### 7) Summary Checklist

- [ ] App created
- [ ] **OpenID Connect** product added
- [ ] Redirect URI registered exactly
- [ ] Client ID/Secret saved in SESLP
- [ ] Scopes: `openid profile email` (no legacy scopes)
- [ ] Tested on HTTPS frontend

---

> **Note:**
>
> - SESLP fully supports **OIDC flow**.
> - Legacy OAuth 2.0 is **no longer supported**.
> - Always use **OpenID Connect** for new integrations.

</details>

---

<details>
  <summary><strong>Naver</strong></summary>

> - **Redirect URI:** `https://{domain}/?social_login=naver`
> - **Recommended Scopes:** Basic Profile (`name`), Email (`email`)
> - Naver uses **Naver Login (네아로)** API, **HTTPS required**

---

#### 1) Application Registration

(1) Go to **Naver Developer Center**

→ [https://developers.naver.com/apps/](https://developers.naver.com/apps/)

(2) Log in with Naver account

(3) Click **Application → Register Application**

(4) Fill in required fields:

- **Application Name:** e.g., `MySite Naver Login`
- **API Usage:** Select `Naver Login (네아로)`
- **Add Environment → Web**
- **Service URL:** `https://example.com`
- **Callback URL:** `https://example.com/?social_login=naver`

(5) Agree to terms → **Register**

> **Note:**
>
> - **HTTPS mandatory** → HTTP not allowed
> - **Subdomains must be registered separately**

#### 2) Get Client ID / Client Secret

(1) Go to **My Applications**

(2) Click the app → copy **Client ID** and **Client Secret**

#### 3) WordPress (Plugin) Settings

(1) WP Admin → **SESLP Settings → Naver**

(2) Paste **Client ID / Client Secret**

(3) Ensure **Redirect URI** matches exactly: `https://{domain}/?social_login=naver`

(4) **Save** → Test with **Naver login button** on frontend

#### 4) Permissions and Data Provision

| Data             | Scope    | Note                |
| ---------------- | -------- | ------------------- |
| Name             | `name`   | Default             |
| Email            | `email`  | Default             |
| Gender, Birthday | Separate | **Review required** |

> - Users can **agree/decline** on consent screen
> - If email declined → `email = null` → use **ID-based linking**
> - Sensitive data requires **Naver app review**

#### 5) Troubleshooting

(1) **Redirect URI mismatch**

→ Even slight difference → ensure **100% match**

(2) **HTTP error**

→ Must use **HTTPS**

(3) **Subdomain error**

→ Register each subdomain separately

(4) **email NULL**

→ User declined or private → prepare ID-based logic

(5) **Review needed**

→ Basic login: **no review**  
→ Additional data: **review required**

> **Logs:**
>
> - `/wp-content/seslp-logs/seslp-debug.log`
> - `/wp-content/debug.log`

#### 6) Summary Checklist

- [ ] App registered in Naver Developer Center
- [ ] **Callback URL** registered exactly
- [ ] **HTTPS** used
- [ ] Subdomains registered separately (if needed)
- [ ] Client ID/Secret saved in SESLP
- [ ] Tested email agree/decline behavior
- [ ] Frontend login test completed

---

> - **Note:**
>
> - SESLP fully supports **Naver Login (네아로)**.
> - Basic login (`name`, `email`) is **available without review**.

</details>

---

<details>
  <summary><strong>Kakao</strong></summary>

> - **Redirect URI:** `https://{domain}/?social_login=kakao`
> - **Recommended Scopes:** `profile_nickname`, `profile_image`, `account_email`
> - `account_email` available **only after identity or business verification**
> - **HTTPS required**, **Client Secret activation mandatory**

---

#### 1) Create Application

(1) Go to **Kakao Developers**

→ [https://developers.kakao.com/](https://developers.kakao.com/)

(2) Log in → **My Applications → Add New App**

(3) Enter:

- App Name, Company Name
- Category
- Agree to Operation Policy

(4) **Save**

#### 2) Enable Kakao Login

(1) **Product Settings > Kakao Login**

(2) Toggle **Enable Kakao Login** → **ON**

(3) **Register Redirect URI**

- `https://{domain}/?social_login=kakao`
- **Save**

(4) Domain must match **Platform site domain**

#### 3) Consent Items (Scopes)

(1) **Consent Items**

(2) Add and configure:

| Scope              | Description   | Consent Type      | Note                      |
| ------------------ | ------------- | ----------------- | ------------------------- |
| `profile_nickname` | Nickname      | Required/Optional | Basic                     |
| `profile_image`    | Profile Image | Required/Optional | Basic                     |
| `account_email`    | Email         | **Optional**      | **Verification required** |

(3) Clearly state **purpose** for each

(4) **Save**

> Sensitive scopes require **verification**

#### 4) Register Web Platform

(1) **App Settings > Platform**

(2) **Register Web Platform**

(3) Site Domain: `https://{domain}`

(4) **Save** → Must match Redirect URI domain

#### 5) Security – Generate & Activate Client Secret

(1) **Product Settings > Security**

(2) **Use Client Secret** → **ON**

(3) **Generate Secret** → Copy value

(4) **Activation Status** → **Active**

(5) **Save**

> Must **activate** after generation

#### 6) Get REST API Key (Client ID)

(1) **App Keys**

(2) Copy **REST API Key** → Use as **Client ID**

#### 7) WordPress Settings

(1) WP Admin → **SESLP Settings → Kakao**

(2) **Client ID** = REST API Key  
 **Client Secret** = Generated Secret

(3) **Save**

(4) Test with **Kakao Login Button**

#### 8) Troubleshooting

(1) **redirect_uri_mismatch** → 100% match required

(2) **invalid_client** → Secret not activated or typo

(3) **email empty** → User declined or unverified

(4) **Domain mismatch** → Platform vs Redirect URI

(5) **HTTP forbidden** → **HTTPS only**

> **Logs:**
>
> `/wp-content/seslp-logs/seslp-debug.log`  
> `/wp-content/debug.log`

#### 9) Summary Checklist

- [ ] Kakao Login enabled
- [ ] Redirect URI registered
- [ ] Web platform domain registered
- [ ] Consent items configured
- [ ] Client Secret generated + activated
- [ ] REST API Key / Secret in SESLP
- [ ] Tested on HTTPS frontend

</details>

---

<details>
  <summary><strong>LINE</strong></summary>

> - **Redirect URI:** `https://{domain}/?social_login=line`
> - **Required:** Enable OpenID Connect, **Apply & get approved for Email permission**
> - **Recommended Scopes:** `openid`, `profile`, `email`
> - **HTTPS mandatory**, **Email permission requires approval**

---

#### 1) Create Provider and Channel

(1) Access **LINE Developers Console**

→ [https://developers.line.biz/console/](https://developers.line.biz/console/)

(2) Log in with **LINE Business Account** (personal account not allowed)

(3) Click **Create a new provider** → Enter name → **Create**

(4) Under the provider → **Channels** tab

(5) Select **Create a LINE Login channel**

(6) Configure:

- **Channel type:** `LINE Login`
- **Provider:** Select created provider
- **Region:** Target country (e.g., `South Korea`, `Japan`)
- **Name / description / icon:** Shown on consent screen

(7) Agree to terms → **Create**

#### 2) Enable OpenID Connect & Apply for Email Permission

(1) Go to **OpenID Connect** in left menu

(2) Click **Apply** next to **Email address permission**

(3) Fill out application:

- **Privacy Policy URL** (must be publicly accessible)
- Upload **screenshot of Privacy Policy**
- Submit

(4) **`email` scope works only after approval**  
 → Approval usually takes 1–3 business days

#### 3) Register Callback URL & Publish Channel

(1) Go to **LINE Login** in left menu

(2) Enter **Callback URL**:

→ `https://{domain}/?social_login=line`

(3) **Exact match required**:

- Protocol: `https://` (**HTTP not allowed**)
- Domain, path, query string must **100% match**

(4) Click **Save**

(5) Change channel status to **Published**

- **Development mode: test only**
- **Published: live service**

#### 4) Get Channel ID / Secret

(1) Channel top or **Basic settings**

(2) **Channel ID** → SESLP **Client ID**  
 **Channel Secret** → SESLP **Client Secret**

#### 5) WordPress Settings

(1) WP Admin → **SESLP Settings → LINE**

(2) **Client ID** ← Channel ID  
 **Client Secret** ← Channel Secret

(3) **Save**

(4) Test with **LINE login button** on frontend

#### 6) Troubleshooting

(1) **redirect_uri_mismatch** → Even slight difference causes error → **100% match**

(2) **invalid_client** → Secret typo or **not Published**

(3) **email NULL** → **Email permission not approved** or user declined

(4) **HTTP not allowed** → **HTTPS required** (localhost HTTPS OK)

(5) **Development mode limit** → Only test accounts can log in

> **Logs:**
>
> - `/wp-content/seslp-logs/seslp-debug.log`
> - `/wp-content/debug.log`

#### 7) Summary Checklist

- [ ] Created **Provider + LINE Login channel** with Business Account
- [ ] **Email permission applied and approved**
- [ ] **Callback URL** registered exactly
- [ ] **HTTPS used**, **Published status**
- [ ] Channel ID/Secret → SESLP saved
- [ ] Frontend login test completed

> **Note:** SESLP fully supports
>
> - **LINE Login v2.1 + OpenID Connect**.
> - **Email collection requires pre-approval**.

</details>
