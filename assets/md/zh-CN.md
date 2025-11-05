# Simple Easy Social Login (SESLP) — 社交登录指南（简体中文）

> 本文档说明如何在 **Simple Easy Social Login (SESLP)** 插件中  
> 配置各个社交登录提供方（Google、Facebook、LinkedIn、Naver、Kakao、LINE）。  
> 所有登录均基于 **OAuth 2.0 / OpenID Connect (OIDC)**。  
> 您需要在各提供方的开发者控制台中创建应用（Client），并在 SESLP 中填写 **Client ID / Client Secret**。

---

## 🔧 通用设置指南

- **重定向 URI 规则：**  
  `https://{你的域名}/?social_login={provider}`  
  示例：

  - Google → `https://example.com/?social_login=google`
  - Facebook → `https://example.com/?social_login=facebook`
  - LinkedIn → `https://example.com/?social_login=linkedin`
  - Naver → `https://example.com/?social_login=naver`
  - Kakao → `https://example.com/?social_login=kakao`
  - LINE → `https://example.com/?social_login=line`

- **必须使用 HTTPS**  
  大多数提供方要求 HTTPS，并拒绝 `http://` 重定向。

- **严格匹配**  
  控制台中注册的 Redirect URI 必须与 SESLP 实际发送的地址 **100% 完全一致**  
  （包括协议、子域名、路径、结尾斜杠和查询参数）。

- **邮箱可能不可用**  
  一些提供方允许用户拒绝共享邮箱。SESLP 可使用提供方稳定的用户 ID 来关联账户。

- **日志查看路径**
  - `/wp-content/seslp-logs/seslp-debug.log`
  - `/wp-content/debug.log`（`WP_DEBUG_LOG = true`）

---

## 🌍 各提供方指南

> 展开以下提供方查看对应说明。  
> Google 已完整展示，其他部分请根据需要添加。

---

<details open>
  <summary><strong>Google</strong></summary>

> **推荐作用域：** `openid email profile`  
> **Redirect URI：** `https://{domain}/?social_login=google`

---

### 1) 准备工作

- **必须使用 HTTPS**。
- Redirect URI 必须 **100% 完全匹配** 控制台中的值。
- 测试模式下仅 **测试用户** 可登录。
- 如使用隐私/服务 URL，请登记 **Authorized domains** 并验证域名所有权。

### 2) 项目与授权屏幕设置

1. 登录 **Google Cloud Console**
   - <https://console.cloud.google.com/apis/credentials>
2. 创建或选择项目。
3. 打开 **APIs & Services → OAuth consent screen**。
4. 用户类型：**External（外部）**。
5. 填写应用信息。
6. 配置 **App domain** 并保存。
7. 设定 **Scopes：** `openid email profile`。
8. 添加 **测试用户** → 保存。

> 仅使用基础作用域通常可 **无需审核直接发布**。

### 3) 创建 OAuth 客户端（Web 应用）

1. 打开 **APIs & Services → Credentials**。
2. 选择 **+ Create Credentials → OAuth client ID**。
3. 类型：`Web application`。
4. 名称：`SESLP – Front`。
5. **Authorized redirect URIs：**
   - `https://{domain}/?social_login=google`
6. 复制 **Client ID / Secret**。

### 4) WordPress 插件设置

1. WP 后台 → **SESLP Settings → Google**。
2. 粘贴 **Client ID / Secret** → 保存。
3. 前端测试 Google 登录。

### 5) 切换生产环境

1. 检查发布状态。
2. 删除无关作用域。
3. 使用敏感作用域需审核。

### 6) 常见错误

- **redirect_uri_mismatch** – URI 不一致。
- **access_denied** – 浏览器限制。
- **invalid_client** – 凭证错误。
- **邮箱为空** – 检查作用域与隐私设置。

</details>

---

<details>
  <summary><strong>Facebook (Meta)</strong></summary>

> **Redirect URI:** `https://{domain}/?social_login=facebook`  
> **推荐权限:** `public_profile`, `email`  
> ※ Facebook 不使用 `openid`。

---

### 1) 创建应用并添加产品

1. 打开 **Meta for Developers** → 登录
2. 点击 **Create App** → 选择类型（如 Consumer）→ 创建应用
3. 左侧菜单 → **Products → Facebook Login**
4. 进入 **Settings** → 检查以下：
   - **Client OAuth Login:** 开启
   - **Web OAuth Login:** 开启
   - **Valid OAuth Redirect URIs:**
     - 添加 `https://{domain}/?social_login=facebook`
   - (可选) **Enforce HTTPS:** 推荐开启

### 2) 应用基本设置

- **App Domains:** `example.com`
- **Privacy Policy URL:** 可公开访问
- **Terms of Service URL:** 可公开访问
- **User Data Deletion:** 提供删除数据说明或接口
- **分类 / 图标:** 设置并保存

### 3) 权限与审核

- 基本权限：**`public_profile`**，可选：**`email`**
- 通常 **`email` 可直接使用**
- 高级权限需 **App Review** 与 **Business Verification**

### 4) 切换模式（开发 → 生产）

- 将模式从 **Development → Live**
- 检查：
  - [ ] 政策/条款/删除 URL 已公开
  - [ ] Redirect URI 精确
  - [ ] 权限最少化
  - [ ] 审核完成

### 5) WordPress 设置 (SESLP)

1. WP 后台 → **SESLP 设置 → Facebook**
2. 输入 **App ID / Secret** → 保存
3. 测试 Facebook 登录按钮

### 6) 故障排查

- **redirect_uri 错误** → 检查 URI 是否一致
- **email 为空** → 用户无邮箱或未公开
- **权限错误** → 需要审核
- **无法上线** → URL 缺失或非公开

</details>

---

<details>
  <summary><strong>LinkedIn</strong></summary>

> **Redirect URI：** `https://{domain}/?social_login=linkedin`  
> **必需设置：** 启用 OpenID Connect (OIDC)  
> **推荐作用域：** `openid`, `profile`, `email`

---

### 1) 创建应用

1. 访问 **LinkedIn Developers Console**  
   → [链接](https://www.linkedin.com/developers/apps)
2. 登录
3. 点击 **Create app**
4. 填写必填项：
   - 应用名、页面、Logo、隐私政策、邮箱
5. 创建

> 开发模式 → 可立即测试

---

### 2) 启用 OIDC

1. **Products** → 添加 **Sign In with LinkedIn using OpenID Connect**

---

### 3) OAuth 设置

1. **Auth → OAuth 2.0 settings**
2. 添加重定向 URI：`https://{domain}/?social_login=linkedin`
3. 必须完全一致
4. 保存

---

### 4) Client ID / Secret

1. 在 **Auth** 获取
2. SESLP → LinkedIn → 粘贴 → 保存
3. 前端测试

---

### 5) 作用域

| 作用域    | 说明             | 备注     |
| --------- | ---------------- | -------- |
| `openid`  | ID 令牌          | **必需** |
| `profile` | 姓名、头像、标题 | **必需** |
| `email`   | 邮箱             | **必需** |

> 旧作用域 **已弃用**

---

### 6) 故障排查

- **redirect_uri_mismatch** → URI 完全一致
- **invalid_client** → ID/Secret 错误
- **email 为空** → 作用域缺失或用户拒绝

---

### 7) 检查清单

- [ ] 应用创建
- [ ] OIDC 启用
- [ ] 重定向 URI 注册
- [ ] ID/Secret 输入
- [ ] HTTPS 测试

</details>

---

<details>
  <summary><strong>Naver</strong></summary>

> **Redirect URI：** `https://{domain}/?social_login=naver`  
> **推荐作用域：** `name`, `email`  
> ※ Naver 使用 **네아로 (Naver Login)**，**必须 HTTPS**

---

### 1) 应用注册

1. 访问 **Naver Developer Center**  
   → [链接](https://developers.naver.com/apps/)
2. 登录
3. **应用注册**
4. 填写：
   - 应用名、API: `Naver Login`
   - Web: 服务 URL、**Callback URL**
5. **注册**

> HTTPS 必填，子域名需单独注册

---

### 2) Client ID / Secret

1. **我的应用** → 复制

---

### 3) WordPress 设置

1. WP 后台 → **SESLP → Naver**
2. 粘贴 ID/Secret
3. 确认 URI 完全一致
4. **保存** → 测试

---

### 4) 权限

| 信息       | 作用域   | 备注       |
| ---------- | -------- | ---------- |
| 姓名       | `name`   | 默认       |
| 邮箱       | `email`  | 默认       |
| 性别、生日 | 单独申请 | **需审核** |

> 邮箱拒绝 → `null`

---

### 5) 故障排查

- **redirect_uri_mismatch** → 完全一致
- **HTTP 禁用** → 仅 HTTPS
- **子域名** → 单独注册

---

### 6) 检查清单

- [ ] 应用注册
- [ ] Callback URL 准确
- [ ] HTTPS
- [ ] ID/Secret 输入
- [ ] 邮箱同意测试

</details>

---

<details>
  <summary><strong>Kakao</strong></summary>

> **Redirect URI：** `https://{domain}/?social_login=kakao`  
> **推荐作用域：** `profile_nickname`, `profile_image`, `account_email`  
> ※ `account_email` 仅在 **实名认证或企业注册完成后** 可用  
> ※ **必须 HTTPS**, **Client Secret 必须激活**

---

### 1) 创建应用

1. 访问 **Kakao Developers**  
   → [https://developers.kakao.com/](https://developers.kakao.com/)
2. 登录 → **我的应用 → 添加应用**
3. 输入：
   - 应用名称、公司名称
   - 类别
   - 同意运营政策
4. **保存**

---

### 2) 启用 Kakao 登录

1. **产品设置 > Kakao 登录**
2. **启用 Kakao 登录** → **开启**
3. **注册重定向 URI**
   - `https://{domain}/?social_login=kakao`
   - **保存**
4. 域名必须与 **平台注册域名完全一致**

---

### 3) 同意项（作用域）设置

1. **同意项**
2. 添加并配置：

| 作用域             | 说明 | 同意类型  | 备注           |
| ------------------ | ---- | --------- | -------------- |
| `profile_nickname` | 昵称 | 必选/可选 | 基础           |
| `profile_image`    | 头像 | 必选/可选 | 基础           |
| `account_email`    | 邮箱 | **可选**  | **需实名认证** |

3. 每项明确填写 **使用目的**
4. **保存**

> 敏感作用域需 **实名认证**

---

### 4) 注册 Web 平台

1. **应用设置 > 平台**
2. **注册 Web 平台**
3. 站点域名：`https://{domain}`
4. **保存** → 必须与重定向 URI 域名一致

---

### 5) 安全 – 生成并激活 Client Secret

1. **产品设置 > 安全**
2. **使用 Client Secret** → **开启**
3. **生成 Secret** → 复制值
4. **激活状态** → **使用中**
5. **保存**
   > **生成后必须激活**

---

### 6) 获取 REST API 密钥 (Client ID)

1. **应用密钥**
2. 复制 **REST API 密钥** → 作为 **Client ID**

---

### 7) WordPress 设置

1. WP 后台 → **SESLP 设置 → Kakao**
2. **Client ID** = REST API 密钥  
   **Client Secret** = 生成的 Secret
3. **保存**
4. 使用 **Kakao 登录按钮** 测试

---

### 8) 故障排查

- **redirect_uri_mismatch** → 必须 100% 匹配
- **invalid_client** → Secret 未激活或错误
- **email 为空** → 用户拒绝或未认证
- **域名不一致** → 平台 vs URI
- **HTTP 禁用** → **仅 HTTPS**

> **日志：**  
> `/wp-content/seslp-logs/seslp-debug.log`  
> `/wp-content/debug.log`

---

### 9) 检查清单

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

> **Redirect URI：** `https://{domain}/?social_login=line`  
> **必需：** 启用 OpenID Connect，**申请并获批邮件地址权限**  
> **推荐作用域：** `openid`, `profile`, `email`  
> ※ **必须 HTTPS**，邮件收集需**提前审批**

---

### 1) 创建 Provider 和 Channel

1. 访问 **LINE Developers Console**  
   → [https://developers.line.biz/console/](https://developers.line.biz/console/)
2. 使用 **LINE 企业账号**登录（个人账号不可用）
3. 点击 **创建新 Provider** → 输入名称 → **Create**
4. 在 Provider 下 → **Channels** 标签页
5. 选择 **创建 LINE Login 频道**
6. 配置：
   - **频道类型：** `LINE Login`
   - **Provider：** 选择已创建
   - **地区：** 目标国家（例如 `South Korea`, `Japan`）
   - **名称 / 描述 / 图标：** 显示在用户同意界面
7. 同意条款 → **创建**

---

### 2) 启用 OpenID Connect 并申请邮件权限

1. 菜单 **OpenID Connect**
2. 在 **Email address permission** 旁点击 **Apply**
3. 填写申请：
   - **隐私政策 URL**（需公开可访问）
   - **隐私政策截图**
   - 勾选同意 → **Submit**
4. **`email` 作用域仅在审批通过后生效**  
   → 审批通常需 1–3 个工作日

---

### 3) 注册 Callback URL 并发布频道

1. 菜单 **LINE Login**
2. 输入 **Callback URL**：  
   → `https://{domain}/?social_login=line`
3. **必须完全一致**：
   - 协议：`https://`（**HTTP 不可用**）
   - 域名、路径、查询参数 **100% 一致**
4. 点击 **保存**
5. 将频道状态改为 **Published**
   - **Development：** 仅测试
   - **Published：** 正式上线

---

### 4) 获取 Channel ID / Secret

1. 频道页面顶部或 **Basic settings**
2. **Channel ID** → SESLP **Client ID**  
   **Channel Secret** → SESLP **Client Secret**

---

### 5) WordPress 设置

1. WP 后台 → **SESLP 设置 → LINE**
2. **Client ID** ← Channel ID  
   **Client Secret** ← Channel Secret
3. **保存**
4. 使用前端 **LINE 登录按钮** 进行实际测试

---

### 6) 故障排查

- **redirect_uri_mismatch** → 任何细微差异都会出错 → **100% 一致**
- **invalid_client** → Secret 输入错误或 **未发布**
- **email 为空** → **邮件权限未获批** 或用户拒绝
- **HTTP 禁用** → **仅支持 HTTPS**（本地 `https://localhost` 可）
- **Development 模式限制** → 仅测试账号可登录

> **日志：**  
> `/wp-content/seslp-logs/seslp-debug.log`  
> `/wp-content/debug.log`

---

### 7) 检查清单

- [ ] 使用企业账号创建 **Provider + LINE Login 频道**
- [ ] **邮件权限申请并获批**
- [ ] **Callback URL** 完全一致注册
- [ ] **使用 HTTPS**，状态为 **Published**
- [ ] Channel ID/Secret 已输入 SESLP
- [ ] 前端完成实际登录测试

---

> **备注：** SESLP 完全支持 **LINE Login v2.1 + OpenID Connect**。  
> **收集邮件必须提前审批**。

</details>

---

## 📋 摘要

| 计划 | 提供方       | 必需 / 推荐作用域                                    | Redirect URI 示例                         | 备注                   |
| ---- | ------------ | ---------------------------------------------------- | ----------------------------------------- | ---------------------- |
| 免费 | **Google**   | `openid email profile`                               | `https://{domain}/?social_login=google`   | 需要外部同意页面       |
| 免费 | **Facebook** | `public_profile`, `email`                            | `https://{domain}/?social_login=facebook` | 不使用 `openid`        |
| 免费 | **LinkedIn** | `openid profile email`                               | `https://{domain}/?social_login=linkedin` | 完整迁移到 OIDC        |
| 付费 | **Naver**    | `email`, `name`                                      | `https://{domain}/?social_login=naver`    | 使用 “Naver Login” API |
| 付费 | **Kakao**    | `profile_nickname`, `profile_image`, `account_email` | `https://{domain}/?social_login=kakao`    | 需要 Client Secret     |
| 付费 | **LINE**     | `openid profile email`                               | `https://{domain}/?social_login=line`     | 必须为 Published 状态  |
