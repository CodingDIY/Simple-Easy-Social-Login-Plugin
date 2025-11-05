# Simple Easy Social Login (SESLP) — ソーシャルログインガイド（日本語）

> このドキュメントでは、**Simple Easy Social Login (SESLP)** プラグインで  
> （Google、Facebook、LinkedIn、Naver、Kakao、LINE）など各ソーシャルログインプロバイダーの設定方法を説明します。  
> すべてのログインは **OAuth 2.0 / OpenID Connect (OIDC)** に基づいています。  
> 各プロバイダーの開発者コンソールでアプリ（クライアント）を作成し、**Client ID / Client Secret** を SESLP に入力してください。

---

## 🔧 共通セットアップガイド

- **リダイレクト URI のルール：**  
  `https://{ドメイン}/?social_login={provider}`  
  例：

  - Google → `https://example.com/?social_login=google`
  - Facebook → `https://example.com/?social_login=facebook`
  - LinkedIn → `https://example.com/?social_login=linkedin`
  - Naver → `https://example.com/?social_login=naver`
  - Kakao → `https://example.com/?social_login=kakao`
  - LINE → `https://example.com/?social_login=line`

- **HTTPS は必須**  
  多くのプロバイダーでは HTTPS 接続が必須で、`http://`のリダイレクトは拒否されます。

- **完全一致が必要**  
  コンソールに登録したリダイレクト URI は、SESLP が送信する URI と**100%一致**している必要があります。  
  （プロトコル、サブドメイン、パス、末尾のスラッシュ、クエリ文字列を含む）

- **メールが取得できない場合あり**  
  一部のプロバイダーでは、ユーザーがメール共有を拒否することができます。  
  SESLP はその場合、プロバイダーの安定したユーザー ID を使用してアカウントを紐付けることができます。

- **ログの確認場所**
  - `/wp-content/seslp-logs/seslp-debug.log`
  - `/wp-content/debug.log`（`WP_DEBUG_LOG = true`）

---

## 🌍 プロバイダー別ガイド

> 以下の各プロバイダーを展開して、日本語ガイドを追加してください。  
> Google セクションには完全な参考例を記載しています。

---

<details open>
  <summary><strong>Google</strong></summary>

> **推奨スコープ:** `openid email profile`  
> **Redirect URI:** `https://{domain}/?social_login=google`

---

### 1) 準備（必須チェックリスト）

- **HTTPS は必須**（ローカルでは信頼できる証明書を使用）。
- Redirect URI は登録値と**100％一致**させる。
- テストモードでは**テストユーザーのみ**ログイン可（最大 100）。
- ホーム／ポリシー URL を設定する場合、**Authorized domains**登録と**ドメイン所有確認**が必要。

### 2) プロジェクトと同意画面設定

1. **Google Cloud Console**
   - <https://console.cloud.google.com/apis/credentials>
2. プロジェクトを作成または選択。
3. **APIs & Services → OAuth consent screen** を開く。
4. **User Type:** External。
5. **App Information** を入力。
6. **App domain** に URL とルートドメインを登録し保存。
7. **Scopes:** `openid email profile` を設定。
8. **Test users** を追加 → 保存。

> 基本スコープのみなら多くの場合**審査不要**で公開可能。

### 3) OAuth クライアント作成（Web アプリ）

1. **APIs & Services → Credentials**。
2. **+ Create Credentials → OAuth client ID**。
3. 種類：`Web application`。
4. 名前：`SESLP – Front`。
5. **Authorized redirect URIs:**
   - `https://{domain}/?social_login=google`
6. **Client ID / Secret** をコピー。

### 4) WordPress で設定

1. 管理画面 → **SESLP 設定 → Google**。
2. **Client ID / Secret** を貼り付け → 保存。
3. フロントで Google ボタンをテスト。

### 5) 本番切替

1. 公開ステータスを確認。
2. 不要スコープ削除、情報確認。
3. センシティブスコープ使用時は審査申請。

### 6) よくあるエラー

- **redirect_uri_mismatch** – URI 不一致。
- **access_denied** – ブラウザ制限。
- **invalid_client** – ID/Secret 誤り。
- **メールなし** – スコープ・設定確認。

</details>

---

<details>
  <summary><strong>Facebook (Meta)</strong></summary>

> **Redirect URI:** `https://{domain}/?social_login=facebook`  
> **推奨権限:** `public_profile`, `email`  
> ※ Facebook は `openid` を使用しません。

---

### 1) アプリ作成と製品追加

1. **Meta for Developers** にアクセス → ログイン
2. **Create App** → 一般タイプ (Consumer など) を選択 → アプリ作成
3. 左メニューの **Products** → **Facebook Login** を追加
4. **Settings** → 以下を確認：
   - **Client OAuth Login:** ON
   - **Web OAuth Login:** ON
   - **Valid OAuth Redirect URIs:**
     - `https://{domain}/?social_login=facebook` を追加
   - (任意) **Enforce HTTPS:** 推奨設定

### 2) アプリの基本設定 (App Settings → Basic)

- **App Domains:** `example.com`
- **Privacy Policy URL:** 公開されたポリシーページ
- **Terms of Service URL:** 公開された利用規約ページ
- **User Data Deletion:** 削除ガイド URL または API エンドポイント
- **カテゴリ / アイコン:** 設定 → 保存

### 3) 権限とレビュー

- 基本権限：**`public_profile`**, オプション：**`email`**
- **`email`** は多くの場合レビュー不要
- 高度な権限（ページ/広告など）は **App Review** と **Business Verification** が必要

### 4) 開発モード → 本番モードへの切替

- 上部または設定で **Development → Live** に変更
- 切替前に確認：
  - [ ] ポリシー / 利用規約 / 削除 URL を用意
  - [ ] Redirect URI が正確である
  - [ ] 不要な権限を削除
  - [ ] App Review / Business Verification 完了

### 5) WordPress 設定 (SESLP)

1. WP 管理画面 → **SESLP 設定 → Facebook**
2. **App ID / Secret** を入力 → 保存
3. フロントエンドでログインテスト

### 6) トラブルシューティング

- **Can't Load URL / redirect_uri エラー** → URI が一致しているか確認
- **email null** → ユーザーがメールを登録していない
- **権限エラー** → 拡張スコープにはレビューが必要
- **Live 切替不可** → ポリシー URL が未公開

</details>

---

<details>
  <summary><strong>LinkedIn</strong></summary>

> **Redirect URI:** `https://{domain}/?social_login=linkedin`  
> **必須設定:** OpenID Connect(OIDC) 有効化  
> **推奨スコープ:** `openid`, `profile`, `email`

---

### 1) アプリ作成

1. **LinkedIn Developers Console** → [リンク](https://www.linkedin.com/developers/apps)
2. ログイン
3. **Create app**
4. 必須項目:
   - アプリ名、ページ、ロゴ、プライバシーポリシー、メール
5. 作成

> 開発モード → 即時テスト可能

---

### 2) OIDC 有効化

1. **Products** → **Sign In with LinkedIn using OpenID Connect** 追加

---

### 3) OAuth 設定

1. **Auth → OAuth 2.0 settings**
2. Redirect URL: `https://{domain}/?social_login=linkedin`
3. 完全一致必須
4. 保存

---

### 4) Client ID / Secret

1. **Auth** タブで確認
2. SESLP → LinkedIn → 貼り付け → 保存
3. フロントでテスト

---

### 5) スコープ

| スコープ  | 説明                     | 備考     |
| --------- | ------------------------ | -------- |
| `openid`  | ID トークン              | **必須** |
| `profile` | 名前、写真、ヘッドライン | **必須** |
| `email`   | メールアドレス           | **必須** |

> 旧スコープ **非推奨**

---

### 6) トラブルシューティング

- **redirect_uri_mismatch** → URI 完全一致
- **invalid_client** → ID/Secret 確認
- **email NULL** → スコープまたは同意不足

---

### 7) チェックリスト

- [ ] アプリ作成
- [ ] OIDC 追加
- [ ] Redirect URI 登録
- [ ] ID/Secret 入力
- [ ] HTTPS テスト

</details>

---

<details>
  <summary><strong>Naver</strong></summary>

> **Redirect URI:** `https://{domain}/?social_login=naver`  
> **推奨スコープ:** `name`, `email`  
> ※ Naver は **네아로 (Naver Login)** を使用、**HTTPS 必須**

---

### 1) アプリ登録

1. **Naver Developer Center** → [リンク](https://developers.naver.com/apps/)
2. ログイン
3. **アプリケーション登録**
4. 必須項目:
   - アプリ名、API: `Naver Login`
   - Web: サービス URL、**Callback URL**
5. **登録**

> HTTPS 必須、サブドメイン別登録

---

### 2) Client ID / Secret

1. **マイアプリケーション** → コピー

---

### 3) WordPress 設定

1. WP 管理 → **SESLP → Naver**
2. ID/Secret 貼り付け
3. URI 完全一致確認
4. **保存** → テスト

---

### 4) 権限

| 情報           | スコープ | 備考         |
| -------------- | -------- | ------------ |
| 名前           | `name`   | デフォルト   |
| メール         | `email`  | デフォルト   |
| 性別、生年月日 | 別途     | **審査必要** |

> メール拒否 → `null`

---

### 5) トラブルシューティング

- **redirect_uri_mismatch** → 完全一致
- **HTTP 不可** → HTTPS のみ
- **サブドメイン** → 個別登録

---

### 6) チェックリスト

- [ ] アプリ登録
- [ ] Callback URL 正確
- [ ] HTTPS
- [ ] ID/Secret 入力
- [ ] メール同意テスト

</details>

---

<details>
  <summary><strong>Kakao</strong></summary>

> **Redirect URI:** `https://{domain}/?social_login=kakao`  
> **推奨スコープ:** `profile_nickname`, `profile_image`, `account_email`  
> ※ `account_email` は **本人確認または事業者登録完了後** のみ使用可能  
> ※ **HTTPS 必須**, **Client Secret アクティブ化必須**

---

### 1) アプリケーション作成

1. **Kakao Developers** にアクセス  
   → [https://developers.kakao.com/](https://developers.kakao.com/)
2. ログイン → **マイアプリケーション → アプリ追加**
3. 入力:
   - アプリ名、会社名
   - カテゴリ
   - 運営ポリシー同意
4. **保存**

---

### 2) Kakao ログイン有効化

1. **製品設定 > Kakao ログイン**
2. **Kakao ログイン有効化** → **ON**
3. **リダイレクト URI 登録**
   - `https://{domain}/?social_login=kakao`
   - **保存**
4. ドメインは **プラットフォーム登録ドメインと完全一致**

---

### 3) 同意項目（スコープ）設定

1. **同意項目**
2. 追加と設定:

| スコープ           | 説明             | 同意タイプ | 備考             |
| ------------------ | ---------------- | ---------- | ---------------- |
| `profile_nickname` | ニックネーム     | 必須/任意  | 基本             |
| `profile_image`    | プロフィール画像 | 必須/任意  | 基本             |
| `account_email`    | メールアドレス   | **任意**   | **本人確認必要** |

3. 各項目に **利用目的** を明確に記載
4. **保存**

> 機密スコープは **本人確認必須**

---

### 4) Web プラットフォーム登録

1. **アプリ設定 > プラットフォーム**
2. **Web プラットフォーム登録**
3. サイトドメイン: `https://{domain}`
4. **保存** → リダイレクト URI と完全一致

---

### 5) セキュリティ – Client Secret 生成 & アクティブ化

1. **製品設定 > セキュリティ**
2. **Client Secret 使用** → **ON**
3. **Secret 生成** → 値をコピー
4. **アクティブ化状態** → **使用中**
5. **保存**
   > **生成後にアクティブ化必須**

---

### 6) REST API キー取得 (Client ID)

1. **アプリキー**
2. **REST API キー** コピー → **Client ID** として使用

---

### 7) WordPress 設定

1. WP 管理 → **SESLP 設定 → Kakao**
2. **Client ID** = REST API キー  
   **Client Secret** = 生成した Secret
3. **保存**
4. **Kakao ログインボタン** でテスト

---

### 8) トラブルシューティング

- **redirect_uri_mismatch** → 完全一致必須
- **invalid_client** → Secret 未アクティブ化または誤り
- **email 空** → ユーザー拒否または未確認
- **ドメイン不一致** → プラットフォーム vs URI
- **HTTP 禁止** → **HTTPS のみ**

> **ログ:**  
> `/wp-content/seslp-logs/seslp-debug.log`  
> `/wp-content/debug.log`

---

### 9) チェックリスト

- [ ] Kakao ログイン有効化
- [ ] リダイレクト URI 登録
- [ ] Web プラットフォームドメイン登録
- [ ] 同意項目設定
- [ ] Client Secret 生成 + アクティブ化
- [ ] REST API キー / Secret を SESLP に入力
- [ ] HTTPS フロントエンドでテスト完了

</details>

---

<details>
  <summary><strong>LINE</strong></summary>

> **Redirect URI:** `https://{domain}/?social_login=line`  
> **必須:** OpenID Connect 有効化、**メールアドレス権限申請・承認取得**  
> **推奨スコープ:** `openid`, `profile`, `email`  
> ※ **HTTPS 必須**、メール取得は**事前承認が必要**

---

### 1) プロバイダーとチャネル作成

1. **LINE Developers Console** にアクセス  
   → [https://developers.line.biz/console/](https://developers.line.biz/console/)
2. **LINE ビジネスアカウント**でログイン（個人アカウント不可）
3. **新しいプロバイダー作成** → 名前入力 → **Create**
4. プロバイダー下 → **Channels** タブ
5. **LINE Login チャネル作成** を選択
6. 設定:
   - **チャネルタイプ:** `LINE Login`
   - **プロバイダー:** 作成済み
   - **地域:** 対象国（例: `South Korea`, `Japan`）
   - **名前 / 説明 / アイコン:** 同意画面に表示
7. 規約同意 → **作成**

---

### 2) OpenID Connect 有効化とメール権限申請

1. メニュー **OpenID Connect**
2. **Email address permission** の横の **Apply** をクリック
3. 申請フォーム:
   - **プライバシーポリシー URL**（公開アクセス可能）
   - **プライバシーポリシーのスクリーンショット**
   - 同意 → **Submit**
4. **`email` スコープは承認後にのみ有効**  
   → 承認まで通常 1〜3 営業日

---

### 3) Callback URL 登録とチャネル公開

1. メニュー **LINE Login**
2. **Callback URL** 入力:  
   → `https://{domain}/?social_login=line`
3. **完全一致必須**:
   - プロトコル: `https://`（**HTTP 不可**）
   - ドメイン、パス、クエリ **100% 一致**
4. **保存**
5. チャネル状態を **Published** に変更
   - **Development:** テストのみ
   - **Published:** 本番運用

---

### 4) Channel ID / Secret 取得

1. チャネル上部または **Basic settings**
2. **Channel ID** → SESLP **Client ID**  
   **Channel Secret** → SESLP **Client Secret**

---

### 5) WordPress 設定

1. WP 管理 → **SESLP 設定 → LINE**
2. **Client ID** ← Channel ID  
   **Client Secret** ← Channel Secret
3. **保存**
4. フロントエンドの **LINE ログインボタン** でテスト

---

### 6) トラブルシューティング

- **redirect_uri_mismatch** → わずかな違いでもエラー → **100% 一致**
- **invalid_client** → Secret 誤り or **未公開**
- **email NULL** → **メール権限未承認** または拒否
- **HTTP 使用不可** → **HTTPS 必須**（localhost HTTPS 可）
- **Development モード制限** → テストアカウントのみログイン可能

> **ログ:**  
> `/wp-content/seslp-logs/seslp-debug.log`  
> `/wp-content/debug.log`

---

### 7) チェックリスト

- [ ] ビジネスアカウントで **プロバイダー + LINE Login チャネル作成**
- [ ] **メール権限申請・承認完了**
- [ ] **Callback URL** 完全一致で登録
- [ ] **HTTPS 使用**、**Published 状態**
- [ ] Channel ID/Secret を SESLP に保存
- [ ] フロントエンドで実際のログイン確認

---

> **備考:** SESLP は **LINE Login v2.1 + OpenID Connect** に完全対応。  
> **メール取得には事前承認必須**。

</details>

---

## 📋 まとめ

| プラン | プロバイダ   | 必須 / 推奨スコープ                                  | リダイレクト URI 例                       | 備考                    |
| ------ | ------------ | ---------------------------------------------------- | ----------------------------------------- | ----------------------- |
| 無料   | **Google**   | `openid email profile`                               | `https://{domain}/?social_login=google`   | 外部の同意画面が必要    |
| 無料   | **Facebook** | `public_profile`, `email`                            | `https://{domain}/?social_login=facebook` | `openid` は未使用       |
| 無料   | **LinkedIn** | `openid profile email`                               | `https://{domain}/?social_login=linkedin` | OIDC へ完全移行         |
| 有料   | **Naver**    | `email`, `name`                                      | `https://{domain}/?social_login=naver`    | 「Naver Login」API      |
| 有料   | **Kakao**    | `profile_nickname`, `profile_image`, `account_email` | `https://{domain}/?social_login=kakao`    | Client Secret が必須    |
| 有料   | **LINE**     | `openid profile email`                               | `https://{domain}/?social_login=line`     | 「Published」状態が必須 |
