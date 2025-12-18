> このドキュメントでは、**Simple Easy Social Login (SESLP)** プラグインで（Google、Facebook、LinkedIn、Naver、Kakao、LINE）など各ソーシャルログインプロバイダーの設定方法を説明します。  
> すべてのログインは **OAuth 2.0 / OpenID Connect (OIDC)** に基づいています。  
> 各プロバイダーの開発者コンソールでアプリ（クライアント）を作成し、**Client ID / Client Secret** を SESLP に入力してください。

---

## 🔧 共通セットアップガイド

#### 1) **リダイレクト URI のルール：**

`https://{ドメイン}/?social_login={provider}`

例：

- Google → `https://example.com/?social_login=google`
- Facebook → `https://example.com/?social_login=facebook`
- LinkedIn → `https://example.com/?social_login=linkedin`
- Naver → `https://example.com/?social_login=naver`
- Kakao → `https://example.com/?social_login=kakao`
- LINE → `https://example.com/?social_login=line`

#### 2) **HTTPS は必須**

多くのプロバイダーでは HTTPS 接続が必須で、`http://`のリダイレクトは拒否されます。

#### 3) **完全一致が必要**

コンソールに登録したリダイレクト URI は、SESLP が送信する URI と**100%一致**している必要があります。  
 （プロトコル、サブドメイン、パス、末尾のスラッシュ、クエリ文字列を含む）

#### 4) **メールが取得できない場合あり**

一部のプロバイダーでは、ユーザーがメール共有を拒否することができます。  
 SESLP はその場合、プロバイダーの安定したユーザー ID を使用してアカウントを紐付けることができます。

#### 5) **ログの確認場所**

- `/wp-content/SESLP-debug.log`
- `/wp-content/debug.log`（`WP_DEBUG_LOG = true`）

## 🐞 デバッグログとトラブルシューティング

SESLP は、OAuth やソーシャルログインの問題を診断するための専用デバッグログファイルを提供します。

<details>
  <summary><strong>SESLP デバッグログの読み方</strong></summary>

#### ログファイルの場所

- `/wp-content/SESLP-debug.log`（SESLP デバッグログ）
- `/wp-content/debug.log`（`WP_DEBUG_LOG = true`）

#### ログフォーマット

```
[YYYY-MM-DD HH:MM:SS Z] [LEVEL] Message {"key":"value",...}
```

- `Z`：UTC または WordPress のローカル時間（例：KST）— SESLP 設定で選択可能
- プライバシー：メール／トークン／シークレットは自動的にマスクされます （例：`r********@g****.com`）

#### OAuth フローのログ（一般的）

**1) OAuth 開始**

```
[DEBUG] State created {"provider":"google","state":"906****23","ttl":"10min"}
```

意味：CSRF 保護用の state トークンが作成されました。 `ttl` は **10 分間** 有効です。

**2) コールバック実行**

```
[DEBUG] Auth route triggered {"provider":"google","has_code":1}
```

意味：コールバックに到達しました。 `has_code:1` → OAuth の `code` を受信しています。

**3) state の検証**

成功：

```
[DEBUG] State validated {"provider":"google","state":"906****23"}
```

失敗：

```
[WARNING] State validation failed: not found/expired {"provider":"google","state":"906****23"}
```

**4) トークン交換**

```
[DEBUG] Token response (google) {"has_access_token":1}
```

意味：アクセストークンの取得に成功しました。

失敗：

```
[ERROR] Token request failed (google) {"error":"..."}
```

**5) userinfo リクエスト**

```
[ERROR] Userinfo request failed (google)
[WARNING] Invalid userinfo (google)
```

**6) ユーザー連携（Linker）**

```
[DEBUG] Linker: signing in user {"user_id":45,"provider":"google","created":0}
[INFO]  Login success (google) {"user_id":45,"email":"r********@g****.com"}
```

**7) リダイレクト**

```
[DEBUG] Redirect decision {"mode":"profile","user_id":45,"url":"https://example.com/wp-admin/profile.php"}
```

#### クイックリファレンステーブル

| ログメッセージ（簡易）  | 主な原因                                       | 対処方法                                        |
| ----------------------- | ---------------------------------------------- | ----------------------------------------------- |
| State validation failed | タイムアウト、タブ切替、重複リクエスト         | すぐ再試行、プライベートモードを使用            |
| Token request failed    | Client ID / Secret / Redirect の誤り、通信遮断 | 開発者コンソール、FW、サーバー時刻を確認        |
| Userinfo invalid        | スコープ不足、メール非公開                     | `email, profile` スコープ追加、同意を確認       |
| User create failed      | アカウント競合、WordPress 制限                 | 既存ユーザー、マルチサイト設定を確認            |
| Redirect missing        | コード内での早期 return                        | Callback 後に Redirect クラスが実行されるか確認 |

#### バグ報告時に含めると有用な情報

- 関連するログ行（マスク済み）
- 使用したプロバイダー（Google / Naver など）
- リダイレクトモード／カスタム URL
- デバッグログの有効状態
- WordPress 環境（シングルサイト、マルチサイト、キャッシュプラグイン）

</details>

---

## 🌍 プロバイダー別ガイド

> 以下の各プロバイダーを展開して、日本語ガイドを追加してください。  
> Google セクションには完全な参考例を記載しています。

---

<details open>
  <summary><strong>Google</strong></summary>

> - **推奨スコープ:** `openid email profile`
> - **Redirect URI:** `https://{domain}/?social_login=google`

---

#### 1) 準備（必須チェックリスト）

(1) **HTTPS は必須**（ローカルでは信頼できる証明書を使用）。

(2) Redirect URI は登録値と**100％一致**させる。

(3) テストモードでは**テストユーザーのみ**ログイン可（最大 100）。

(4) ホーム／ポリシー URL を設定する場合、**Authorized domains**登録と**ドメイン所有確認**が必要。

#### 2) プロジェクトと同意画面設定

(1) **Google Cloud Console**
[https://console.cloud.google.com/apis/credentials](https://console.cloud.google.com/apis/credentials)

(2) プロジェクトを作成または選択。

(3) **APIs & Services → OAuth consent screen** を開く。

(4) **User Type:** External。

(5) **App Information** を入力。

(6) **アプリドメイン** セクション

- アプリのホームページ URL、プライバシーポリシー URL、利用規約 URL を入力
- **Authorized domains（承認済みドメイン）** に **ルートドメイン（例：example.com）** を追加 → **保存**
- 必要に応じて、Search Console を使用して **ドメイン所有権の確認** を実施

(7) **スコープの設定**

- **推奨スコープ：** `openid`, `email`, `profile`
- センシティブ／制限付きスコープは、本番公開前にレビューが必要になる場合があります

(8) **テストユーザー** を追加（テストモードでログインを許可するメールアドレス）

(9) **保存（Save）**

> **注記：**  
> 基本スコープ（`openid email profile`）のみを使用する場合、  
> 多くのケースで **レビューなし** で公開が可能です。

#### 3) OAuth クライアント（Web アプリケーション）を作成

(1) サイドバー：**APIs & Services → Credentials（認証情報）**

(2) 上部メニュー：**+ Create Credentials → OAuth client ID**

(3) アプリケーションの種類：`Web application`

(4) 識別しやすい **名前** を入力（例：`SESLP – Front`）

(5) **Authorized redirect URIs（承認済みリダイレクト URI）** を追加

- `https://{domain}/?social_login=google`

(6) **Create（作成）** をクリック → 表示された **Client ID / Client Secret** をコピー

> （任意）  
> 本プラグインは Authorization Code Grant を使用するため、  
> **Authorized JavaScript origins** は通常不要です。

#### 4) WordPress で設定

(1) 管理画面 → **SESLP 設定 → Google**。

(2) **Client ID / Secret** を貼り付け → 保存。

(3) フロントで Google ボタンをテスト。

#### 5) テストモードから本番（プロダクション）へ切り替え

(1) **OAuth consent screen（同意画面）→ Publishing status（公開ステータス）** を確認

(2) テストモードから本番環境へ切り替えるには：

- アプリ情報（ロゴ／アプリドメイン／ポリシー／利用規約）が正確であることを確認
- 不要なスコープを削除し、必要なスコープのみを残す
- センシティブスコープを使用する場合は、レビュー申請を提出

(3) 本番モードに切り替えると **すべての Google アカウント** がログイン可能になります

#### 6) よくあるエラーと対処方法

(1) **redirect_uri_mismatch**

→ コンソールに登録した Redirect URI と、実際のリクエスト URI が  
わずかでも異なる場合（プロトコル、サブドメイン、スラッシュ、クエリ文字列など）に発生します。  
**完全に一致するように** 修正してください。

(2) **access_denied / disallowed_useragent**

→ ブラウザやアプリ内ブラウザ側の制限によるエラーです。  
通常の Web ブラウザで再度お試しください。

(3) **invalid_client / unauthorized_client**

→ Client ID / Client Secret のタイプミス、またはアプリの状態（削除／無効化）によって発生します。  
認証情報を再確認し、必要に応じて再発行してください。

(4) **Email is empty**

→ `email` スコープが含まれているか、同意画面で正しく表示されているか、  
またアカウント側のメール可視性・セキュリティ設定を確認してください。  
同意画面で「メールアドレスをどのように利用するか」を明確に説明することが重要です。

> **ログの確認場所:**
>
> - `wp-content/SESLP-debug.log`（プラグインのデバッグ ON の場合）
> - `wp-content/debug.log`（`WP_DEBUG`, `WP_DEBUG_LOG = true` の場合）

#### 7) サマリーチェックリスト

- [ ] OAuth 同意画面：アプリ情報／ドメイン／ポリシー／利用規約／スコープ／テストユーザーを設定した
- [ ] 認証情報：**Web Application** クライアントを作成した
- [ ] Redirect URI を登録：`https://{domain}/?social_login=google`
- [ ] SESLP：Client ID / Client Secret を保存し、ログインテストを実施した
- [ ] 本番公開時に公開ステータスを変更した（必要に応じてレビューを申請した）

</details>

---

<details>
  <summary><strong>Facebook</strong></summary>

> - **Redirect URI:** `https://{domain}/?social_login=facebook`
> - **推奨権限:** `public_profile`, `email`
> - ※ Facebook は `openid` を使用しません。

---

#### 1) アプリ作成と製品追加

(1) **Meta for Developers** にアクセス → ログイン
[https://developers.facebook.com/](https://developers.facebook.com/)

(2) **Create App** → 一般タイプ (Consumer など) を選択 → アプリ作成

(3) 左メニューの **Products** → **Facebook Login** を追加

(4) **Settings** → 以下を確認：

- **Client OAuth Login:** ON
- **Web OAuth Login:** ON
- **Valid OAuth Redirect URIs:**
  - `https://{domain}/?social_login=facebook` を追加
- (任意) **Enforce HTTPS:** 推奨設定

#### 2) アプリの基本設定 (App Settings → Basic)

(1) **App Domains:** `example.com`

(2) **Privacy Policy URL:** 公開されたポリシーページ

(3) **Terms of Service URL:** 公開された利用規約ページ

(4) **User Data Deletion:** 削除ガイド URL または API エンドポイント

(5) **カテゴリ / アイコン:** 設定 → 保存

#### 3) 権限とレビュー

(1) 基本権限：**`public_profile`**, オプション：**`email`**

(2) **`email`** は多くの場合レビュー不要

(3) 高度な権限（ページ/広告など）は **App Review** と **Business Verification** が必要

#### 4) 開発モード → 本番モードへの切替

- 上部または設定で **Development → Live** に変更

#### 5) 切替前に確認：

- [ ] ポリシー / 利用規約 / 削除 URL を用意
- [ ] Redirect URI が正確である
- [ ] 不要な権限を削除
- [ ] App Review / Business Verification 完了

#### 6) WordPress 設定 (SESLP)

(1) WP 管理画面 → **SESLP 設定 → Facebook**

(2) **App ID / Secret** を入力 → 保存

(3) フロントエンドでログインテスト

#### 7) トラブルシューティング

(1) **Can't Load URL / redirect_uri エラー**

→ **Valid OAuth Redirect URIs** に登録されている URI が、実際のリクエスト URI と  
（プロトコル、サブドメイン、末尾のスラッシュ、クエリ文字列も含めて）  
**完全に同一であるか** を必ず確認してください。

(2) **email null**

→ ユーザーが Facebook アカウントにメールアドレスを登録していない、または非公開設定になっている場合に発生します。  
その場合は **ID ベースのアカウント連携ロジック** を用意し、  
同意画面でメール権限をどのように利用するのかを明確に説明してください。

(3) **権限（パーミッション）関連のエラー**

→ 要求しているスコープが基本的な範囲を超えている場合、  
**App Review（アプリ審査）／Business Verification（ビジネス認証）** が必要になります。

(4) **Live モードへ切り替えできない**

→ プライバシーポリシー／利用規約／データ削除ガイドラインの URL が  
**未設定、もしくは公開されていない** 場合に発生します。  
必ず外部からアクセス可能な **公開 URL** を設定してください。

</details>

---

<details>
  <summary><strong>LinkedIn</strong></summary>

> - **Redirect URI:** `https://{domain}/?social_login=linkedin`
> - **必須設定:** OpenID Connect(OIDC) 有効化
> - **推奨スコープ:** `openid`, `profile`, `email`
> - LinkedIn は旧来のスコープ（`r_liteprofile`, `r_emailaddress`）を**段階的に廃止**しています。
> - 新規アプリは **OIDC の標準スコープ** を使用する必要があります。

---

#### 1) アプリケーションの作成

(1) **LinkedIn Developers Console** にアクセス

→ [https://www.linkedin.com/developers/apps](https://www.linkedin.com/developers/apps)

(2) LinkedIn アカウントでログイン

(3) **Create app** をクリック

(4) 必須項目を入力：

- **App name:** 例）`MySite LinkedIn Login`
- **LinkedIn Page:** 既存ページを選択、または「None」
- **App logo:** 100×100 以上の PNG/JPG
- **Privacy Policy URL / Business Email:** 有効で公開された URL / メールアドレス

(5) **Create app** をクリック

> **Development Mode** がデフォルトで有効  
> → `openid`, `profile`, `email` ログインを **公開前でもすぐにテスト可能**

#### 2) OpenID Connect (OIDC) を有効化

(1) **Products** タブに移動

(2) **Sign In with LinkedIn using OpenID Connect** を探す

(3) **Add product** をクリック → 通常すぐに承認される

(4) OIDC の各種設定が **Auth** タブに表示される

> **必須 OIDC スコープ**
>
> - `openid` → ID トークン
> - `profile` → 名前、写真、ヘッドライン
> - `email` → メールアドレス

#### 3) OAuth 2.0 設定（Auth タブ）

(1) **Auth → OAuth 2.0 settings** に移動

(2) **Redirect URLs** に次の URL を追加：

→ `https://{domain}/?social_login=linkedin`

(3) **完全一致が必須**（プロトコル、サブドメイン、スラッシュ、クエリ文字列まで）

(4) 必要に応じて複数登録：

- ローカル: `https://localhost:3000/?social_login=linkedin`
- ステージング: `https://staging.example.com/?social_login=linkedin`
- 本番: `https://example.com/?social_login=linkedin`

(5) **Save** をクリック

#### 4) Client ID / Client Secret の取得

(1) **Auth** タブで次を確認：

- **Client ID**
- **Client Secret**

(2) WordPress 管理画面 → **SESLP Settings → LinkedIn**

(3) 両方を貼り付け → **Save**

(4) フロントエンドの **LinkedIn ログインボタン** でテスト

> **セキュリティ:**
>
> - Client Secret を第三者に公開しないこと
> - 流出の可能性がある場合は **Regenerate secret** で再発行すること

#### 5) スコープ

| スコープ  | 説明                     | 備考     |
| --------- | ------------------------ | -------- |
| `openid`  | ID トークン              | **必須** |
| `profile` | 名前、写真、ヘッドライン | **必須** |
| `email`   | メールアドレス           | **必須** |

> **旧来のスコープ（`r_liteprofile`, `r_emailaddress`）**
>
> - **2024 年以降は非推奨**
> - **新規アプリでは利用できません**

#### 6) トラブルシューティング

(1) **redirect_uri_mismatch**

→ コンソールに登録した Redirect URI と、実際のリクエスト URI が  
わずかでも異なる場合に発生します（プロトコル、サブドメイン、スラッシュ、クエリ文字列などを含む）。  
文字列が**100％一致**するように修正してください。

(2) **invalid_client**

→ Client ID / Client Secret の誤り、またはアプリが無効・非アクティブな場合に発生します。  
入力値を再確認するか、必要に応じて再発行してください。

(3) **email NULL**

→ ユーザーがメール共有を拒否したか、`email` スコープが付与されていない場合に発生します。  
同意画面でメールアドレスの利用目的を明確に説明し、`email` スコープが含まれているか確認してください。

(4) **insufficient_scope**

→ リクエストしたスコープが承認されていない場合に発生します。  
OpenID Connect (OIDC) が正しく有効化・設定されているか確認してください。

(5) **OIDC not enabled**

→ Products に **Sign In with LinkedIn using OpenID Connect** が追加されていない場合に発生します。  
対象のプロダクトを追加・有効化してください。

> **ログの確認場所:**
>
> - `/wp-content/SESLP-debug.log`
> - `/wp-content/debug.log`

#### 7) サマリーチェックリスト

- [ ] アプリを作成した
- [ ] **OpenID Connect** プロダクトを追加した
- [ ] Redirect URI を 100％ 正確に登録した
- [ ] Client ID / Client Secret を SESLP に保存した
- [ ] スコープが `openid profile email`（レガシースコープなし）で設定されている
- [ ] HTTPS 環境のフロントエンドでログインテストを行った

---

> **備考:**
>
> - SESLP は **OIDC フロー** に完全対応しています。
> - 従来の OAuth 2.0 は **サポート終了** しています。
> - 新規連携では必ず **OpenID Connect** を使用してください。

</details>

---

<details>
  <summary><strong>Naver</strong></summary>

> - **Redirect URI:** `https://{domain}/?social_login=naver`
> - **推奨スコープ:** `name`, `email`
> - Naver は **네아로 (Naver Login)** を使用、**HTTPS 必須**

---

#### 1) アプリケーション登録

(1) **Naver Developer Center** にアクセス

→ [https://developers.naver.com/apps/](https://developers.naver.com/apps/)

(2) Naver アカウントでログイン

(3) **Application → Register Application** をクリック

(4) 必須項目を入力：

- **Application Name:** 例）`MySite Naver Login`
- **API Usage:** `Naver Login (네아로)` を選択
- **Add Environment → Web**
- **Service URL:** `https://example.com`
- **Callback URL:** `https://example.com/?social_login=naver`

(5) 利用規約に同意 → **Register**

> **注意:**
>
> - **HTTPS は必須** → HTTP は利用不可
> - **サブドメインは個別に登録する必要があります**

#### 2) Client ID / Client Secret の取得

(1) **My Applications（マイアプリケーション）** にアクセス

(2) 対象アプリをクリック → **Client ID** と **Client Secret** をコピー

#### 3) WordPress（プラグイン）設定

(1) WP 管理画面 → **SESLP Settings → Naver**

(2) **Client ID / Client Secret** を貼り付け

(3) **Redirect URI** が次の値と完全に一致していることを確認：  
`https://{domain}/?social_login=naver`

(4) **保存（Save）** → フロントエンドの **Naver ログインボタン** でテスト

#### 4) 権限とデータ提供

| 情報           | スコープ | 備考         |
| -------------- | -------- | ------------ |
| 名前           | `name`   | デフォルト   |
| メール         | `email`  | デフォルト   |
| 性別、生年月日 | 別途     | **審査必要** |

> - ユーザーは同意画面で **同意 / 拒否** を選択できます
> - メール共有を拒否した場合 → `email = null` → **ID ベースのアカウント連携** を使用
> - センシティブなデータを取得する場合は **Naver アプリ審査** が必要

#### 5) トラブルシューティング

(1) **Redirect URI mismatch**

→ わずかな違いでもエラーになります → **100% 一致** しているか確認

(2) **HTTP エラー**

→ **HTTPS が必須**

(3) **サブドメイン関連エラー**

→ サブドメインはそれぞれ **別個に登録** する必要があります

(4) **email NULL**

→ ユーザーがメールを拒否した、またはプライベート設定  
→ **ID ベースの連携ロジック** を準備しておく

(5) **審査が必要なケース**

→ 基本ログイン：**審査不要**  
→ 追加データ取得：**審査が必要**

> **ログ確認:**
>
> - `/wp-content/SESLP-debug.log`
> - `/wp-content/debug.log`

#### 6) チェックリスト（まとめ）

- [ ] Naver Developer Center でアプリ登録済み
- [ ] **Callback URL** を正確に登録
- [ ] **HTTPS** を使用
- [ ] 必要に応じてサブドメインを別途登録
- [ ] Client ID / Secret を SESLP に保存
- [ ] メール同意 / 拒否の挙動をテスト
- [ ] フロントエンドでログイン動作を確認済み

---

> **注意:**
>
> - SESLP は **Naver Login (네아로)** に完全対応しています。
> - 基本ログイン（`name`, `email`）は **審査なしで利用可能** です。

</details>

---

<details>
  <summary><strong>Kakao</strong></summary>

> - **Redirect URI:** `https://{domain}/?social_login=kakao`
> - **推奨スコープ:** `profile_nickname`, `profile_image`, `account_email`
> - `account_email` は **本人確認または事業者登録完了後** のみ使用可能
> - **HTTPS 必須**, **Client Secret アクティブ化必須**

---

#### 1) アプリケーション作成

(1) **Kakao Developers** にアクセス

→ [https://developers.kakao.com/](https://developers.kakao.com/)

(2) ログイン → **マイアプリケーション → アプリ追加**

(3) 入力:

- アプリ名、会社名
- カテゴリ
- 運営ポリシー同意

(4) **保存**

#### 2) Kakao ログイン有効化

(1) **製品設定 > Kakao ログイン**

(2) **Kakao ログイン有効化** → **ON**

(3) **リダイレクト URI 登録**

- `https://{domain}/?social_login=kakao`
- **保存**

(4) ドメインは **プラットフォーム登録ドメインと完全一致**

#### 3) 同意項目（スコープ）設定

(1) **同意項目**

(2) 追加と設定:

| スコープ           | 説明             | 同意タイプ | 備考             |
| ------------------ | ---------------- | ---------- | ---------------- |
| `profile_nickname` | ニックネーム     | 必須/任意  | 基本             |
| `profile_image`    | プロフィール画像 | 必須/任意  | 基本             |
| `account_email`    | メールアドレス   | **任意**   | **本人確認必要** |

(3) 各項目に **利用目的** を明確に記載

(4) **保存**

> 機密スコープは **本人確認必須**

#### 4) Web プラットフォーム登録

(1) **アプリ設定 > プラットフォーム**

(2) **Web プラットフォーム登録**

(3) サイトドメイン: `https://{domain}`

(4) **保存** → リダイレクト URI と完全一致

#### 5) セキュリティ – Client Secret 生成 & アクティブ化

(1) **製品設定 > セキュリティ**

(2) **Client Secret 使用** → **ON**

(3) **Secret 生成** → 値をコピー

(4) **アクティブ化状態** → **使用中**

(5) **保存**

> **生成後にアクティブ化必須**

#### 6) REST API キー取得 (Client ID)

(1) **アプリキー**

(2) **REST API キー** コピー → **Client ID** として使用

#### 7) WordPress 設定

(1) WP 管理 → **SESLP 設定 → Kakao**

(2) **Client ID** = REST API キー  
 **Client Secret** = 生成した Secret

(3) **保存**

(4) **Kakao ログインボタン** でテスト

#### 8) トラブルシューティング

(1) **redirect_uri_mismatch** → 完全一致必須

(2) **invalid_client** → Secret 未アクティブ化または誤り

(3) **email 空** → ユーザー拒否または未確認

(4) **ドメイン不一致** → プラットフォーム vs URI

(5) **HTTP 禁止** → **HTTPS のみ**

> **ログ:**
>
> - `/wp-content/SESLP-debug.log`
> - `/wp-content/debug.log`

#### 9) チェックリスト

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

> - **Redirect URI:** `https://{domain}/?social_login=line`
> - **必須:** OpenID Connect 有効化、**メールアドレス権限申請・承認取得**
> - **推奨スコープ:** `openid`, `profile`, `email`
> - **HTTPS 必須**、メール取得は**事前承認が必要**

---

#### 1) プロバイダーとチャネル作成

(1) **LINE Developers Console** にアクセス

→ [https://developers.line.biz/console/](https://developers.line.biz/console/)

(2) **LINE ビジネスアカウント**でログイン（個人アカウント不可）

(3) **新しいプロバイダー作成** → 名前入力 → **Create**

(4) プロバイダー下 → **Channels** タブ

(5) **LINE Login チャネル作成** を選択

(6) 設定:

- **チャネルタイプ:** `LINE Login`
- **プロバイダー:** 作成済み
- **地域:** 対象国（例: `South Korea`, `Japan`）
- **名前 / 説明 / アイコン:** 同意画面に表示

(7) 規約同意 → **作成**

#### 2) OpenID Connect 有効化とメール権限申請

(1) メニュー **OpenID Connect**

(2) **Email address permission** の横の **Apply** をクリック

(3) 申請フォーム:

- **プライバシーポリシー URL**（公開アクセス可能）
- **プライバシーポリシーのスクリーンショット**
- 同意 → **Submit**

(4) **`email` スコープは承認後にのみ有効**  
 → 承認まで通常 1〜3 営業日

#### 3) Callback URL 登録とチャネル公開

(1) メニュー **LINE Login**

(2) **Callback URL** 入力:

→ `https://{domain}/?social_login=line`

(3) **完全一致必須**:

- プロトコル: `https://`（**HTTP 不可**）
- ドメイン、パス、クエリ **100% 一致**

(4) **保存**

(5) チャネル状態を **Published** に変更

- **Development:** テストのみ
- **Published:** 本番運用

#### 4) Channel ID / Secret 取得

(1) チャネル上部または **Basic settings**

(2) **Channel ID** → SESLP **Client ID**  
 **Channel Secret** → SESLP **Client Secret**

#### 5) WordPress 設定

(1) WP 管理 → **SESLP 設定 → LINE**

(2) **Client ID** ← Channel ID  
 **Client Secret** ← Channel Secret

(3) **保存**

(4) フロントエンドの **LINE ログインボタン** でテスト

### 6) トラブルシューティング

(1) **redirect_uri_mismatch** → わずかな違いでもエラー → **100% 一致**

(2) **invalid_client** → Secret 誤り or **未公開**

(3) **email NULL** → **メール権限未承認** または拒否

(4) **HTTP 使用不可** → **HTTPS 必須**（localhost HTTPS 可）

(5) **Development モード制限** → テストアカウントのみログイン可能

> **ログ:**
>
> - `/wp-content/SESLP-debug.log`
> - `/wp-content/debug.log`

### 7) チェックリスト

- [ ] ビジネスアカウントで **プロバイダー + LINE Login チャネル作成**
- [ ] **メール権限申請・承認完了**
- [ ] **Callback URL** 完全一致で登録
- [ ] **HTTPS 使用**、**Published 状態**
- [ ] Channel ID/Secret を SESLP に保存
- [ ] フロントエンドで実際のログイン確認

> **注意:**
>
> - SESLP は **LINE Login v2.1 + OpenID Connect** に完全対応しています。
> - メールアドレスの取得には**事前承認**が必要です。

</details>
