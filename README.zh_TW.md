# Simple Easy Social Login – OAuth Login

Simple Easy Social Login 是一款輕量且使用者友善的 WordPress 外掛，可為您的網站新增快速、順暢的社群登入功能。

本外掛支援 **Google、Facebook、LinkedIn（免費）**，以及 **Naver、Kakao、Line（進階版）**，  
特別適合面向亞洲（韓國、日本、中國）的網站，同時也能良好支援歐洲與南美地區的使用者。

此外掛可與 WordPress 預設的登入與註冊頁面無縫整合，  
同時支援 WooCommerce 的登入與註冊表單。  
社群平台的個人頭像可自動同步為 WordPress 使用者的個人頭像。

此外，本外掛採用 **可擴充的 Provider 架構** 設計，  
在需要時，可透過獨立的 Add-on 外掛方式新增其他 OAuth Provider。

---

## ✨ 功能特色

- Google 登入（免費）
- Facebook 登入（免費）
- LinkedIn 登入（免費）
- Naver 登入（進階版）
- Kakao 登入（進階版）
- Line 登入（進階版）
- 使用者頭像自動同步
- 依電子郵件自動連結既有的 WordPress 使用者
- 支援登入／登出／註冊後的自訂重新導向 URL
- 簡潔直觀的管理後台，用於設定各 Provider
- 支援短代碼： [se_social_login]
- 自動顯示於 WordPress 登入與註冊表單
- 支援 WooCommerce 登入與註冊表單（選用）
- 輕量化架構，遵循 WordPress 編碼標準
- 不建立不必要的資料庫資料表
- 支援透過 Add-on 外掛擴充新的 OAuth Provider 的 Provider 系統

---

## 🌐 外部服務

本外掛會連接第三方外部服務，以提供基於 OAuth 的社群登入功能。

### 支援的 OAuth 提供商

本外掛會與以下官方 API 進行通訊：

- Google
- Facebook（Meta）
- LinkedIn
- Naver（進階版）
- Kakao（進階版）
- LINE（進階版）

### 傳送的資料

在 OAuth 登入過程中，可能會傳送以下資料：

- OAuth 授權碼（authorization code）
- 存取權杖（access token）
- 使用者基本資料（例如：ID、姓名、電子郵件、頭像）
- Redirect URI 以及用於 CSRF 防護的 state 參數

這些資料僅用於使用者身份驗證，以及在 WordPress 中進行帳號關聯。

### 資料傳送時機

僅當使用者點擊社群登入按鈕並開始 OAuth 流程時，才會傳送資料。

### 服務條款與隱私政策

各提供商皆有其各自的服務條款與隱私政策：

- Google：https://policies.google.com/privacy
- Facebook（Meta）：https://www.facebook.com/privacy/policy/
- LinkedIn：https://www.linkedin.com/legal/privacy-policy
- Naver：https://policy.naver.com/policy/privacy.html
- Kakao：https://www.kakao.com/policy/privacy
- LINE：https://line.me/en/terms/policy/

建議您在使用本外掛前，先行查閱相關政策。

---

## 🐞 偵錯日誌

SESLP 內建偵錯日誌系統，可協助診斷 OAuth 與社群登入相關問題。

您可在 WordPress 管理後台中查看詳細的日誌說明：
**SESLP → Guides → Debug Log & Troubleshooting**

日誌檔案產生位置：

- `/wp-content/SESLP-debug.log`
- `/wp-content/debug.log`（啟用 `WP_DEBUG_LOG` 時）

---

## 🚀 安裝方式

1. 將外掛上傳至 `/wp-content/plugins/simple-easy-social-login/` 目錄。
2. 於 WordPress 管理後台中，透過 **外掛 → 已安裝的外掛** 啟用外掛。
3. 前往 **設定 → Simple Easy Social Login**。
4. 輸入各社群登入 Provider 的 Client ID 與 Client Secret。
5. 儲存設定。
6. 確認前端頁面中的社群登入按鈕是否正常顯示。

---

## ❓ 常見問題

### 是否支援 WooCommerce？

是的。本外掛可與 WooCommerce 的登入與註冊表單整合使用。

### WooCommerce 登入可以正常使用，但重新導向行為不同，這是正常的嗎？

是的。當 WooCommerce 啟用時，使用者登入後通常會被重新導向至 **我的帳戶** 頁面。  
您可以在外掛設定中或透過可用的篩選器自訂重新導向 URL。

### 如果 WooCommerce 網站上的社群登入無法正常運作，應該檢查哪些項目？

請檢查以下事項：

- WooCommerce 是否已更新至最新的穩定版本
- 外掛設定中是否已啟用相應的社群登入提供者
- Client ID 與 Client Secret 是否填寫正確
- 重新導向 / 回呼 URL 是否已正確註冊至各提供者的開發者後台
- 自訂的登入或結帳模板是否移除了 WooCommerce 的預設 Hook
- 是否已啟用除錯記錄，並檢查 `/wp-content/SESLP-debug.log`

### 是否只能使用 Google 登入？

可以。每個 Provider 都可個別啟用或停用。

### 什麼時候需要進階版授權？

使用 **Naver、Kakao、Line** 登入時，需要進階版授權。  
Google、Facebook 與 LinkedIn 可免費使用。

### 是否提供短代碼？

是的。您可以使用以下短代碼，在任何位置插入社群登入按鈕： [se_social_login]

### 是否會自動匯入使用者頭像？

是的。對於 Google、Facebook 等部分 Provider，可自動取得並同步使用者的個人頭像作為 WordPress 使用者頭像。

---

## 🖼 螢幕截圖

1. 顯示於 WordPress 登入頁面的社群登入按鈕（列表配置）。
2. 登入畫面中僅顯示圖示的社群登入按鈕配置。
3. 登入後重新導向選項（控制台、個人資料、首頁或自訂 URL）。
4. 除錯記錄、介面配置選項、短代碼與解除安裝設定。
5. 內建設定指南，說明 OAuth 重新導向規則與常見需求。
6. Google OAuth 同意畫面與用戶端設定的逐步指南。
7. Google、Facebook 與 LinkedIn 登入憑證的管理設定畫面。
8. Naver、Kakao 與 LINE 登入提供者的管理設定畫面。
9. 所有支援提供者共用的重新導向 URI 規則。
10. 除錯記錄位置與疑難排解概覽。
11. 常見 OAuth 錯誤、建議的解決方式以及除錯記錄位置。

---

## 📝 更新紀錄（Changelog）

### 1.9.9

- 完成公開發佈所需的螢幕截圖與文件整理
- 新增涵蓋登入流程、設定、指南與疑難排解的完整螢幕截圖說明
- 文件的小幅整理與一致性改善

### 1.9.8

- 修正 `SESLP_Avatar::resolve_user()` 中的致命型別錯誤，確保回傳值為 `WP_User|null`
- 改善頭像回退處理：
  - 當社群個人資料頭像缺失或無效時，安全地使用 WordPress 預設頭像
  - 防止頭像圖片損壞（例如 LinkedIn 個人資料圖片問題）
- 與頭像顯示相關的輕微穩定性改善

### 1.9.7

- 在 README 中新增偵錯日誌說明區段
- 將詳細的偵錯日誌指南整合至管理後台說明（多語系）
- 統一日誌檔案路徑說明（`/wp-content/SESLP-debug.log`）
- 文件結構整理與一致性改善

### 1.9.6

- 改善設定頁面的使用體驗
- 新增 Secret 金鑰顯示／隱藏切換功能
- 修正與 WordPress 核心樣式的衝突問題
- 改善 Pro / Max 方案偵測邏輯

### 1.9.5

- 大規模重構
- 整合 Helpers 並改善 Provider 架構
- 整理設定介面
- 提升穩定性與可維護性

### 1.9.3

- 更新 Guides 的翻譯內容
- 於設定頁面新增短代碼顯示

### 1.9.2

- 整理內部結構
- 新增 Guides 載入器類別
- 重構範本結構
- 提升設定與 CSS 載入器的穩定性

### 1.9.1

- 新增管理員指南頁面
- 基於 Markdown 的多語系文件渲染（採用 Parsedown）
- 改善 UI 樣式

### 1.9.0

- 大規模重構準備階段
- 擴充 i18n 輔助工具
- 改善安全格式化與日誌結構

### 1.7.23

- 翻譯更新

### 1.7.22

- 改善除錯訊息，顯示先前登入的 Provider

### 1.7.21

- 偵測到相同電子郵件重複註冊時，於錯誤訊息中顯示 Provider 名稱
- 透過 JavaScript 在 10 秒後自動隱藏錯誤訊息

### 1.7.19

- 防止使用相同電子郵件建立重複帳號
- 改善 OAuth 流程：
  - `get_access_token()`
  - `get_user_info()`
  - `create_or_link_user()`

### 1.7.18

- 移除 Google Client ID / Secret 欄位的提示說明
- 優化程式碼結構
- 移除 Line 登入按鈕中的「(Email required)」文字

### 1.7.17

- 修正 Line 登入相關問題：
  - 防止重新登入時建立重複使用者
  - 修正 `/complete-profile` 頁面重複出現的問題
  - 允許更新電子郵件，解決「Invalid request」錯誤
- 使用 `SESLP_Logger` 統一除錯日誌

### 1.7.16

- 在除錯日誌中遮罩顯示授權金鑰（例如：abc\*\*\*\*123）
- 新增 `wp_options` 檢查指南以利除錯
- 當日誌寫入失敗時顯示管理員通知

### 1.7.15

- 修正除錯日誌寫入失敗問題
- 使用 WordPress 本地時區記錄時間戳
- 於儲存設定時新增除錯日誌

### 1.7.5

- 套用最新的安全性修補
- 進行效能最佳化並改善使用者體驗

### 1.7.0

- 改善社群登入按鈕同步機制
- 強化安全性並修正錯誤

### 1.7.3

- 改善除錯系統
- 新增專用 debug 目錄

### 1.6.0

- 在選擇 Plus / Premium 時，恢復顯示授權金鑰區塊的邏輯

### 1.5.0

- 註冊 `seslp_license_type` 選項
- 修正儲存設定時授權類型被重設為 Free 的問題

### 1.4.0

- 使用 `admin_enqueue_scripts` 修正後台 `style.css` 載入問題

### 1.3.0

- 改善單選按鈕 UI
- 將內嵌 CSS 移至 `style.css`

### 1.2.0

- 新增授權類型選擇功能（Free / Plus / Premium）
- 改善設定頁面 UI 版面配置

### 1.1.0

- 新增多語系支援與翻譯檔載入功能
- 改善使用者驗證邏輯

### 1.0.0

- 初始版本發佈
- 新增 Google、Facebook、Naver、Kakao、Line、Weibo 社群登入

---

## 📄 授權

GPLv2 or later
https://www.gnu.org/licenses/gpl-2.0.html
