> 本文档说明如何在 **Simple Easy Social Login (SESLP)** 插件中配置各个社交登录提供方（Google、Facebook、LinkedIn、Naver、Kakao、LINE）。  
> 所有登录均基于 **OAuth 2.0 / OpenID Connect (OIDC)**。  
> 您需要在各提供方的开发者控制台中创建应用（Client），并在 SESLP 中填写 **Client ID / Client Secret**。

---

## 🔧 通用设置指南

#### 1) **重定向 URI 规则：**

`https://{你的域名}/?social_login={provider}`

示例：

- Google → `https://example.com/?social_login=google`
- Facebook → `https://example.com/?social_login=facebook`
- LinkedIn → `https://example.com/?social_login=linkedin`
- Naver → `https://example.com/?social_login=naver`
- Kakao → `https://example.com/?social_login=kakao`
- LINE → `https://example.com/?social_login=line`

#### 2) **必须使用 HTTPS**

大多数提供方要求 HTTPS，并拒绝 `http://` 重定向。

#### 3) **严格匹配**

控制台中注册的 Redirect URI 必须与 SESLP 实际发送的地址 **100% 完全一致**  
 （包括协议、子域名、路径、结尾斜杠和查询参数）。

#### 4) **邮箱可能不可用**

一些提供方允许用户拒绝共享邮箱。SESLP 可使用提供方稳定的用户 ID 来关联账户。

#### 5) **日志查看路径**

- `/wp-content/seslp-logs/seslp-debug.log`
- `/wp-content/debug.log`（`WP_DEBUG_LOG = true`）

---

## 🌍 各提供方指南

> 展开以下提供方查看对应说明。  
> Google 已完整展示，其他部分请根据需要添加。

---

<details open>
  <summary><strong>Google</strong></summary>

> - **推荐作用域：** `openid email profile`
> - **Redirect URI：** `https://{domain}/?social_login=google`

---

#### 1) 准备工作

(1) **必须使用 HTTPS**。

(2) Redirect URI 必须 **100% 完全匹配** 控制台中的值。 Ex) `https://example.com/?social_login=google`

(3) 测试模式下仅 **测试用户** 可登录。

(4) 如使用隐私/服务 URL，请登记 **Authorized domains** 并验证域名所有权。

#### 2) 项目与授权屏幕设置

(1) 登录 **Google Cloud Console**
[https://console.cloud.google.com/apis/credentials](https://console.cloud.google.com/apis/credentials)

(2) 创建或选择项目。

(3) 打开 **APIs & Services → OAuth consent screen**。

(4) 用户类型：**External（外部）**。

(5) 填写应用信息。

(6) **应用域（App domain）** 部分

- 输入应用主页 URL、隐私政策 URL、服务条款（Terms of Service）URL
- 在 **Authorized domains** 中添加**根域名（例如：example.com）** → 点击 **Save**
- 如有需要，通过 Search Console 进行**域名所有权验证**

(7) 设定 **Scopes：** `openid email profile`。

(8) 添加 **测试用户** → 保存。

> 仅使用基础作用域通常可 **无需审核直接发布**。

#### 3) 创建 OAuth 客户端（Web 应用）

(1) 打开 **APIs & Services → Credentials**。

(2) 选择 **+ Create Credentials → OAuth client ID**。

(3) 类型：`Web application`。

(4) 名称：`SESLP – Front`。

(5) **Authorized redirect URIs：**

- `https://{domain}/?social_login=google`

(6) 复制 **Client ID / Secret**。

（可选）对于本插件使用的授权码模式，一般不需要配置 “Authorized JavaScript origins（已授权的 JavaScript 来源）”。

#### 4) WordPress 插件设置

(1) WP 后台 → **SESLP Settings → Google**。

(2) 粘贴 **Client ID / Secret** → 保存。

(3) 前端测试 Google 登录。

#### 5) 从测试模式切换到正式环境

(1) 在 **OAuth consent screen → Publishing status** 中查看当前发布状态。

(2) 要从测试切换到正式环境时：

- 确认应用信息（Logo / 应用域名 / 隐私政策 / 服务条款）填写正确。
- 移除不必要的作用域，仅保留真正需要的作用域。
- 如使用敏感作用域，需提交审核申请。

(3) 切换为正式环境后，**所有 Google 账户** 都可以登录。

#### 6) 常见错误与解决方法

(1) **redirect_uri_mismatch**

→ 当控制台中登记的 Redirect URI 与实际请求 URI 有哪怕**一丁点差异**时（包括协议、子域名、斜杠、查询参数等）就会发生此错误。  
请修改为 **完全一致（100% 匹配）**。

(2) **access_denied / disallowed_useragent**

→ 由于浏览器或应用内浏览环境限制导致。  
请在普通的标准浏览器中重新尝试。

(3) **invalid_client / unauthorized_client**

→ Client ID / Client Secret 拼写错误，或应用状态为已删除 / 已禁用时会出现。  
请重新检查或重新生成凭证。

(4) **Email is empty**

→ 请检查是否包含 `email` 作用域、同意屏幕上是否展示了邮箱授权，以及账号本身的邮箱可见性/安全设置。  
务必在用户同意屏幕中**清楚说明邮箱权限的用途**。

> **查看日志：**
>
> - `wp-content/seslp-logs/seslp-debug.log`（插件调试开启时）
> - `wp-content/debug.log`（`WP_DEBUG`, `WP_DEBUG_LOG = true`）

#### 7) 总结检查清单

- [ ] OAuth 同意屏幕：已设置应用信息 / 域名 / 隐私政策 / 服务条款 / 作用域 / 测试用户
- [ ] 凭证：已创建 **Web Application** 类型的客户端
- [ ] 已注册 Redirect URI：`https://{domain}/?social_login=google`
- [ ] SESLP 中已保存 Client ID / Client Secret 并完成登录测试
- [ ] 上线前已修改发布状态（如使用敏感作用域，已提交审核）

</details>

---

<details>
  <summary><strong>Facebook</strong></summary>

> - **Redirect URI:** `https://{domain}/?social_login=facebook`
> - **推荐权限:** `public_profile`, `email`
> - Facebook 不使用 `openid`。

---

#### 1) 创建应用并添加产品

(1) 打开 **Meta for Developers** → 登录
[https://developers.facebook.com/](https://developers.facebook.com/)

(2) 点击 **Create App** → 选择类型（如 Consumer）→ 创建应用

(3) 左侧菜单 → **Products → Facebook Login**

(4) 进入 **Settings** → 检查以下：

- **Client OAuth Login:** 开启
- **Web OAuth Login:** 开启
- **Valid OAuth Redirect URIs:**
- 添加 `https://{domain}/?social_login=facebook`
- (可选) **Enforce HTTPS:** 推荐开启

#### 2) 应用基本设置

(1) **App Domains:** `example.com`

(2) **Privacy Policy URL:** 可公开访问

(3) **Terms of Service URL:** 可公开访问

(4) **User Data Deletion:** 提供删除数据说明或接口

(5) **分类 / 图标:** 设置并保存

#### 3) 权限与审核

(1) 基本权限：**`public_profile`**，可选：**`email`**

(2) 通常 **`email` 可直接使用**

(3) 高级权限需 **App Review** 与 **Business Verification**

#### 4) 切换模式（开发 → 生产）

- 将模式从 **Development → Live**

#### 5) 检查：

- [ ] 政策/条款/删除 URL 已公开
- [ ] Redirect URI 精确
- [ ] 权限最少化
- [ ] 审核完成

#### 6) WordPress 设置 (SESLP)

(1) WP 后台 → **SESLP 设置 → Facebook**

(2) 输入 **App ID / Secret** → 保存

(3) 测试 Facebook 登录按钮

#### 7) 故障排查

(1) **Can't Load URL / redirect_uri 错误**

→ 请确保在 **Valid OAuth Redirect URIs** 中注册的 URI 与实际使用的地址 **完全一致（100% 匹配）**，包括协议、子域名、斜杠、查询参数等。

(2) **email 为 null**

→ 用户未注册邮箱，或邮箱为私密状态。请准备基于 **ID 的账户关联逻辑**，并在同意屏幕中清楚说明邮箱权限的用途。

(3) **权限相关错误**

→ 如果请求的权限超出基础范围，则需要进行 **App Review / Business Verification（应用审核 / 企业验证）**。

(4) **无法切换到 Live 模式**

→ 如果隐私政策 / 使用条款 / 数据删除指南 URL **缺失或不可公开访问**，则无法上线。必须提供一个可公开访问的 URL。

</details>

---

<details>
  <summary><strong>LinkedIn</strong></summary>

> - **Redirect URI：** `https://{domain}/?social_login=linkedin`
> - **必需设置：** 启用 OpenID Connect (OIDC)
> - **推荐作用域：** `openid`, `profile`, `email`
> - LinkedIn 正在逐步**淘汰**旧版作用域（`r_liteprofile`, `r_emailaddress`）。
> - 新创建的应用**必须使用 OIDC 标准作用域**。

---

#### 1) 创建应用

(1) 进入 **LinkedIn Developers Console**

→ [https://www.linkedin.com/developers/apps](https://www.linkedin.com/developers/apps)

(2) 使用 LinkedIn 账号登录

(3) 点击 **Create app（创建应用）**

(4) 填写以下必填信息：

- **应用名称（App name）：** 如 `MySite LinkedIn Login`
- **LinkedIn 页面（LinkedIn Page）：** 选择一个或填 “None”
- **应用 Logo（App logo）：** 100×100+ PNG/JPG
- **隐私政策 URL / 商务邮箱（Privacy Policy URL / Business Email）：** 必须有效且可公开访问

(5) 点击 **Create app（创建应用）**

> **默认是开发模式（Development Mode）** → 可立即测试  
> `openid`, `profile`, `email` 登录，无需发布（publishing）

#### 2) 启用 OpenID Connect (OIDC)

(1) 进入 **Products（产品）** 标签页

(2) 找到 **Sign In with LinkedIn using OpenID Connect**

(3) 点击 **Add product（添加产品）** → 一般会立即通过审批

(4) 在 **Auth（认证）** 标签页中会出现 OIDC 相关设置

> **必需的 OIDC 作用域（Scopes）**
>
> - `openid` → 返回 ID token
> - `profile` → 姓名、头像、头衔等
> - `email` → 邮件地址

#### 3) OAuth 2.0 设置（Auth 标签）

(1) 前往 **Auth → OAuth 2.0 settings**

(2) 在 **Redirect URLs** 中添加：

→ `https://{domain}/?social_login=linkedin`

(3) 要求 **完全精确匹配**（协议、子域名、路径、结尾斜杠、查询字符串）

(4) 如有需要，可注册多条重定向地址：

- 本地环境：`https://localhost:3000/?social_login=linkedin`
- 测试环境：`https://staging.example.com/?social_login=linkedin`
- 生产环境：`https://example.com/?social_login=linkedin`

(5) 点击 **Save（保存）**

#### 4) 获取 Client ID / Client Secret

(1) 在 **Auth**（认证）标签页中找到：

- **Client ID**
- **Client Secret**

(2) 打开 WordPress 后台 → **SESLP 设置 → LinkedIn**

(3) 将两项粘贴进去 → 点击 **保存（Save）**

(4) 在前端使用 **LinkedIn 登录按钮** 进行测试

> **安全提示：**
>
> - 切勿泄露 Client Secret
> - 如发现泄露风险，请使用 **Regenerate secret（重新生成密钥）**

#### 5) 作用域

| 作用域    | 说明             | 备注     |
| --------- | ---------------- | -------- |
| `openid`  | ID 令牌          | **必需** |
| `profile` | 姓名、头像、标题 | **必需** |
| `email`   | 邮箱             | **必需** |

> **传统作用域（`r_liteprofile`, `r_emailaddress`）**
>
> - 将在 2024 年之后**废弃（Deprecated）**
> - **新应用已无法使用**

#### 6) 故障排查

(1) **redirect_uri_mismatch**

→ 控制台中登记的 Redirect URI 与实际请求 URI 只要有**哪怕一点点差异**（协议、子域名、路径、斜杠、查询参数等），就会出现此错误 → 请确保 **100% 完全匹配**

(2) **invalid_client**

→ Client ID / Client Secret 填写错误，或应用处于未激活 / 已删除状态 → 请重新检查或重新生成凭证

(3) **email NULL**

→ 用户拒绝授权，或未请求 `email` 作用域 → 请在同意屏幕中清楚说明邮箱用途，并确认已包含 `email` 作用域

(4) **insufficient_scope**

→ 请求的作用域尚未被批准 → 请确认已启用 OIDC 并仅使用允许的作用域

(5) **OIDC not enabled**

→ 未在 Products 中添加 **Sign In with LinkedIn using OpenID Connect** 产品 → 请先启用该产品

> **日志路径：**
>
> - `/wp-content/seslp-logs/seslp-debug.log`
> - `/wp-content/debug.log`

#### 7) 总结检查清单

- [ ] 应用已创建
- [ ] 已添加 **OpenID Connect** 产品
- [ ] Redirect URI 已按要求精确登记
- [ ] Client ID / Secret 已保存到 SESLP
- [ ] 使用的作用域为 `openid profile email`（不再使用传统作用域）
- [ ] 已在 HTTPS 前端完成登录测试

---

> **说明：**
>
> - SESLP 完整支持 **OIDC 登录流程（OIDC flow）**。
> - 旧版的传统 OAuth 2.0 方式**已不再支持**。
> - 新的集成应始终使用 **OpenID Connect** 标准。

</details>

---

<details>
  <summary><strong>Naver</strong></summary>

> - **Redirect URI：** `https://{domain}/?social_login=naver`
> - **推荐作用域：** `name`, `email`
> - ※ Naver 使用 **Naver Login(네아로)** API，**必须 HTTPS**

---

#### 1) 应用注册（Application Registration）

(1) 前往 **Naver Developer Center**

→ [https://developers.naver.com/apps/](https://developers.naver.com/apps/)

(2) 使用 Naver 账号登录

(3) 点击 **Application → Register Application（注册应用）**

(4) 填写必填信息：

- **Application Name（应用名称）：** 例如 `MySite Naver Login`
- **API Usage（API 使用）：** 选择 `Naver Login (네아로)`
- **Add Environment → Web（添加环境 → Web）**
- **Service URL：** `https://example.com`
- **Callback URL：** `https://example.com/?social_login=naver`

(5) 同意条款 → 点击 **Register（注册）**

> **注意：**
>
> - **必须使用 HTTPS** → 不允许使用 HTTP
> - **子域名必须分别单独注册**

#### 2) 获取 Client ID / Client Secret

(1) 进入 **My Applications（我的应用）**

(2) 点击对应应用 → 复制 **Client ID** 和 **Client Secret**

#### 3) WordPress（插件）设置

(1) WP 后台 → **SESLP Settings → Naver**

(2) 粘贴 **Client ID / Client Secret**

(3) 确保 **Redirect URI** 与注册的一致：`https://{domain}/?social_login=naver`

(4) 点击 **Save（保存）** → 在前端使用 **Naver 登录按钮** 进行测试

#### 4) 权限与数据提供

| 信息       | 作用域   | 备注       |
| ---------- | -------- | ---------- |
| 姓名       | `name`   | 默认       |
| 邮箱       | `email`  | 默认       |
| 性别、生日 | 单独申请 | **需审核** |

> - 用户可以在同意界面中 **同意 / 拒绝**
> - 如果用户拒绝邮箱权限 → `email = null` → 使用 **基于用户 ID 的关联方式**
> - 敏感数据需通过 **Naver 应用审核**

#### 5) 故障排查

(1) **Redirect URI mismatch（重定向 URI 不匹配）**

→ 只要有**一丁点差异** → 必须确保 **100% 完全一致**

(2) **HTTP error**

→ 必须使用 **HTTPS**

(3) **子域名错误（Subdomain error）**

→ 每个子域名都必须 **单独注册**

(4) **email 为 NULL**

→ 用户拒绝或邮箱为私密 → 准备基于 **用户 ID 的关联逻辑**

(5) **需要审核（Review needed）**

→ 基础登录：**无需审核**  
→ 额外数据：**需要审核**

> **日志（Logs）：**
>
> - `/wp-content/seslp-logs/seslp-debug.log`
> - `/wp-content/debug.log`

#### 6) 检查清单（Summary Checklist）

- [ ] 应用已在 Naver Developer Center 注册
- [ ] **Callback URL** 已完全精确登记
- [ ] 已使用 **HTTPS**
- [ ] 子域名（如有）已单独注册
- [ ] Client ID/Secret 已保存到 SESLP
- [ ] 已测试邮箱同意/拒绝逻辑
- [ ] 已在前端完成登录测试

---

> **注意（Note）：**
>
> - SESLP 完整支持 **Naver Login (네아로)**
> - 基础权限（`name`, `email`）**无需审核即可使用**

</details>

---

<details>
  <summary><strong>Kakao</strong></summary>

> - **Redirect URI：** `https://{domain}/?social_login=kakao`
> - **推荐作用域：** `profile_nickname`, `profile_image`, `account_email`
> - `account_email` 仅在 **实名认证或企业注册完成后** 可用
> - **必须 HTTPS**, **Client Secret 必须激活**

---

#### 1) 创建应用

(1) 访问 **Kakao Developers**

→ [https://developers.kakao.com/](https://developers.kakao.com/)

(2) 登录 → **我的应用 → 添加应用**

(3) 输入：

- 应用名称、公司名称
- 类别
- 同意运营政策

(4) **保存**

#### 2) 启用 Kakao 登录

(1) **产品设置 > Kakao 登录**

(2) **启用 Kakao 登录** → **开启**

(3) **注册重定向 URI**

- `https://{domain}/?social_login=kakao`
- **保存**

(4) 域名必须与 **平台注册域名完全一致**

#### 3) 同意项（作用域）设置

(1) **同意项**

(2) 添加并配置：

| 作用域             | 说明 | 同意类型  | 备注           |
| ------------------ | ---- | --------- | -------------- |
| `profile_nickname` | 昵称 | 必选/可选 | 基础           |
| `profile_image`    | 头像 | 必选/可选 | 基础           |
| `account_email`    | 邮箱 | **可选**  | **需实名认证** |

(3) 每项明确填写 **使用目的**

(4) **保存**

> 敏感作用域需 **实名认证**

#### 4) 注册 Web 平台

(1) **应用设置 > 平台**

(2) **注册 Web 平台**

(3) 站点域名：`https://{domain}`

(4) **保存** → 必须与重定向 URI 域名一致

#### 5) 安全 – 生成并激活 Client Secret

(1) **产品设置 > 安全**

(2) **使用 Client Secret** → **开启**

(3) **生成 Secret** → 复制值

(4) **激活状态** → **使用中**

(5) **保存**

> **生成后必须激活**

#### 6) 获取 REST API 密钥 (Client ID)

(1) **应用密钥**

(2) 复制 **REST API 密钥** → 作为 **Client ID**

#### 7) WordPress 设置

(1) WP 后台 → **SESLP 设置 → Kakao**

(2) **Client ID** = REST API 密钥  
 **Client Secret** = 生成的 Secret

(3) **保存**

(4) 使用 **Kakao 登录按钮** 测试

#### 8) 故障排查

(1) **redirect_uri_mismatch** → 必须 100% 匹配

(2) **invalid_client** → Secret 未激活或错误

(3) **email 为空** → 用户拒绝或未认证

(4) **域名不一致** → 平台 vs URI

(5) **HTTP 禁用** → **仅 HTTPS**

> **日志：**
>
> - `/wp-content/seslp-logs/seslp-debug.log`
> - `/wp-content/debug.log`

#### 9) 检查清单

- [ ] Kakao 登录已启用
- [ ] 重定向 URI 已注册
- [ ] Web 平台域名已注册
- [ ] 同意项已配置
- [ ] Client Secret 已生成 + 激活
- [ ] REST API 密钥 / Secret 已输入 SESLP
- [ ] 在 HTTPS 前端测试完成

</details>

---

<details>
  <summary><strong>LINE</strong></summary>

> - **Redirect URI：** `https://{domain}/?social_login=line`
> - **必需：** 启用 OpenID Connect，**申请并获批邮件地址权限**
> - **推荐作用域：** `openid`, `profile`, `email`
> - **必须 HTTPS**，邮件收集需**提前审批**

---

#### 1) 创建 Provider 和 Channel

(1) 访问 **LINE Developers Console**

→ [https://developers.line.biz/console/](https://developers.line.biz/console/)

(2) 使用 **LINE 企业账号**登录（个人账号不可用）

(3) 点击 **创建新 Provider** → 输入名称 → **Create**

(4) 在 Provider 下 → **Channels** 标签页

(5) 选择 **创建 LINE Login 频道**

(6) 配置：

- **频道类型：** `LINE Login`
- **Provider：** 选择已创建
- **地区：** 目标国家（例如 `South Korea`, `Japan`）
- **名称 / 描述 / 图标：** 显示在用户同意界面

(7) 同意条款 → **创建**

#### 2) 启用 OpenID Connect 并申请邮件权限

(1) 菜单 **OpenID Connect**

(2) 在 **Email address permission** 旁点击 **Apply**

(3) 填写申请：

- **隐私政策 URL**（需公开可访问）
- **隐私政策截图**
- 勾选同意 → **Submit**

(4) **`email` 作用域仅在审批通过后生效**  
 → 审批通常需 1–3 个工作日

#### 3) 注册 Callback URL 并发布频道

(1) 菜单 **LINE Login**

(2) 输入 **Callback URL**：  
 → `https://{domain}/?social_login=line`

(3) **必须完全一致**：

- 协议：`https://`（**HTTP 不可用**）
- 域名、路径、查询参数 **100% 一致**

(4) 点击 **保存**

(5) 将频道状态改为 **Published**

- **Development：** 仅测试
- **Published：** 正式上线

#### 4) 获取 Channel ID / Secret

(1) 频道页面顶部或 **Basic settings**

(2) **Channel ID** → SESLP **Client ID**  
 **Channel Secret** → SESLP **Client Secret**

#### 5) WordPress 设置

(1) WP 后台 → **SESLP 设置 → LINE**

(2) **Client ID** ← Channel ID  
 **Client Secret** ← Channel Secret

(3) **保存**

(4) 使用前端 **LINE 登录按钮** 进行实际测试

#### 6) 故障排查

(1) **redirect_uri_mismatch** → 任何细微差异都会出错 → **100% 一致**

(2) **invalid_client** → Secret 输入错误或 **未发布**

(3) **email 为空** → **邮件权限未获批** 或用户拒绝

(4) **HTTP 禁用** → **仅支持 HTTPS**（本地 `https://localhost` 可）

(5) **Development 模式限制** → 仅测试账号可登录

> **日志：**
>
> - `/wp-content/seslp-logs/seslp-debug.log`
> - `/wp-content/debug.log`

#### 7) 检查清单

- [ ] 使用企业账号创建 **Provider + LINE Login 频道**
- [ ] **邮件权限申请并获批**
- [ ] **Callback URL** 完全一致注册
- [ ] **使用 HTTPS**，状态为 **Published**
- [ ] Channel ID/Secret 已输入 SESLP
- [ ] 前端完成实际登录测试

> **备注：** SESLP 完全支持
>
> - **LINE Login v2.1 + OpenID Connect**。
> - **收集邮件必须提前审批**。

</details>
