> 本文件說明如何在 **Simple Easy Social Login (SESLP)** 外掛中設定各社群登入提供者（Google、Facebook、LinkedIn、Naver、Kakao、LINE）。  
> 所有登入皆基於 **OAuth 2.0 / OpenID Connect (OIDC)**。  
> 您需要在各提供者的開發者主控台建立應用程式（Client），並在 SESLP 中輸入 **Client ID / Client Secret**。

---

## 🔧 通用設定指南

#### 1) **Redirect URI 規則：**

`https://{您的網域}/?social_login={provider}`

範例：

- Google → `https://example.com/?social_login=google`
- Facebook → `https://example.com/?social_login=facebook`
- LinkedIn → `https://example.com/?social_login=linkedin`
- Naver → `https://example.com/?social_login=naver`
- Kakao → `https://example.com/?social_login=kakao`
- LINE → `https://example.com/?social_login=line`

#### 2) **必須使用 HTTPS**

多數提供者要求 HTTPS，並拒絕 `http://` 轉址。

#### 3) **需完全相符**

主控台中登錄的 Redirect URI 必須與 SESLP 實際傳送的 URI **100% 完全一致**  
 （包含通訊協定、子網域、路徑、結尾斜線與查詢字串）。

#### 4) **Email 可能不可用**

部分提供者允許使用者拒絕分享 Email。SESLP 可回退使用提供者穩定的使用者 ID 來關聯帳號。

#### 5) **Log 檔案位置**

- `/wp-content/SESLP-debug.log`
- `/wp-content/debug.log`（`WP_DEBUG_LOG = true`）

## 🐞 偵錯日誌與問題排查

SESLP 提供專用的偵錯日誌檔案，協助您診斷 OAuth 與社群登入相關問題。

<details>
  <summary><strong>如何閱讀 SESLP 偵錯日誌</strong></summary>

#### 日誌檔案位置

- `/wp-content/SESLP-debug.log`（SESLP 偵錯日誌）
- `/wp-content/debug.log`（`WP_DEBUG_LOG = true`）

#### 日誌格式

```
[YYYY-MM-DD HH:MM:SS Z] [LEVEL] Message {"key":"value",...}
```

- `Z`：UTC 或 WordPress 本地時間（例如 KST）— 可在 SESLP 設定中選擇
- 隱私說明：Email / Token / Secret 會自動進行遮罩處理 （範例：`r********@g****.com`）

#### OAuth 流程日誌（常見）

**1) OAuth 啟動**

```
[DEBUG] State created {"provider":"google","state":"906****23","ttl":"10min"}
```

說明：已建立用於 CSRF 防護的 state token。 `ttl` 的有效期限為 **10 分鐘**。

**2) 回呼觸發**

```
[DEBUG] Auth route triggered {"provider":"google","has_code":1}
```

說明：已進入回呼流程。 `has_code:1` → 已接收到 OAuth 的 `code`。

**3) State 驗證**

成功：

```
[DEBUG] State validated {"provider":"google","state":"906****23"}
```

失敗：

```
[WARNING] State validation failed: not found/expired {"provider":"google","state":"906****23"}
```

**4) Token 交換**

```
[DEBUG] Token response (google) {"has_access_token":1}
```

說明：成功取得存取權杖（Access Token）。

失敗：

```
[ERROR] Token request failed (google) {"error":"..."}
```

**5) 使用者資訊請求（userinfo）**

```
[ERROR] Userinfo request failed (google)
[WARNING] Invalid userinfo (google)
```

**6) 使用者關聯（Linker）**

```
[DEBUG] Linker: signing in user {"user_id":45,"provider":"google","created":0}
[INFO]  Login success (google) {"user_id":45,"email":"r********@g****.com"}
```

**7) 重新導向**

```
[DEBUG] Redirect decision {"mode":"profile","user_id":45,"url":"https://example.com/wp-admin/profile.php"}
```

#### 快速參考表

| 日誌訊息（簡述）        | 可能原因                                       | 處理方式                                    |
| ----------------------- | ---------------------------------------------- | ------------------------------------------- |
| State validation failed | 逾時、切換分頁、重複請求                       | 立即重試，使用無痕／私人瀏覽模式            |
| Token request failed    | Client ID / Secret / Redirect 錯誤、請求被阻擋 | 檢查開發者主控台、防火牆、伺服器時間        |
| Userinfo invalid        | 缺少 Scope 或 Email 為私人                     | 加入 `email, profile` Scope，取得使用者同意 |
| User create failed      | 帳號衝突或 WordPress 限制                      | 檢查既有使用者、Multisite 規則              |
| Redirect missing        | 程式碼中過早 return                            | 確保 Redirect 類別在回呼後執行              |

#### 回報錯誤時建議提供的資訊

- 相關日誌內容（已遮罩）
- 使用的登入提供者（Google / Naver 等）
- 重新導向模式／自訂 URL
- 偵錯日誌啟用狀態
- WordPress 環境（單站台、多站台、快取外掛）

</details>

---

## 🌍 各提供者指南

> 展開以下各提供者區塊查看詳細說明。  
> Google 部分已完整撰寫，可作為範例。

---

<details open>
  <summary><strong>Google</strong></summary>

> - **建議 Scopes：** `openid email profile`
> - **Redirect URI：** `https://{domain}/?social_login=google`

---

#### 1) 準備事項

(1) **必須使用 HTTPS**。

(2) Redirect URI 必須與控制台登錄的值 **100% 完全相符**。

(3) 測試模式下僅 **測試使用者** 可登入。

(4) 若使用隱私權或服務條款連結，請登錄 **Authorized domains** 並驗證網域。

#### 2) 專案 / 同意畫面設定

(1) 進入 **Google Cloud Console**  
[https://console.cloud.google.com/apis/credentials](https://console.cloud.google.com/apis/credentials)

(2) 在上方選擇專案 → 如有需要可 **建立新專案**。

(3) 左側選單前往 **APIs & Services → OAuth consent screen（OAuth 同意畫面）**。

(4) 選擇 **使用者類型（User Type）**：一般為 **External（外部）**。

(5) 填寫 **應用程式資訊（App Information）**：應用名稱、支援電子郵件、（可選）Logo。

(6) **App domain（應用網域）** 區塊

- 輸入網站首頁 URL、隱私權政策 URL、服務條款 URL
- 將 **根網域（如 example.com）** 加入 **Authorized domains（授權網域）** → **儲存（Save）**
- 若需要，透過 Search Console 完成 **網域擁有權驗證**

(7) 設定 **Scopes（範圍）**

- **建議：** `openid`, `email`, `profile`
- 若使用敏感 / 受限範圍，正式上線前可能需要審查

(8) 新增 **測試使用者（Test users）**（測試模式下允許登入的 Email）

(9) **儲存（Save）**

> 注意：僅使用基本範圍（`openid email profile`）時，通常可 **免審查直接發布（publishing）**。

#### 3) 建立 OAuth 用戶端（Web 應用）

(1) **APIs & Services → Credentials**。

(2) 點擊 **+ Create Credentials → OAuth client ID**。

(3) 類型：`Web application`。

(4) 名稱：`SESLP – Front`。

(5) **Authorized redirect URIs：**

- `https://{domain}/?social_login=google`

(6) 複製 **Client ID / Secret**。

> （選用）本外掛採用授權碼（code grant）流程，一般**不需要**設定 `Authorized JavaScript origins`。

#### 4) WordPress 外掛設定

(1) WP 後台 → **SESLP Settings → Google**。

(2) 貼上 **Client ID / Secret** → 儲存。

(3) 前台測試 Google 登入。

#### 5) 從測試模式切換到正式模式

(1) 前往 **OAuth 同意畫面 → Publishing status（發布狀態）** 檢查目前模式。

(2) 要將應用從測試切換到正式模式，請完成：

- 確認應用資訊（Logo / 網域 / 隱私權政策 / 服務條款）正確無誤。
- 移除所有不必要的 Scopes，僅保留實際需要的範圍。
- 若使用敏感範圍（Sensitive scopes），需提交審查申請。

(3) 完成切換後，所有 Google 帳戶皆可登入（不再侷限於測試使用者）。

---

#### 6) 常見錯誤與解決方式

(1) **redirect_uri_mismatch**

→ 主控台註冊的 Redirect URI 與實際請求 URI 只要有一點點差異（包含協定、子網域、斜線、查詢字串）就會報錯。  
請確保 **100% 完全一致**。

(2) **access_denied / disallowed_useragent**

→ 使用者端瀏覽器或 App 環境限制造成。  
請在一般瀏覽器中重新嘗試。

(3) **invalid_client / unauthorized_client**

→ Client ID / Secret 輸入錯誤、過期、或應用被停用。  
請重新檢查或重發憑證。

(4) **Email 為空**

→ 請確認是否有加入 `email` scope、同意畫面是否顯示 Email 權限，  
以及帳戶的 Email 是否設為隱私／受保護。  
在同意畫面中需明確說明 Email 權限的用途。

> **查看日誌：**
>
> - `wp-content/SESLP-debug.log`（啟用外掛 Debug 時）
> - `wp-content/debug.log`（WP_DEBUG、WP_DEBUG_LOG = true 時）

---

#### 7) 最終檢查清單

- [ ] OAuth 同意畫面：設定應用資訊／網域／政策／條款／Scopes／測試使用者
- [ ] Credentials：建立 **Web Application** 用戶端
- [ ] 已註冊 Redirect URI：`https://{domain}/?social_login=google`
- [ ] SESLP：已儲存 Client ID / Secret 並測試登入
- [ ] 上線前切換發布狀態（如需要，提交審查）

</details>

---

<details>
  <summary><strong>Facebook</strong></summary>

> - **Redirect URI:** `https://{domain}/?social_login=facebook`
> - **建議權限:** `public_profile`, `email`
> - Facebook 不使用 `openid`。

---

#### 1) 建立應用並新增產品

(1) 前往 **Meta for Developers** → 登入
[https://developers.facebook.com/](https://developers.facebook.com/)

(2) 點擊 **Create App** → 選擇一般類型（例如 Consumer）→ 建立

(3) 左側選單 → **Products → Facebook Login**

(4) 進入 **Settings** → 確認以下項目：

- **Client OAuth Login:** 開啟
- **Web OAuth Login:** 開啟
- **Valid OAuth Redirect URIs:**
- 新增 `https://{domain}/?social_login=facebook`
- （選用）**Enforce HTTPS:** 建議開啟

#### 2) 基本設定 (App Settings → Basic)

(1) **App Domains:** `example.com`

(2) **Privacy Policy URL:** 可公開存取的頁面

(3) **Terms of Service URL:** 可公開存取的頁面

(4) **User Data Deletion:** 提供刪除資料的指引或端點

(5) **分類 / 圖示:** 設定後儲存

#### 3) 權限與審查

(1) 基本權限：**`public_profile`**，選用：**`email`**

(2) 一般 **`email` 可直接使用**，少數需審查

(3) 進階權限需 **App Review** 與 **Business Verification**

#### 4) 模式切換（開發 → 正式）

- 將模式從 **Development → Live**

#### 5) 確認：

- [ ] 政策／條款／刪除 URL 可公開
- [ ] Redirect URI 正確
- [ ] 僅保留必要權限
- [ ] 完成審查與驗證

#### 6) WordPress 設定 (SESLP)

(1) WP 後台 → **SESLP 設定 → Facebook**

(2) 輸入 **App ID / Secret** → 儲存

(3) 前台測試 Facebook 登入按鈕

#### 6) 疑難排解

(1) **redirect_uri 錯誤** → 檢查是否與註冊 URI 完全一致（包含通訊協定／子網域／結尾斜線／查詢字串）

(2) **email 為空** → 使用者未提供或未公開 Email。請準備以提供者 ID 為基礎的帳號連結邏輯，並在同意畫面清楚說明 Email 權限用途

(3) **權限相關錯誤** → 請求超出基本範圍時，需通過 **App Review** 與 **Business Verification**

(4) **無法切換為 Live** → 隱私權／服務條款／資料刪除指引 URL 缺漏或未公開；請提供公開可存取的 URL 後再嘗試

</details>

---

<details>
  <summary><strong>LinkedIn</strong></summary>

> - **Redirect URI：** `https://{domain}/?social_login=linkedin`
> - **必要設定：** 啟用 OpenID Connect (OIDC)
> - **建議範圍：** `openid`, `profile`, `email`
> - LinkedIn 正在**逐步淘汰**舊版 scopes（`r_liteprofile`, `r_emailaddress`）。
> - 新建立的應用程式**必須使用 OIDC 標準 scopes**。

---

#### 1) 建立應用

(1) 前往 **LinkedIn Developers Console**

→ [https://www.linkedin.com/developers/apps](https://www.linkedin.com/developers/apps)

(2) 登入

(3) 點擊 **Create app**

(4) 填寫必填欄位：

- **應用程式名稱（App name）：** 例如 `MySite LinkedIn Login`
- **LinkedIn 專頁（LinkedIn Page）：** 選擇一個專頁或選擇「None」
- **應用程式圖示（App logo）：** 100×100 以上的 PNG/JPG 圖片
- **隱私權政策網址 / 商務電子郵件（Privacy Policy URL / Business Email）：** 必須為有效且可公開存取的網址與信箱

(5) 點擊 **Create app（建立應用程式）**

> 預設為 **開發模式（Development Mode）** → 可立即測試 `openid`、`profile`、`email` 登入，**無需先發布**

#### 2) 啟用 OpenID Connect (OIDC)

(1) 前往 **Products** 分頁

(2) 找到 **Sign In with LinkedIn using OpenID Connect**

(3) 點擊 **Add product（新增產品）** → 立即核准

(4) OIDC 設定會出現在 **Auth** 分頁中

> **必需的 OIDC Scopes**
>
> - `openid` → ID Token
> - `profile` → 姓名、頭像、標題
> - `email` → 電子郵件地址

---

#### 3) OAuth 2.0 設定（Auth 分頁）

(1) 前往 **Auth → OAuth 2.0 settings**

(2) 在 **Redirect URLs** 中新增：

→ `https://{domain}/?social_login=linkedin`

(3) **必須完全相符**（通訊協定、子網域、斜線、查詢字串）

(4) 若需要，可註冊多個：

- 本機：`https://localhost:3000/?social_login=linkedin`
- 測試站：`https://staging.example.com/?social_login=linkedin`
- 正式站：`https://example.com/?social_login=linkedin`

(5) 點擊 **Save（儲存）**

#### 4) 取得 Client ID / Client Secret

(1) 在 **Auth** 分頁中找到：

- **Client ID**
- **Client Secret**

(2) 前往 WordPress 後台 → **SESLP Settings → LinkedIn**

(3) 貼上兩者 → **儲存（Save）**

(4) 在前台使用 **LinkedIn 登入按鈕** 進行測試

> **安全性說明：**
>
> - 請勿洩露 Client Secret
> - 若密鑰疑似外洩，請使用 **Regenerate secret（重新產生密鑰）**

#### 5) 範圍

| 範圍      | 說明             | 備註     |
| --------- | ---------------- | -------- |
| `openid`  | ID 權杖          | **必要** |
| `profile` | 姓名、頭像、標題 | **必要** |
| `email`   | 電子郵件         | **必要** |

> **舊版 Scopes（`r_liteprofile`, `r_emailaddress`）**
>
> - **自 2024 年後已被淘汰（Deprecated）**
> - **新建立的應用程式無法再使用**

#### 6) 疑難排解

(1) **redirect_uri_mismatch**

→ 重新導向 URI 只要有一點差異就會出錯 → 必須 **100% 完全一致**

(2) **invalid_client**

→ Client ID / Secret 錯誤或應用未啟用 → 請重新檢查或重新產生密鑰

(3) **email NULL**

→ 使用者拒絕提供 Email 或缺少 `email` scope → 請在同意畫面解釋用途

(4) **insufficient_scope**

→ 要求的範圍未核准 → 請確認是否已啟用 OIDC

(5) **OIDC 未啟用**

→ 產品中缺少 **Sign In with LinkedIn using OpenID Connect**

> **日誌位置：**
>
> - `/wp-content/SESLP-debug.log`
> - `/wp-content/debug.log`

#### 7) 檢查清單

- [ ] 應用已建立
- [ ] 已新增 **OpenID Connect** 產品
- [ ] 已正確註冊 Redirect URI（完全一致）
- [ ] 已在 SESLP 輸入 Client ID/Secret
- [ ] Scopes：`openid profile email`（不含舊版 scopes）
- [ ] 已在 HTTPS 前端完成測試

---

> **注意：**
>
> - SESLP 完全支援 **OIDC 流程**。
> - 舊版 OAuth 2.0 **已不再支援**。
> - 新整合請務必使用 **OpenID Connect**。

</details>

---

<details>
  <summary><strong>Naver</strong></summary>

> - **Redirect URI：** `https://{domain}/?social_login=naver`
> - **建議範圍：** `name`, `email`
> - Naver 使用 **Naver Login (네아로)**，**必須 HTTPS**

---

#### 1) 應用程式註冊（Application Registration）

(1) 前往 **Naver Developer Center**  
→ https://developers.naver.com/apps/

(2) 使用 Naver 帳號登入

(3) 點擊 **Application → Register Application（註冊應用程式）**

(4) 填寫必填欄位：

- **應用程式名稱（Application Name）：** 例如 `MySite Naver Login`
- **API 使用用途（API Usage）：** 選擇 `Naver Login (네아로)`
- **新增環境（Add Environment）→ Web**
- **服務網址（Service URL）：** `https://example.com`
- **Callback URL：** `https://example.com/?social_login=naver`

(5) 勾選同意條款 → 點擊 **Register（註冊）**

> **注意：**
>
> - **必須使用 HTTPS** → 不允許使用 HTTP
> - **子網域必須分別個別註冊**

#### 2) 取得 Client ID / Client Secret

(1) 前往 **My Applications（我的應用程式）**

(2) 點擊該應用程式 → 複製 **Client ID** 與 **Client Secret**

#### 3) WordPress（外掛）設定

(1) WP 後台 → **SESLP Settings → Naver**

(2) 貼上 **Client ID / Client Secret**

(3) 確認 **Redirect URI** 與註冊值完全相同：`https://{domain}/?social_login=naver`

(4) 按下 **儲存（Save）** → 在前台使用 **Naver 登入按鈕** 測試

#### 4) 權限

| 資訊       | 範圍     | 備註       |
| ---------- | -------- | ---------- |
| 姓名       | `name`   | 預設       |
| 電子郵件   | `email`  | 預設       |
| 性別、生日 | 單獨申請 | **需審核** |

> - 使用者可以在同意畫面中選擇**同意 / 拒絕**
> - 若使用者拒絕提供 Email → `email = null` → 請使用**以 ID 為基礎的帳號連結方式**
> - 申請存取敏感資料時需要通過 **Naver 應用程式審查**

#### 5) 疑難排解（Troubleshooting）

(1) **Redirect URI 不相符（mismatch）**

→ 只要有一點點差異都會失敗 → 必須確保 **100% 完全一致**

(2) **HTTP 錯誤（HTTP error）**

→ 必須使用 **HTTPS**

(3) **子網域錯誤（Subdomain error）**

→ 每個子網域必須**分別註冊**

(4) **email 為 NULL**

→ 使用者拒絕提供或設定為私人 → 請預先準備以 ID 為基礎的邏輯

(5) **需要審查（Review needed）**

→ 基本登入：**不需要審查**  
→ 額外資料：**需要審查**

> **Logs：**
>
> - `/wp-content/SESLP-debug.log`
> - `/wp-content/debug.log`

#### 6) 總結檢查清單（Summary Checklist）

- [ ] 已在 Naver Developer Center 註冊應用程式
- [ ] 已**準確註冊 Callback URL**
- [ ] 已使用 **HTTPS**
- [ ] 如有需要，子網域已分別註冊
- [ ] 已在 SESLP 中儲存 Client ID / Secret
- [ ] 已測試使用者同意 / 拒絕 Email 的行為
- [ ] 已完成前台登入測試

---

> - **注意（Note）：**
>
> - SESLP 完全支援 **Naver Login（네아로）**。
> - 基本登入（`name`, `email`）在多數情況下**無需審查即可使用**。

</details>

---

<details>
  <summary><strong>Kakao</strong></summary>

> - **Redirect URI：** `https://{domain}/?social_login=kakao`
> - **建議範圍：** `profile_nickname`, `profile_image`, `account_email`
> - `account_email` 僅在 **實名認證或企業登記完成後** 可用
> - **必須 HTTPS**, **Client Secret 必須啟用**

---

#### 1) 建立應用

(1) 前往 **Kakao Developers**

→ [https://developers.kakao.com/](https://developers.kakao.com/)

(2) 登入 → **我的應用 → 新增應用**

(3) 輸入：

- 應用名稱、公司名稱
- 分類
- 同意營運政策

(4) **儲存**

#### 2) 啟用 Kakao 登入

(1) **產品設定 > Kakao 登入**

(2) **啟用 Kakao 登入** → **開啟**

(3) **註冊重新導向 URI**

- `https://{domain}/?social_login=kakao`
- **儲存**

(4) 網域必須與 **平台註冊網域完全一致**

#### 3) 同意項目（範圍）設定

(1) **同意項目**

(2) 新增並設定：

| 範圍               | 說明     | 同意類型  | 備註           |
| ------------------ | -------- | --------- | -------------- |
| `profile_nickname` | 暱稱     | 必選/選用 | 基礎           |
| `profile_image`    | 頭像     | 必選/選用 | 基礎           |
| `account_email`    | 電子郵件 | **選用**  | **需實名認證** |

(3) 每項明確填寫 **使用目的**

(4) **儲存**

> 敏感範圍需 **實名認證**

#### 4) 註冊 Web 平台

(1) **應用設定 > 平台**

(2) **註冊 Web 平台**

(3) 網站網域：`https://{domain}`

(4) **儲存** → 必須與重新導向 URI 網域一致

#### 5) 安全 – 產生並啟用 Client Secret

(1) **產品設定 > 安全**

(2) **使用 Client Secret** → **開啟**

(3) **產生 Secret** → 複製值

(4) **啟用狀態** → **使用中**

(5) **儲存**

> **產生後必須啟用**

#### 6) 取得 REST API 金鑰 (Client ID)

(1) **應用金鑰**

(2) 複製 **REST API 金鑰** → 作為 **Client ID**

#### 7) WordPress 設定

(1) WP 後台 → **SESLP 設定 → Kakao**

(2) **Client ID** = REST API 金鑰  
 **Client Secret** = 產生的 Secret

(3) **儲存**

(4) 使用 **Kakao 登入按鈕** 測試

#### 8) 疑難排解

(1) **redirect_uri_mismatch** → 必須 100% 相符

(2) **invalid_client** → Secret 未啟用或錯誤

(3) **email 為空** → 使用者拒絕或未認證

(4) **網域不一致** → 平台 vs URI

(5) **HTTP 禁用** → **僅 HTTPS**

> **日誌：**
>
> - `/wp-content/SESLP-debug.log`
> - `/wp-content/debug.log`

#### 9) 檢查清單

- [ ] Kakao 登入已啟用
- [ ] 重新導向 URI 已註冊
- [ ] Web 平台網域已註冊
- [ ] 同意項目已設定
- [ ] Client Secret 已產生 + 啟用
- [ ] REST API 金鑰 / Secret 已輸入 SESLP
- [ ] 在 HTTPS 前台測試完成

</details>

---

<details>
  <summary><strong>LINE</strong></summary>

> - **Redirect URI：** `https://{domain}/?social_login=line`
> - **必要：** 啟用 OpenID Connect，**申請並通過電子郵件權限審核**
> - **建議範圍：** `openid`, `profile`, `email`
> - **必須 HTTPS**，收集郵件需**提前審核**

---

#### 1) 建立 Provider 和頻道

(1) 前往 **LINE Developers Console**  
 → [https://developers.line.biz/console/](https://developers.line.biz/console/)

(2) 使用 **LINE 企業帳號**登入（個人帳號不可）

(3) 點擊 **建立新 Provider** → 輸入名稱 → **Create**

(4) 在 Provider 下 → **Channels** 分頁

(5) 選擇 **建立 LINE Login 頻道**

(6) 設定：

- **頻道類型：** `LINE Login`
- **Provider：** 選擇已建立
- **地區：** 目標國家（例如 `South Korea`, `Japan`）
- **名稱 / 描述 / 圖示：** 顯示於使用者同意畫面

(7) 同意條款 → **建立**

#### 2) 啟用 OpenID Connect 並申請郵件權限

(1) 選單 **OpenID Connect**

(2) 在 **Email address permission** 旁點擊 **Apply**

(3) 填寫申請：

- **隱私權政策 URL**（需公開可訪問）
- **隱私權政策截圖**
- 勾選同意 → **Submit**

(4) **`email` 範圍僅在審核通過後生效**  
 → 審核通常需 1–3 個工作天

#### 3) 註冊 Callback URL 並發佈頻道

(1) 選單 **LINE Login**

(2) 輸入 **Callback URL**：  
 → `https://{domain}/?social_login=line`

(3) **必須完全相符**：

- 通訊協定：`https://`（**HTTP 不可用**）
- 網域、路徑、查詢參數 **100% 一致**

(4) 點擊 **儲存**

(5) 將頻道狀態改為 **Published**

- **Development：** 僅測試
- **Published：** 正式上線

#### 4) 取得 Channel ID / Secret

(1) 頻道頁面頂部或 **Basic settings**

(2) **Channel ID** → SESLP **Client ID**  
 **Channel Secret** → SESLP **Client Secret**

#### 5) WordPress 設定

(1) WP 後台 → **SESLP 設定 → LINE**

(2) **Client ID** ← Channel ID  
 **Client Secret** ← Channel Secret

(3) **儲存**

(4) 使用前台 **LINE 登入按鈕** 進行實際測試

#### 6) 疑難排解

(1) **redirect_uri_mismatch** → 任何微小差異都會出錯 → **100% 一致**

(2) **invalid_client** → Secret 輸入錯誤或 **未發佈**

(3) **email 為空** → **郵件權限未通過審核** 或使用者拒絕

(4) **HTTP 禁用** → **僅支援 HTTPS**（本地 `https://localhost` 可）

(5) **Development 模式限制** → 僅測試帳號可登入

> **日誌：**
>
> - `/wp-content/SESLP-debug.log`
> - `/wp-content/debug.log`

#### 7) 檢查清單

- [ ] 使用企業帳號建立 **Provider + LINE Login 頻道**
- [ ] **郵件權限申請並通過審核**
- [ ] **Callback URL** 完全一致註冊
- [ ] **使用 HTTPS**，狀態為 **Published**
- [ ] Channel ID/Secret 已輸入 SESLP
- [ ] 前台完成實際登入測試

> **備註：** SESLP 完全支援
>
> - **LINE Login v2.1 + OpenID Connect**。
> - **收集郵件必須提前審核**。

</details>
