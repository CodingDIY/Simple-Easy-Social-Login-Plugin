
# Debug Log Guide

## 0) Basic Info
- **Log location**: `wp-content/SESLP-debug.log`
- **Format**:
  ```
  [YYYY-MM-DD HH:MM:SS Z] [LEVEL] Message {"key":"value",...}
  ```
  - `Z`: UTC or WP Local (e.g. KST) — selectable in Settings
- **Privacy**: Emails/tokens/secrets masked automatically  
  Example: `r********@g****.com`
- **Enable Logging**: Settings → Enable Debug Logging = ON

---

## 1) OAuth Start
```
[DEBUG] State created {"provider":"google","state":"906****23","ttl":"10min"}
```
Meaning: CSRF protection state token created. `ttl` valid 10 min.

---

## 2) Callback Triggered
```
[DEBUG] Auth route triggered {"provider":"google","has_code":1}
```
Meaning: Callback entered. `has_code:1` → OAuth `code` received.

---

## 3) State Validation
- Success:
```
[DEBUG] State validated {"provider":"google","state":"906****23"}
```
- Failure:
```
[WARNING] State validation failed: not found/expired {"provider":"google","state":"906****23"}
```

---

## 4) Token Exchange
```
[DEBUG] Token response (google) {"has_access_token":1}
```
Meaning: Token obtained.

Failure:
```
[ERROR] Token request failed (google) {"error":"..."}
```

---

## 5) Userinfo Request
```
[ERROR] Userinfo request failed (google)
[WARNING] Invalid userinfo (google)
```

---

## 6) User Linker
```
[DEBUG] Linker: signing in user {"user_id":45,"provider":"google","created":0}
[INFO]  Login success (google) {"user_id":45,"email":"r********@g****.com"}
```

---

## 7) Redirect
```
[DEBUG] Redirect decision {"mode":"profile","user_id":45,"url":"https://example.com/wp-admin/profile.php"}
```

---

## 8) Quick Reference Table

| Log Message (short) | Likely Cause | Action |
|---|---|---|
| State validation failed | Timeout, tab switch, duplicate request | Retry quickly, use private mode |
| Token request failed | Wrong client ID/secret/redirect, blocked request | Check dev console, firewall, server time |
| Userinfo invalid | Missing scope or email private | Add `email, profile` scope, user consent |
| User create failed | Account conflict or WP restriction | Check existing users, multisite rules |
| Redirect missing | Early return in code | Ensure Redirect class runs after callback |

---

## 9) Helpful Info to Include in Bug Reports
- Relevant log lines (masked)
- Provider used (Google/Naver/etc.)
- Redirect mode/custom URL
- Debug logging state
- WP environment (single site, multisite, cache plugins)
