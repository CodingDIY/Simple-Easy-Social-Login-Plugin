# Simple Easy Social Login (SESLP) — Guia de login social (Português – Brasil)

> Este documento explica como configurar cada provedor de login social  
> (Google, Facebook, LinkedIn, Naver, Kakao, LINE) no plugin **Simple Easy Social Login (SESLP)**.  
> Todos os logins são baseados em **OAuth 2.0 / OpenID Connect (OIDC)**.  
> Você deve criar um aplicativo (cliente) no console de cada provedor e inserir o **Client ID / Client Secret** no SESLP.

---

## 🔧 Guia de configuração comum

- **Regra da Redirect URI:**  
  `https://{seu-dominio}/?social_login={provider}`  
  Exemplos:

  - Google → `https://example.com/?social_login=google`
  - Facebook → `https://example.com/?social_login=facebook`
  - LinkedIn → `https://example.com/?social_login=linkedin`
  - Naver → `https://example.com/?social_login=naver`
  - Kakao → `https://example.com/?social_login=kakao`
  - LINE → `https://example.com/?social_login=line`

- **HTTPS obrigatório**  
  A maioria dos provedores requer HTTPS e rejeita redirecionamentos `http://`.

- **Correspondência exata**  
  A Redirect URI registrada no console deve corresponder **100%** ao valor enviado pelo SESLP  
  (protocolo, subdomínio, caminho, barra final e parâmetros de consulta).

- **E-mail pode não estar disponível**  
  Alguns provedores permitem que o usuário negue o compartilhamento do e-mail. O SESLP pode vincular contas usando IDs estáveis do provedor.

- **Onde verificar os logs**
  - `/wp-content/seslp-logs/seslp-debug.log`
  - `/wp-content/debug.log` (`WP_DEBUG_LOG = true`)

---

## 🌍 Guias por provedor

> Expanda cada provedor abaixo e cole o guia correspondente em português quando estiver pronto.

---

<details open>
  <summary><strong>Google</strong></summary>

> **Escopos recomendados:** `openid email profile`  
> **Redirect URI:** `https://{dominio}/?social_login=google`

---

### 1) Preparação

- **HTTPS obrigatório/recomendado**.
- Redirect URI deve corresponder **100%** ao valor registrado.
- Apenas **usuários de teste** podem logar no modo teste.
- Registre **Authorized domains** e verifique o domínio, se necessário.

### 2) Configurar projeto e tela de consentimento

1. Acesse **Google Cloud Console**
   - <https://console.cloud.google.com/apis/credentials>
2. Crie ou selecione um projeto.
3. **APIs & Services → OAuth consent screen**.
4. **User Type:** External.
5. Preencha as informações do app.
6. Configure URLs e domínio autorizado.
7. **Scopes:** `openid email profile`.
8. Adicione **Test users** → Salvar.

> Usando apenas escopos básicos, a publicação é geralmente **sem revisão**.

### 3) Criar cliente OAuth (Web)

1. **APIs & Services → Credentials**.
2. **+ Create Credentials → OAuth client ID**.
3. Tipo: `Web application`.
4. Nome: `SESLP – Front`.
5. **Authorized redirect URIs:**
   - `https://{dominio}/?social_login=google`
6. Copie **Client ID / Secret**.

### 4) Configurar no WordPress

1. WP Admin → **SESLP Settings → Google**.
2. Cole **Client ID / Secret** → Salvar.
3. Teste com o botão Google.

### 5) Produção

1. Verifique o status de publicação.
2. Revise escopos e informações.
3. Envie para revisão se necessário.

### 6) Erros comuns

- **redirect_uri_mismatch** – URIs diferentes.
- **access_denied** – restrição de navegador.
- **invalid_client** – credenciais erradas.
- **Email vazio** – verificar escopos e privacidade.

</details>

---

<details>
  <summary><strong>Facebook (Meta)</strong></summary>

> **Redirect URI:** `https://{dominio}/?social_login=facebook`  
> **Permissões recomendadas:** `public_profile`, `email`  
> ※ O Facebook não usa `openid`.

---

### 1) Criar aplicativo e adicionar produto

1. Vá para **Meta for Developers** → Faça login
2. **Criar App** → Tipo geral (Consumer) → Criar
3. Menu lateral → **Produtos → Facebook Login**
4. **Configurações** → Verifique:
   - **Client OAuth Login:** Ativo
   - **Web OAuth Login:** Ativo
   - **Valid OAuth Redirect URIs:**
     - Adicione `https://{dominio}/?social_login=facebook`
   - (Opcional) **Aplicar HTTPS:** Recomendado

### 2) Configuração básica

- **App Domains:** `example.com`
- **Privacy Policy URL:** Página pública
- **Terms of Service URL:** Página pública
- **User Data Deletion:** URL ou endpoint de exclusão
- **Categoria / Ícone:** Configurar → Salvar

### 3) Permissões e revisão

- Básicas: **`public_profile`**, opcional: **`email`**
- **`email`** geralmente sem revisão
- Permissões avançadas exigem **App Review** e **Business Verification**

### 4) Modo (Desenvolvimento → Produção)

- Alterar para **Live**
- Antes de publicar:
  - [ ] URLs de política/termos/deleção
  - [ ] URI correta
  - [ ] Permissões mínimas
  - [ ] Revisão concluída

### 5) Configuração no WordPress

1. WP Admin → **SESLP Settings → Facebook**
2. Inserir **App ID / Secret** → Salvar
3. Testar botão do Facebook

### 6) Solução de problemas

- **redirect_uri erro** → Verifique URI exata
- **email null** → E-mail privado ou ausente
- **Erro de permissão** → Requer revisão
- **Live bloqueado** → URLs ausentes/privadas

</details>

---

<details>
  <summary><strong>LinkedIn</strong></summary>

> **Redirect URI:** `https://{dominio}/?social_login=linkedin`  
> **Configuração obrigatória:** Ativar OpenID Connect (OIDC)  
> **Escopos recomendados:** `openid`, `profile`, `email`

---

### 1) Criar aplicativo

1. Acessar **LinkedIn Developers Console**  
   → [Link](https://www.linkedin.com/developers/apps)
2. Fazer login
3. **Create app**
4. Preencher:
   - Nome, página, logo, política de privacidade, e-mail
5. Criar

> Modo desenvolvimento → teste imediato

---

### 2) Ativar OIDC

1. **Products** → Adicionar **Sign In with LinkedIn using OpenID Connect**

---

### 3) Configuração OAuth

1. **Auth → OAuth 2.0 settings**
2. Adicionar: `https://{dominio}/?social_login=linkedin`
3. Correspondência exata
4. Salvar

---

### 4) Client ID / Secret

1. Em **Auth**, copiar
2. SESLP → LinkedIn → Colar → Salvar
3. Testar no frontend

---

### 5) Escopos

| Escopo    | Descrição          | Nota            |
| --------- | ------------------ | --------------- |
| `openid`  | Token ID           | **Obrigatório** |
| `profile` | Nome, foto, título | **Obrigatório** |
| `email`   | E-mail             | **Obrigatório** |

> Escopos antigos **obsoletos**

---

### 6) Solução de problemas

- **redirect_uri_mismatch** → URI exata
- **invalid_client** → ID/Secret errados
- **email NULL** → escopo ausente ou negado

---

### 7) Checklist

- [ ] App criado
- [ ] OIDC ativado
- [ ] URI de redirecionamento registrado
- [ ] ID/Secret no SESLP
- [ ] Teste em HTTPS

</details>

---

<details>
  <summary><strong>Naver</strong></summary>

> **Redirect URI:** `https://{dominio}/?social_login=naver`  
> **Escopos recomendados:** `name`, `email`  
> ※ Naver usa **Naver Login (네아로)**, **HTTPS obrigatório**

---

### 1) Registro do aplicativo

1. Acessar **Naver Developer Center**  
   → [Link](https://developers.naver.com/apps/)
2. Fazer login
3. **Registrar aplicativo**
4. Preencher:
   - Nome, API: `Naver Login`
   - Web: URL do site, **Callback URL**
5. **Registrar**

> HTTPS obrigatório, subdomínios separados

---

### 2) Client ID / Secret

1. **Meus aplicativos** → copiar

---

### 3) Configuração no WordPress

1. WP Admin → **SESLP → Naver**
2. Colar ID/Secret
3. Verificar URI exata
4. **Salvar** → Testar

---

### 4) Permissões

| Dado                | Escopo   | Nota                   |
| ------------------- | -------- | ---------------------- |
| Nome                | `name`   | Padrão                 |
| E-mail              | `email`  | Padrão                 |
| Gênero, aniversário | Separado | **Revisão necessária** |

> E-mail recusado → `null`

---

### 5) Solução de problemas

- **redirect_uri_mismatch** → correspondência exata
- **HTTP proibido** → apenas HTTPS
- **Subdomínio** → registro separado

---

### 6) Checklist

- [ ] App registrado
- [ ] Callback URL exata
- [ ] HTTPS
- [ ] ID/Secret no SESLP
- [ ] Teste de consentimento de e-mail

</details>

---

<details>
  <summary><strong>Kakao</strong></summary>

> **Redirect URI:** `https://{dominio}/?social_login=kakao`  
> **Escopos recomendados:** `profile_nickname`, `profile_image`, `account_email`  
> ※ `account_email` só após **verificação de identidade ou registro empresarial**  
> ※ **HTTPS obrigatório**, **Client Secret deve ser ativado**

---

### 1) Criar aplicativo

1. Acessar **Kakao Developers**  
   → [https://developers.kakao.com/](https://developers.kakao.com/)
2. Login → **Meus aplicativos → Adicionar app**
3. Preencher:
   - Nome do app, empresa
   - Categoria
   - Aceitar política operacional
4. **Salvar**

---

### 2) Ativar Kakao Login

1. **Configurações do produto > Kakao Login**
2. **Ativar Kakao Login** → **LIGADO**
3. **Registrar URI de redirecionamento**
   - `https://{dominio}/?social_login=kakao`
   - **Salvar**
4. O domínio deve coincidir **com o domínio da plataforma**

---

### 3) Itens de consentimento (Escopos)

1. **Itens de consentimento**
2. Adicionar e configurar:

| Escopo             | Descrição      | Tipo de consentimento | Nota                       |
| ------------------ | -------------- | --------------------- | -------------------------- |
| `profile_nickname` | Apelido        | Obrigatório/Opcional  | Básico                     |
| `profile_image`    | Foto de perfil | Obrigatório/Opcional  | Básico                     |
| `account_email`    | E-mail         | **Opcional**          | **Verificação necessária** |

3. Informar **finalidade** claramente
4. **Salvar**

> Escopos sensíveis exigem **verificação**

---

### 4) Registrar plataforma Web

1. **Configurações do app > Plataforma**
2. **Registrar plataforma Web**
3. Domínio do site: `https://{dominio}`
4. **Salvar** → Deve coincidir com URI de redirecionamento

---

### 5) Segurança – Gerar e ativar Client Secret

1. **Configurações do produto > Segurança**
2. **Usar Client Secret** → **LIGADO**
3. **Gerar Secret** → Copiar valor
4. **Estado de ativação** → **Ativo**
5. **Salvar**
   > **Obrigatório ativar após gerar**

---

### 6) Obter chave REST API (Client ID)

1. **Chaves do app**
2. Copiar **Chave REST API** → Usar como **Client ID**

---

### 7) Configuração no WordPress

1. WP Admin → **SESLP Settings → Kakao**
2. **Client ID** = Chave REST API  
   **Client Secret** = Secret gerado
3. **Salvar**
4. Testar com **botão Kakao Login**

---

### 8) Solução de problemas

- **redirect_uri_mismatch** → Correspondência 100 %
- **invalid_client** → Secret não ativado ou erro
- **email vazio** → Recusado pelo usuário ou não verificado
- **Domínio não coincide** → Plataforma vs URI
- **HTTP proibido** → **Somente HTTPS**

> **Logs:**  
> `/wp-content/seslp-logs/seslp-debug.log`  
> `/wp-content/debug.log`

---

### 9) Checklist

- [ ] Kakao Login ativado
- [ ] URI de redirecionamento registrado
- [ ] Domínio da plataforma Web registrado
- [ ] Consentimentos configurados
- [ ] Client Secret gerado + ativado
- [ ] Chave REST API / Secret no SESLP
- [ ] Testado no frontend HTTPS

</details>

---

<details>
  <summary><strong>LINE</strong></summary>

> **Redirect URI:** `https://{dominio}/?social_login=line`  
> **Obrigatório:** Ativar OpenID Connect, **solicitar e obter aprovação para permissão de e-mail**  
> **Escopos recomendados:** `openid`, `profile`, `email`  
> ※ **HTTPS obrigatório**, **e-mail exige aprovação prévia**

---

### 1) Criar Provider e Canal

1. Acessar **LINE Developers Console**  
   → [https://developers.line.biz/console/](https://developers.line.biz/console/)
2. Fazer login com **conta LINE Business** (conta pessoal não permitida)
3. Clicar em **Criar novo provider** → Digitar nome → **Create**
4. Sob o provider → aba **Channels**
5. Selecionar **Criar canal LINE Login**
6. Configurar:
   - **Tipo de canal:** `LINE Login`
   - **Provider:** Selecionar criado
   - **Região:** País alvo (ex. `South Korea`, `Japan`)
   - **Nome / descrição / ícone:** Exibido na tela de consentimento
7. Aceitar termos → **Create**

---

### 2) Ativar OpenID Connect e solicitar permissão de e-mail

1. Menu **OpenID Connect**
2. Clicar em **Apply** ao lado de **Email address permission**
3. Preencher solicitação:
   - **URL da política de privacidade** (deve ser pública)
   - **Print da política de privacidade**
   - Marcar acordo → **Submit**
4. **O escopo `email` só funciona após aprovação**  
   → Aprovação: 1–3 dias úteis

---

### 3) Registrar Callback URL e publicar canal

1. Menu **LINE Login**
2. Inserir **Callback URL**:  
   → `https://{dominio}/?social_login=line`
3. **Correspondência exata exigida**:
   - Protocolo: `https://` (**HTTP não permitido**)
   - Domínio, caminho, query string **100% iguais**
4. **Salvar**
5. Alterar status do canal para **Published**
   - **Development:** apenas teste
   - **Published:** em produção

---

### 4) Obter Channel ID / Secret

1. Topo do canal ou **Basic settings**
2. **Channel ID** → SESLP **Client ID**  
   **Channel Secret** → SESLP **Client Secret**

---

### 5) Configuração no WordPress

1. WP Admin → **SESLP Settings → LINE**
2. **Client ID** ← Channel ID  
   **Client Secret** ← Channel Secret
3. **Salvar**
4. Testar com **botão LINE Login** no frontend

---

### 6) Solução de problemas

- **redirect_uri_mismatch** → Qualquer diferença → erro → **100% igual**
- **invalid_client** → Secret errado ou **não publicado**
- **email NULL** → **Permissão de e-mail não aprovada** ou recusa do usuário
- **HTTP proibido** → **Somente HTTPS** (localhost HTTPS OK)
- **Modo Development** → Apenas contas de teste podem logar

> **Logs:**  
> `/wp-content/seslp-logs/seslp-debug.log`  
> `/wp-content/debug.log`

---

### 7) Checklist

- [ ] Provider + canal LINE Login criado com conta Business
- [ ] Permissão de e-mail **solicitada e aprovada**
- [ ] **Callback URL** registrada exatamente
- [ ] **HTTPS usado**, status **Published**
- [ ] Channel ID/Secret salvos no SESLP
- [ ] Teste de login no frontend concluído

---

> **Nota:** SESLP suporta totalmente **LINE Login v2.1 + OpenID Connect**.  
> **Coleta de e-mail exige aprovação prévia**.

</details>

---

## 📋 Resumo

| Plano  | Provedor     | Escopos obrigatórios / recomendados                  | Exemplo de Redirect URI                    | Observações                   |
| ------ | ------------ | ---------------------------------------------------- | ------------------------------------------ | ----------------------------- |
| Grátis | **Google**   | `openid email profile`                               | `https://{dominio}/?social_login=google`   | Tela de consentimento externa |
| Grátis | **Facebook** | `public_profile`, `email`                            | `https://{dominio}/?social_login=facebook` | Não usa `openid`              |
| Grátis | **LinkedIn** | `openid profile email`                               | `https://{dominio}/?social_login=linkedin` | Migração total para OIDC      |
| Pago   | **Naver**    | `email`, `name`                                      | `https://{dominio}/?social_login=naver`    | API “Naver Login”             |
| Pago   | **Kakao**    | `profile_nickname`, `profile_image`, `account_email` | `https://{dominio}/?social_login=kakao`    | Exige Client Secret           |
| Pago   | **LINE**     | `openid profile email`                               | `https://{dominio}/?social_login=line`     | Deve estar “Published”        |
