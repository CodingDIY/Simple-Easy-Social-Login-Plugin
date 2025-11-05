# Simple Easy Social Login (SESLP) — 社群登入指南（繁體中文）

> 本文件說明如何在 **Simple Easy Social Login (SESLP)** 外掛中  
> 設定各社群登入提供者（Google、Facebook、LinkedIn、Naver、Kakao、LINE）。  
> 所有登入皆基於 **OAuth 2.0 / OpenID Connect (OIDC)**。  
> 您需要在各提供者的開發者主控台建立應用程式（Client），並在 SESLP 中輸入 **Client ID / Client Secret**。

---

## 🔧 通用設定指南

- **Redirect URI 規則：**  
  `https://{您的網域}/?social_login={provider}`  
  範例：

  - Google → `https://example.com/?social_login=google`
  - Facebook → `https://example.com/?social_login=facebook`
  - LinkedIn → `https://example.com/?social_login=linkedin`
  - Naver → `https://example.com/?social_login=naver`
  - Kakao → `https://example.com/?social_login=kakao`
  - LINE → `https://example.com/?social_login=line`

- **必須使用 HTTPS**  
  多數提供者要求 HTTPS，並拒絕 `http://` 轉址。

- **需完全相符**  
  主控台中登錄的 Redirect URI 必須與 SESLP 實際傳送的 URI **100% 完全一致**  
  （包含通訊協定、子網域、路徑、結尾斜線與查詢字串）。

- **Email 可能不可用**  
  部分提供者允許使用者拒絕分享 Email。SESLP 可回退使用提供者穩定的使用者 ID 來關聯帳號。

- **Log 檔案位置**
  - `/wp-content/seslp-logs/seslp-debug.log`
  - `/wp-content/debug.log`（`WP_DEBUG_LOG = true`）

---

## 🌍 各提供者指南

> 展開以下各提供者區塊查看詳細說明。  
> Google 部分已完整撰寫，可作為範例。

---

<details open>
  <summary><strong>Google</strong></summary>

> **建議 Scopes：** `openid email profile`  
> **Redirect URI：** `https://{domain}/?social_login=google`

---

### 1) 準備事項

- **必須使用 HTTPS**。
- Redirect URI 必須與控制台登錄的值 **100% 完全相符**。
- 測試模式下僅 **測試使用者** 可登入。
- 若使用隱私權或服務條款連結，請登錄 **Authorized domains** 並驗證網域。

### 2) 專案與同意畫面設定

1. 進入 **Google Cloud Console**
   - <https://console.cloud.google.com/apis/credentials>
2. 建立或選擇專案。
3. 開啟 **APIs & Services → OAuth consent screen**。
4. 使用者類型：**External（外部）**。
5. 填寫應用程式資訊。
6. 設定 **App domain** 並儲存。
7. 設定 **Scopes：** `openid email profile`。
8. 新增 **測試使用者** → 儲存。

> 僅使用基本範圍時通常可 **免審查直接上線**。

### 3) 建立 OAuth 用戶端（Web 應用）

1. **APIs & Services → Credentials**。
2. 點擊 **+ Create Credentials → OAuth client ID**。
3. 類型：`Web application`。
4. 名稱：`SESLP – Front`。
5. **Authorized redirect URIs：**
   - `https://{domain}/?social_login=google`
6. 複製 **Client ID / Secret**。

### 4) WordPress 外掛設定

1. WP 後台 → **SESLP Settings → Google**。
2. 貼上 **Client ID / Secret** → 儲存。
3. 前台測試 Google 登入。

### 5) 切換至正式環境

1. 檢查發布狀態。
2. 刪除不必要的範圍。
3. 使用敏感範圍時需送審。

### 6) 常見錯誤

- **redirect_uri_mismatch** – URI 不相符。
- **access_denied** – 瀏覽器限制。
- **invalid_client** – 憑證錯誤。
- **Email 為空** – 檢查範圍與隱私設定。

</details>

---

<details>
  <summary><strong>Facebook (Meta)</strong></summary>

> **Redirect URI:** `https://{domain}/?social_login=facebook`  
> **建議權限:** `public_profile`, `email`  
> ※ Facebook 不使用 `openid`。

---

### 1) 建立應用並新增產品

1. 前往 **Meta for Developers** → 登入
2. 點擊 **Create App** → 選擇一般類型（例如 Consumer）→ 建立
3. 左側選單 → **Products → Facebook Login**
4. 進入 **Settings** → 確認以下項目：
   - **Client OAuth Login:** 開啟
   - **Web OAuth Login:** 開啟
   - **Valid OAuth Redirect URIs:**
     - 新增 `https://{domain}/?social_login=facebook`
   - （選用）**Enforce HTTPS:** 建議開啟

### 2) 基本設定

- **App Domains:** `example.com`
- **Privacy Policy URL:** 可公開存取的頁面
- **Terms of Service URL:** 可公開存取的頁面
- **User Data Deletion:** 提供刪除資料的指引或端點
- **分類 / 圖示:** 設定後儲存

### 3) 權限與審查

- 基本權限：**`public_profile`**，選用：**`email`**
- 一般 **`email` 可直接使用**，少數需審查
- 進階權限需 **App Review** 與 **Business Verification**

### 4) 模式切換（開發 → 正式）

- 將模式從 **Development → Live**
- 確認：
  - [ ] 政策／條款／刪除 URL 可公開
  - [ ] Redirect URI 正確
  - [ ] 僅保留必要權限
  - [ ] 完成審查與驗證

### 5) WordPress 設定 (SESLP)

1. WP 後台 → **SESLP 設定 → Facebook**
2. 輸入 **App ID / Secret** → 儲存
3. 前台測試 Facebook 登入按鈕

### 6) 疑難排解

- **redirect_uri 錯誤** → 檢查是否與註冊 URI 完全一致（包含通訊協定／子網域／結尾斜線／查詢字串）
- **email 為空** → 使用者未提供或未公開 Email。請準備以提供者 ID 為基礎的帳號連結邏輯，並在同意畫面清楚說明 Email 權限用途
- **權限相關錯誤** → 請求超出基本範圍時，需通過 **App Review** 與 **Business Verification**
- **無法切換為 Live** → 隱私權／服務條款／資料刪除指引 URL 缺漏或未公開；請提供公開可存取的 URL 後再嘗試

</details>

---

<details>
  <summary><strong>LinkedIn</strong></summary>

> **Redirect URI：** `https://{domain}/?social_login=linkedin`  
> **必要設定：** 啟用 OpenID Connect (OIDC)  
> **建議範圍：** `openid`, `profile`, `email`

---

### 1) 建立應用

1. 前往 **LinkedIn Developers Console**  
   → [連結](https://www.linkedin.com/developers/apps)
2. 登入
3. 點擊 **Create app**
4. 填寫必填：
   - 應用名稱、頁面、Logo、隱私權政策、郵件
5. 建立

> 開發模式 → 可立即測試

---

### 2) 啟用 OIDC

1. **Products** → 新增 **Sign In with LinkedIn using OpenID Connect**

---

### 3) OAuth 設定

1. **Auth → OAuth 2.0 settings**
2. 新增重新導向 URI：`https://{domain}/?social_login=linkedin`
3. 必須完全相符
4. 儲存

---

### 4) Client ID / Secret

1. 在 **Auth** 取得
2. SESLP → LinkedIn → 貼上 → 儲存
3. 前台測試

---

### 5) 範圍

| 範圍      | 說明             | 備註     |
| --------- | ---------------- | -------- |
| `openid`  | ID 權杖          | **必要** |
| `profile` | 姓名、頭像、標題 | **必要** |
| `email`   | 電子郵件         | **必要** |

> 舊範圍 **已停用**

---

### 6) 疑難排解

- **redirect_uri_mismatch** → URI 完全一致
- **invalid_client** → ID/Secret 錯誤
- **email 為空** → 範圍缺失或使用者拒絕

---

### 7) 檢查清單

- [ ] 應用建立
- [ ] OIDC 啟用
- [ ] 重新導向 URI 註冊
- [ ] ID/Secret 輸入
- [ ] HTTPS 測試

</details>

---

<details>
  <summary><strong>Naver</strong></summary>

> **Redirect URI：** `https://{domain}/?social_login=naver`  
> **建議範圍：** `name`, `email`  
> ※ Naver 使用 **네아로 (Naver Login)**，**必須 HTTPS**

---

### 1) 應用註冊

1. 前往 **Naver Developer Center**  
   → [連結](https://developers.naver.com/apps/)
2. 登入
3. **應用程式註冊**
4. 填寫：
   - 應用名稱、API: `Naver Login`
   - Web: 服務 URL、**Callback URL**
5. **註冊**

> HTTPS 必填，子網域需單獨註冊

---

### 2) Client ID / Secret

1. **我的應用** → 複製

---

### 3) WordPress 設定

1. WP 後台 → **SESLP → Naver**
2. 貼上 ID/Secret
3. 確認 URI 完全相符
4. **儲存** → 測試

---

### 4) 權限

| 資訊       | 範圍     | 備註       |
| ---------- | -------- | ---------- |
| 姓名       | `name`   | 預設       |
| 電子郵件   | `email`  | 預設       |
| 性別、生日 | 單獨申請 | **需審核** |

> 郵件拒絕 → `null`

---

### 5) 疑難排解

- **redirect_uri_mismatch** → 完全一致
- **HTTP 禁用** → 僅 HTTPS
- **子網域** → 單獨註冊

---

### 6) 檢查清單

- [ ] 應用註冊
- [ ] Callback URL 準確
- [ ] HTTPS
- [ ] ID/Secret 輸入
- [ ] 郵件同意測試

</details>

---

<details>
  <summary><strong>Kakao</strong></summary>

> **Redirect URI：** `https://{domain}/?social_login=kakao`  
> **建議範圍：** `profile_nickname`, `profile_image`, `account_email`  
> ※ `account_email` 僅在 **實名認證或企業登記完成後** 可用  
> ※ **必須 HTTPS**, **Client Secret 必須啟用**

---

### 1) 建立應用

1. 前往 **Kakao Developers**  
   → [https://developers.kakao.com/](https://developers.kakao.com/)
2. 登入 → **我的應用 → 新增應用**
3. 輸入：
   - 應用名稱、公司名稱
   - 分類
   - 同意營運政策
4. **儲存**

---

### 2) 啟用 Kakao 登入

1. **產品設定 > Kakao 登入**
2. **啟用 Kakao 登入** → **開啟**
3. **註冊重新導向 URI**
   - `https://{domain}/?social_login=kakao`
   - **儲存**
4. 網域必須與 **平台註冊網域完全一致**

---

### 3) 同意項目（範圍）設定

1. **同意項目**
2. 新增並設定：

| 範圍               | 說明     | 同意類型  | 備註           |
| ------------------ | -------- | --------- | -------------- |
| `profile_nickname` | 暱稱     | 必選/選用 | 基礎           |
| `profile_image`    | 頭像     | 必選/選用 | 基礎           |
| `account_email`    | 電子郵件 | **選用**  | **需實名認證** |

3. 每項明確填寫 **使用目的**
4. **儲存**

> 敏感範圍需 **實名認證**

---

### 4) 註冊 Web 平台

1. **應用設定 > 平台**
2. **註冊 Web 平台**
3. 網站網域：`https://{domain}`
4. **儲存** → 必須與重新導向 URI 網域一致

---

### 5) 安全 – 產生並啟用 Client Secret

1. **產品設定 > 安全**
2. **使用 Client Secret** → **開啟**
3. **產生 Secret** → 複製值
4. **啟用狀態** → **使用中**
5. **儲存**
   > **產生後必須啟用**

---

### 6) 取得 REST API 金鑰 (Client ID)

1. **應用金鑰**
2. 複製 **REST API 金鑰** → 作為 **Client ID**

---

### 7) WordPress 設定

1. WP 後台 → **SESLP 設定 → Kakao**
2. **Client ID** = REST API 金鑰  
   **Client Secret** = 產生的 Secret
3. **儲存**
4. 使用 **Kakao 登入按鈕** 測試

---

### 8) 疑難排解

- **redirect_uri_mismatch** → 必須 100% 相符
- **invalid_client** → Secret 未啟用或錯誤
- **email 為空** → 使用者拒絕或未認證
- **網域不一致** → 平台 vs URI
- **HTTP 禁用** → **僅 HTTPS**

> **日誌：**  
> `/wp-content/seslp-logs/seslp-debug.log`  
> `/wp-content/debug.log`

---

### 9) 檢查清單

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

> **Redirect URI：** `https://{domain}/?social_login=line`  
> **必要：** 啟用 OpenID Connect，**申請並通過電子郵件權限審核**  
> **建議範圍：** `openid`, `profile`, `email`  
> ※ **必須 HTTPS**，收集郵件需**提前審核**

---

### 1) 建立 Provider 和頻道

1. 前往 **LINE Developers Console**  
   → [https://developers.line.biz/console/](https://developers.line.biz/console/)
2. 使用 **LINE 企業帳號**登入（個人帳號不可）
3. 點擊 **建立新 Provider** → 輸入名稱 → **Create**
4. 在 Provider 下 → **Channels** 分頁
5. 選擇 **建立 LINE Login 頻道**
6. 設定：
   - **頻道類型：** `LINE Login`
   - **Provider：** 選擇已建立
   - **地區：** 目標國家（例如 `South Korea`, `Japan`）
   - **名稱 / 描述 / 圖示：** 顯示於使用者同意畫面
7. 同意條款 → **建立**

---

### 2) 啟用 OpenID Connect 並申請郵件權限

1. 選單 **OpenID Connect**
2. 在 **Email address permission** 旁點擊 **Apply**
3. 填寫申請：
   - **隱私權政策 URL**（需公開可訪問）
   - **隱私權政策截圖**
   - 勾選同意 → **Submit**
4. **`email` 範圍僅在審核通過後生效**  
   → 審核通常需 1–3 個工作天

---

### 3) 註冊 Callback URL 並發佈頻道

1. 選單 **LINE Login**
2. 輸入 **Callback URL**：  
   → `https://{domain}/?social_login=line`
3. **必須完全相符**：
   - 通訊協定：`https://`（**HTTP 不可用**）
   - 網域、路徑、查詢參數 **100% 一致**
4. 點擊 **儲存**
5. 將頻道狀態改為 **Published**
   - **Development：** 僅測試
   - **Published：** 正式上線

---

### 4) 取得 Channel ID / Secret

1. 頻道頁面頂部或 **Basic settings**
2. **Channel ID** → SESLP **Client ID**  
   **Channel Secret** → SESLP **Client Secret**

---

### 5) WordPress 設定

1. WP 後台 → **SESLP 設定 → LINE**
2. **Client ID** ← Channel ID  
   **Client Secret** ← Channel Secret
3. **儲存**
4. 使用前台 **LINE 登入按鈕** 進行實際測試

---

### 6) 疑難排解

- **redirect_uri_mismatch** → 任何微小差異都會出錯 → **100% 一致**
- **invalid_client** → Secret 輸入錯誤或 **未發佈**
- **email 為空** → **郵件權限未通過審核** 或使用者拒絕
- **HTTP 禁用** → **僅支援 HTTPS**（本地 `https://localhost` 可）
- **Development 模式限制** → 僅測試帳號可登入

> **日誌：**  
> `/wp-content/seslp-logs/seslp-debug.log`  
> `/wp-content/debug.log`

---

### 7) 檢查清單

- [ ] 使用企業帳號建立 **Provider + LINE Login 頻道**
- [ ] **郵件權限申請並通過審核**
- [ ] **Callback URL** 完全一致註冊
- [ ] **使用 HTTPS**，狀態為 **Published**
- [ ] Channel ID/Secret 已輸入 SESLP
- [ ] 前台完成實際登入測試

---

> **備註：** SESLP 完全支援 **LINE Login v2.1 + OpenID Connect**。  
> **收集郵件必須提前審核**。

</details>

---

## 📋 摘要

| 方案 | 提供者       | 必要 / 建議範圍                                      | Redirect URI 範例                         | 備註                   |
| ---- | ------------ | ---------------------------------------------------- | ----------------------------------------- | ---------------------- |
| 免費 | **Google**   | `openid email profile`                               | `https://{domain}/?social_login=google`   | 需要外部同意畫面       |
| 免費 | **Facebook** | `public_profile`, `email`                            | `https://{domain}/?social_login=facebook` | 不使用 `openid`        |
| 免費 | **LinkedIn** | `openid profile email`                               | `https://{domain}/?social_login=linkedin` | 已全面轉向 OIDC        |
| 付費 | **Naver**    | `email`, `name`                                      | `https://{domain}/?social_login=naver`    | 使用「Naver Login」API |
| 付費 | **Kakao**    | `profile_nickname`, `profile_image`, `account_email` | `https://{domain}/?social_login=kakao`    | 需啟用 Client Secret   |
| 付費 | **LINE**     | `openid profile email`                               | `https://{domain}/?social_login=line`     | 必須為 Published 狀態  |
