# Simple Easy Social Login

Simple Easy Social Login 是一款轻量且用户友好的 WordPress 插件，可为您的网站添加快速、无缝的社交登录功能。

它支持 **Google、Facebook、LinkedIn（免费）**，以及 **Naver、Kakao、Line（高级版）**，  
专为面向亚洲（韩国、日本、中国）的网站设计，同时也适用于欧洲和南美地区的用户。

该插件可与 WordPress 默认的登录和注册页面无缝集成，  
同时支持 WooCommerce 的登录和注册表单。  
社交平台的个人头像可自动同步为 WordPress 用户资料头像。

此外，本插件基于 **可扩展的 Provider 架构** 构建，  
在需要时，可通过独立的 Add-on 插件形式添加新的 OAuth Provider。

---

## ✨ 功能特色

- Google 登录（免费）
- Facebook 登录（免费）
- LinkedIn 登录（免费）
- Naver 登录（高级版）
- Kakao 登录（高级版）
- Line 登录（高级版）
- 用户头像自动同步
- 通过电子邮箱自动关联已有的 WordPress 用户
- 支持登录 / 登出 / 注册后的自定义跳转 URL
- 简洁直观的管理后台，用于配置各 Provider
- 支持短代码： [se_social_login]
- 自动显示于 WordPress 登录和注册表单
- 支持 WooCommerce 登录和注册表单（可选）
- 轻量级结构，遵循 WordPress 编码规范
- 不创建不必要的数据库表
- 支持通过 Add-on 插件扩展新的 OAuth Provider 的 Provider 系统

---

## 🐞 调试日志

SESLP 内置调试日志系统，可用于诊断 OAuth 及社交登录相关问题。

您可以在 WordPress 管理后台中查看详细的日志说明：
**SESLP → Guides → Debug Log & Troubleshooting**

日志文件生成位置：

- `/wp-content/SESLP-debug.log`
- `/wp-content/debug.log`（启用 `WP_DEBUG_LOG` 时）

---

## 🚀 安装方法

1. 将插件上传至 `/wp-content/plugins/simple-easy-social-login/` 目录。
2. 在 WordPress 管理后台中，通过 **插件 → 已安装插件** 启用插件。
3. 前往 **设置 → Simple Easy Social Login**。
4. 输入各社交登录 Provider 的 Client ID 和 Client Secret。
5. 保存设置。
6. 确认前端页面中社交登录按钮是否正常显示。

---

## ❓ 常见问题

### 是否支持 WooCommerce？

是的。本插件可与 WooCommerce 的登录和注册表单集成使用。

### 是否只可以使用 Google 登录？

可以。每个 Provider 都可以单独启用或禁用。

### 什么时候需要高级版许可证？

使用 **Naver、Kakao、Line** 登录时需要高级版许可证。  
Google、Facebook 和 LinkedIn 可免费使用。

### 是否提供短代码？

是的。您可以使用以下短代码在任意位置插入社交登录按钮： [se_social_login]

### 是否会自动导入用户头像？

是的。对于 Google、Facebook 等部分 Provider，可自动获取并同步用户的个人头像作为 WordPress 用户头像。

---

## 🖼 截图

1. 管理后台设置页面
2. 社交登录按钮示例
3. 高级 Provider（Naver / Kakao / Line）
4. 与 WordPress 登录表单的集成示例

---

## 📝 更新日志（Changelog）

### 1.9.8

- 修复了 `SESLP_Avatar::resolve_user()` 中的致命类型错误，确保返回值为 `WP_User|null`
- 改进了头像回退处理：
  - 当社交资料头像缺失或无效时，安全地使用 WordPress 默认头像
  - 防止头像图片损坏（例如 LinkedIn 头像问题）
- 与头像显示相关的细微稳定性改进

### 1.9.7

- 在 README 中新增调试日志说明部分
- 将详细的调试日志指南整合到管理后台指南中（多语言）
- 统一日志文件路径说明（`/wp-content/SESLP-debug.log`）
- 优化并整理整体文档结构

### 1.9.6

- 改进设置页面的可用性
- 新增 Secret 密钥显示/隐藏切换功能
- 修复与 WordPress 核心样式的冲突问题
- 改进 Pro / Max 方案检测逻辑

### 1.9.5

- 大规模重构
- 统一 Helpers 并改进 Provider 架构
- 整理设置界面
- 提升稳定性和可维护性

### 1.9.3

- 更新 Guides 的翻译内容
- 在设置页面中新增短代码显示

### 1.9.2

- 整理内部结构
- 新增 Guides 加载器类
- 重构模板结构
- 提升设置及 CSS 加载器的稳定性

### 1.9.1

- 新增管理员指南页面
- 基于 Markdown 的多语言文档渲染（采用 Parsedown）
- 改进 UI 样式

### 1.9.0

- 大规模重构的准备阶段
- 扩展 i18n 辅助功能
- 改进安全格式化及日志结构

### 1.7.23

- 翻译更新

### 1.7.22

- 改进调试信息，显示之前登录的 Provider

### 1.7.21

- 当检测到相同邮箱重复注册时，在错误信息中显示 Provider 名称
- 通过 JavaScript 在 10 秒后自动隐藏错误信息

### 1.7.19

- 防止使用相同邮箱创建重复账号
- 改进 OAuth 流程：
  - `get_access_token()`
  - `get_user_info()`
  - `create_or_link_user()`

### 1.7.18

- 移除 Google Client ID / Secret 字段的工具提示
- 优化代码结构
- 移除 Line 登录按钮中的 “(Email required)” 文本

### 1.7.17

- 修复 Line 登录相关问题：
  - 防止重新登录时创建重复用户
  - 修复 `/complete-profile` 页面重复出现的问题
  - 允许更新邮箱地址，解决 “Invalid request” 错误
- 使用 `SESLP_Logger` 统一调试日志

### 1.7.16

- 在调试日志中对许可证密钥进行脱敏处理（例如：abc\*\*\*\*123）
- 添加 `wp_options` 检查指南以便调试
- 当日志写入失败时显示管理员通知

### 1.7.15

- 修复调试日志写入失败的问题
- 使用 WordPress 本地时区记录时间戳
- 在保存设置时新增调试日志

### 1.7.5

- 应用最新的安全补丁
- 性能优化并提升用户体验

### 1.7.0

- 改进社交登录按钮同步机制
- 加强安全性并修复错误

### 1.7.3

- 改进调试系统
- 新增专用 debug 目录

### 1.6.0

- 在选择 Plus / Premium 时恢复许可证密钥区域的显示逻辑

### 1.5.0

- 注册 `seslp_license_type` 选项
- 修复保存设置时许可证类型重置为 Free 的问题

### 1.4.0

- 使用 `admin_enqueue_scripts` 修复后台 `style.css` 加载问题

### 1.3.0

- 改进单选按钮 UI
- 将内联 CSS 移至 `style.css`

### 1.2.0

- 新增许可证类型选择（Free / Plus / Premium）
- 改进设置页面 UI 布局

### 1.1.0

- 新增多语言支持及翻译文件加载功能
- 改进用户认证逻辑

### 1.0.0

- 初始版本发布
- 新增 Google、Facebook、Naver、Kakao、Line、Weibo 社交登录

---

## 📄 许可证

GPLv2 or later
https://www.gnu.org/licenses/gpl-2.0.html
