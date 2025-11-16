> Este documento explica como configurar cada provedor de login social(Google, Facebook, LinkedIn, Naver, Kakao, LINE) no plugin **Simple Easy Social Login (SESLP)**.  
> Todos os logins são baseados em **OAuth 2.0 / OpenID Connect (OIDC)**.  
> Você deve criar um aplicativo (cliente) no console de cada provedor e inserir o **Client ID / Client Secret** no SESLP.

---

## 🔧 Guia de configuração comum

#### 1) **Regra da Redirect URI:**

`https://{seu-dominio}/?social_login={provider}`

Exemplos:

- Google → `https://example.com/?social_login=google`
- Facebook → `https://example.com/?social_login=facebook`
- LinkedIn → `https://example.com/?social_login=linkedin`
- Naver → `https://example.com/?social_login=naver`
- Kakao → `https://example.com/?social_login=kakao`
- LINE → `https://example.com/?social_login=line`

#### 2) **HTTPS obrigatório**

A maioria dos provedores requer HTTPS e rejeita redirecionamentos `http://`.

#### 3) **Correspondência exata**

A Redirect URI registrada no console deve corresponder **100%** ao valor enviado pelo SESLP  
 (protocolo, subdomínio, caminho, barra final e parâmetros de consulta).

#### 4) **E-mail pode não estar disponível**

Alguns provedores permitem que o usuário negue o compartilhamento do e-mail. O SESLP pode vincular contas usando IDs estáveis do provedor.

#### 5) **Onde verificar os logs**

- `/wp-content/seslp-logs/seslp-debug.log`
- `/wp-content/debug.log` (`WP_DEBUG_LOG = true`)

---

## 🌍 Guias por provedor

> Expanda cada provedor abaixo e cole o guia correspondente em português quando estiver pronto.

---

<details open>
  <summary><strong>Google</strong></summary>

> - **Escopos recomendados:** `openid email profile`
> - **Redirect URI:** `https://{dominio}/?social_login=google`

---

#### 1) Preparação (Checklist obrigatório)

(1) **HTTPS recomendado/essencial** (use certificados confiáveis também em ambientes de desenvolvimento local).

(2) A Redirect URI deve **corresponder 100% exatamente** ao valor registrado no console. Ex.: `https://example.com/?social_login=google`

(3) No modo de testes, apenas **usuários de teste** podem fazer login (até 100 usuários).

(4) Ao usar URLs de homepage do app / política de privacidade / termos de uso, pode ser necessário registrar os **domínios do app (Authorized domains)** e concluir a **verificação de propriedade do domínio**.

#### 2) Configuração do projeto e da tela de consentimento

(1) Acesse o **Google Cloud Console**  
[https://console.cloud.google.com/apis/credentials](https://console.cloud.google.com/apis/credentials)

(2) Selecione o projeto no topo → **Create new project** (se necessário).

(3) No menu lateral, vá em **APIs & Services → OAuth consent screen**.

(4) Selecione o **User Type**: geralmente **External**.

(5) Preencha as **informações do app**: nome do app, e-mail de suporte ao usuário, logo (opcional).

(6) Seção **App domain**

- Informe a URL da homepage, a URL da política de privacidade e a URL dos termos de uso do app
- Adicione o **domínio raiz (ex.: example.com)** em **Authorized domains** → **Save**
- Se necessário, faça a **verificação de propriedade do domínio** via Search Console.

(7) Configure os **Scopes**

- **Recomendados:** `openid`, `email`, `profile`
- Scopes sensíveis/restritos podem exigir revisão antes da publicação.

(8) Adicione **Test users** (e-mails autorizados a fazer login no modo de testes).

(9) Clique em **Save**.

> Observação: Usar apenas os scopes básicos (`openid email profile`) geralmente permite publicar o app **sem necessidade de revisão**.

#### 3) Criar cliente OAuth (Aplicativo Web)

(1) Menu lateral: **APIs & Services → Credentials**.

(2) Parte superior: **+ Create Credentials → OAuth client ID**.

(3) Tipo de aplicativo: `Web application`.

(4) Informe um **nome fácil de identificar** (por exemplo, `SESLP – Front`).

(5) Adicione em **Authorized redirect URIs**:

- `https://{domain}/?social_login=google`

(6) Clique em **Create** e copie o **Client ID / Client Secret** exibidos.

> (Opcional) Em geral, **Authorized JavaScript origins** não são necessários para este plugin, pois ele usa o fluxo _authorization code grant_.

#### 4) Configurar no WordPress

(1) WP Admin → **SESLP Settings → Google**.

(2) Cole **Client ID / Secret** → Salvar.

(3) Teste com o botão Google.

#### 5) Alterar do modo de teste para produção

(1) Verifique **OAuth consent screen → Publishing status**.

(2) Para mudar de teste para produção:

- Confirme que as informações do app (logo/domínio do app/políticas/termos) estão corretas.
- Remova os scopes desnecessários e mantenha apenas os realmente necessários.
- Envie uma solicitação de revisão se estiver usando scopes sensíveis.

(3) Após mudar para produção, **qualquer conta Google** poderá fazer login.

#### 6) Erros comuns e soluções

(1) **redirect_uri_mismatch**

→ Ocorre quando a Redirect URI registrada no console e a URI real da requisição diferem mesmo que minimamente (incluindo protocolo, subdomínio, barra final e parâmetros de consulta). Ajuste para que sejam **exatamente iguais**.

(2) **access_denied / disallowed_useragent**

→ Restrições do navegador ou do ambiente in-app. Tente novamente em um navegador comum.

(3) **invalid_client / unauthorized_client**

→ Erro de digitação no Client ID/Client Secret ou status do app (excluído/desativado). Reemita ou verifique novamente as credenciais.

(4) **Email vazio**

→ Verifique se o escopo `email` está incluído, se o e-mail aparece corretamente na tela de consentimento e as configurações de visibilidade/segurança do e-mail na conta. Explique com clareza, na tela de consentimento, como o e-mail será utilizado.

> **Verificar logs:**
>
> - `wp-content/seslp-logs/seslp-debug.log` (debug do plugin ATIVADO)
> - `wp-content/debug.log` (`WP_DEBUG`, `WP_DEBUG_LOG = true`)

#### 7) Checklist de resumo

- [ ] Tela de consentimento OAuth: configurar informações do app/domínio/políticas/termos/scopes/usuários de teste
- [ ] Credenciais: criar cliente **Web Application**
- [ ] Registrar a Redirect URI: `https://{domain}/?social_login=google`
- [ ] SESLP: salvar Client ID/Client Secret e testar o login
- [ ] Alterar o status de publicação ao entrar em produção (enviar para revisão se necessário)

</details>

---

<details>
  <summary><strong>Facebook</strong></summary>

> - **Redirect URI:** `https://{dominio}/?social_login=facebook`
> - **Permissões recomendadas:** `public_profile`, `email`
> - ※ O Facebook não usa `openid`.

---

#### 1) Criar aplicativo e adicionar produto

(1) Vá para **Meta for Developers** → Faça login

(2) **Criar App** → Tipo geral (Consumer) → Criar

(3) Menu lateral → **Produtos → Facebook Login**

(4) **Configurações** → Verifique:

- **Client OAuth Login:** Ativo
- **Web OAuth Login:** Ativo
- **Valid OAuth Redirect URIs:**
  - Adicione `https://{dominio}/?social_login=facebook`
- (Opcional) **Aplicar HTTPS:** Recomendado

#### 2) Configuração básica

(1) **App Domains:** `example.com`

(2) **Privacy Policy URL:** Página pública

(3) **Terms of Service URL:** Página pública

(4) **User Data Deletion:** URL ou endpoint de exclusão

(5) **Categoria / Ícone:** Configurar → Salvar

#### 3) Escopos (Permissões) e App Review

(1) As permissões básicas necessárias para um login padrão são **`public_profile`**; o e-mail opcional é **`email`**.

(2) Na maioria dos casos, o escopo **`email` pode ser usado sem revisão**, mas podem existir exceções dependendo da região/conta.

(3) **Permissões avançadas**, como acesso a páginas/anúncios, exigem **App Review** e **Business Verification**.

#### 4) Alterar o modo (Desenvolvimento → Produção)

- Na parte superior ou na área de configurações do app, altere **App Mode: Development → Live**

#### 5) Checklist antes de mudar para Live

- [ ] Preparar URLs de Política de Privacidade / Termos de Uso / Diretrizes de exclusão de dados
- [ ] Preencher corretamente as **Valid OAuth Redirect URIs**
- [ ] Remover permissões desnecessárias e solicitar apenas as realmente necessárias
- [ ] (Se necessário) Concluir **App Review / Business Verification**

#### 6) Configuração no WordPress

(1) WP Admin → **SESLP Settings → Facebook**

(2) Inserir **App ID / Secret** → Salvar

(3) Testar botão do Facebook

#### 7) Solução de problemas

(1) **Can't Load URL / erro de redirect_uri**

→ Verifique se a **mesma URI, exatamente igual**, está registrada em **Valid OAuth Redirect URIs** (incluindo protocolo, subdomínio, barra final e parâmetros de consulta).

(2) **email null**

→ O usuário não cadastrou um e-mail no Facebook ou o e-mail está como privado. Prepare uma **lógica de vinculação de conta baseada em ID** e explique de forma clara, na tela de consentimento, como a permissão de e-mail será utilizada.

(3) **Erros relacionados a permissões**

→ Se o escopo solicitado ultrapassar o conjunto básico de permissões, será necessário passar por **App Review / Business Verification**.

(4) **Não é possível mudar para Live**

→ Isso acontece se a URL da política de privacidade / termos / diretrizes de exclusão de dados estiver **ausente ou não for pública**. Você deve fornecer uma URL pública.

</details>

---

<details>
  <summary><strong>LinkedIn</strong></summary>

> - **Redirect URI:** `https://{domain}/?social_login=linkedin`
> - **Configuração obrigatória:** Ativar OpenID Connect (OIDC)
> - **Escopos recomendados:** `openid`, `profile`, `email`
> - O LinkedIn está **descontinuando** os escopos legados (`r_liteprofile`, `r_emailaddress`).
> - Novos apps **devem usar os escopos padrão do OIDC**.

---

#### 1) Criar um aplicativo

(1) Acesse o **LinkedIn Developers Console**

→ [https://www.linkedin.com/developers/apps](https://www.linkedin.com/developers/apps)

(2) Faça login com sua conta LinkedIn

(3) Clique em **Create app**

(4) Preencha os campos obrigatórios:

- **App name:** ex.: `MySite LinkedIn Login`
- **LinkedIn Page:** Selecione uma página ou “None”
- **App logo:** PNG/JPG com 100×100 ou mais
- **Privacy Policy URL / Business Email:** Válidos e públicos

(5) Clique em **Create app**

> Por padrão, o app fica em **Development Mode** → permite testar logins com `openid`, `profile`, `email` **sem publicar**.

#### 2) Ativar OpenID Connect (OIDC)

(1) Vá até a aba **Products**

(2) Encontre **Sign In with LinkedIn using OpenID Connect**

(3) Clique em **Add product** → aprovado instantaneamente

(4) As configurações de OIDC aparecerão na aba **Auth**

> **Escopos OIDC obrigatórios**
>
> - `openid` → ID token
> - `profile` → Nome, foto, headline
> - `email` → Endereço de e-mail

#### 3) Configurações OAuth 2.0 (aba Auth)

(1) Navegue até **Auth → OAuth 2.0 settings**

(2) Adicione em **Redirect URLs**:

→ `https://{domain}/?social_login=linkedin`

(3) **Correspondência exata obrigatória** (protocolo, subdomínio, barra, query string)

(4) Registre múltiplas URIs se necessário:

- Local: `https://localhost:3000/?social_login=linkedin`
- Staging: `https://staging.example.com/?social_login=linkedin`
- Produção: `https://example.com/?social_login=linkedin`

(5) Clique em **Save**

#### 4) Obter Client ID / Client Secret

(1) Na aba **Auth**, localize:

- **Client ID**
- **Client Secret**

(2) WordPress Admin → **SESLP Settings → LinkedIn**

(3) Cole ambos → **Save**

(4) Teste com o **botão de login do LinkedIn** no frontend

> **Segurança:**
>
> - Nunca exponha o Client Secret
> - Use **Regenerate secret** se houver suspeita de comprometimento

#### 5) Escopos

| Escopo    | Descrição          | Nota            |
| --------- | ------------------ | --------------- |
| `openid`  | Token ID           | **Obrigatório** |
| `profile` | Nome, foto, título | **Obrigatório** |
| `email`   | E-mail             | **Obrigatório** |

> **Escopos legados (`r_liteprofile`, `r_emailaddress`)**
>
> - **Descontinuados após 2024**
> - **Não disponíveis para novos apps**

#### 6) Solução de problemas

(1) **redirect_uri_mismatch**

→ As URIs são diferentes, mesmo que ligeiramente → garanta uma correspondência de **100%**

(2) **invalid_client**

→ ID/Secret incorretos ou app inativo → verificar novamente ou gerar novos valores

(3) **email NULL**

→ O usuário negou a permissão ou o escopo `email` não foi incluído → explique o uso do e-mail na tela de consentimento

(4) **insufficient_scope**

→ O escopo solicitado não foi aprovado → verifique se o OIDC está habilitado

(5) **OIDC not enabled**

→ Falta o produto **Sign In with LinkedIn using OpenID Connect** em Products

> **Logs:**
>
> - `/wp-content/seslp-logs/seslp-debug.log`
> - `/wp-content/debug.log`

#### 7) Checklist de resumo

- [ ] App criado
- [ ] Produto **OpenID Connect** adicionado
- [ ] Redirect URI registrada exatamente
- [ ] Client ID/Secret salvos no SESLP
- [ ] Escopos: `openid profile email` (sem escopos legados)
- [ ] Testes realizados em frontend com HTTPS

---

> **Nota:**
>
> - O SESLP é totalmente compatível com o **fluxo OIDC**.
> - O OAuth 2.0 legado **não é mais suportado**.
> - Sempre use **OpenID Connect** para novas integrações.

</details>

---

<details>
  <summary><strong>Naver</strong></summary>

> - **Redirect URI:** `https://{dominio}/?social_login=naver`
> - **Escopos recomendados:** `name`, `email`
> - Naver usa **Naver Login (네아로)**, **HTTPS obrigatório**

---

#### 1) Registro do aplicativo

(1) Acesse o **Naver Developer Center**

→ [https://developers.naver.com/apps/](https://developers.naver.com/apps/)

(2) Faça login com sua conta Naver

(3) Clique em **Application → Register Application**

(4) Preencha os campos obrigatórios:

- **Application Name:** ex.: `MySite Naver Login`
- **API Usage:** Selecione `Naver Login (네아로)`
- **Add Environment → Web**
- **Service URL:** `https://example.com`
- **Callback URL:** `https://example.com/?social_login=naver`

(5) Concorde com os termos → clique em **Register**

> **Observação:**
>
> - **HTTPS obrigatório** → HTTP não é permitido
> - **Subdomínios devem ser registrados separadamente**

#### 2) Obter Client ID / Client Secret

(1) Vá em **My Applications**

(2) Clique no app → copie o **Client ID** e o **Client Secret**

#### 3) Configurações no WordPress (plugin)

(1) WP Admin → **SESLP Settings → Naver**

(2) Cole o **Client ID / Client Secret**

(3) Garanta que a **Redirect URI** corresponda exatamente a: `https://{domain}/?social_login=naver`

(4) Clique em **Save** → teste com o botão de login do Naver no frontend

#### 4) Permissões e Fornecimento de Dados

| Dado                | Escopo   | Nota                   |
| ------------------- | -------- | ---------------------- |
| Nome                | `name`   | Padrão                 |
| E-mail              | `email`  | Padrão                 |
| Gênero, aniversário | Separado | **Revisão necessária** |

> - Os usuários podem **aceitar/recusar** na tela de consentimento
> - Se o e-mail for recusado → `email = null` → use **vinculação de conta baseada em ID**
> - Dados sensíveis exigem **revisão do app Naver**

#### 5) Solução de problemas

(1) **Redirect URI mismatch**

→ Mesmo uma pequena diferença causa erro → garanta **correspondência de 100%**

(2) **Erro HTTP**

→ É obrigatório usar **HTTPS**

(3) **Erro de subdomínio**

→ Registre cada subdomínio separadamente

(4) **email NULL**

→ Usuário recusou ou e-mail é privado → prepare lógica baseada em ID

(5) **Revisão necessária**

→ Login básico: **sem revisão**  
→ Dados adicionais: **revisão obrigatória**

> **Logs:**
>
> - `/wp-content/seslp-logs/seslp-debug.log`
> - `/wp-content/debug.log`

#### 6) Checklist de resumo

- [ ] App registrado no Naver Developer Center
- [ ] **Callback URL** registrada exatamente
- [ ] **HTTPS** ativado
- [ ] Subdomínios registrados separadamente (se necessário)
- [ ] Client ID/Secret salvos no SESLP
- [ ] Testado o comportamento de aceitar/recusar e-mail
- [ ] Teste de login no frontend concluído

---

> - **Nota:**
>
> - O SESLP oferece suporte completo ao **Naver Login (네아로)**.
> - O login básico (`name`, `email`) está **disponível sem necessidade de revisão**.

</details>

---

<details>
  <summary><strong>Kakao</strong></summary>

> - **Redirect URI:** `https://{dominio}/?social_login=kakao`
> - **Escopos recomendados:** `profile_nickname`, `profile_image`, `account_email`
> - `account_email` só após **verificação de identidade ou registro empresarial**
> - **HTTPS obrigatório**, **Client Secret deve ser ativado**

---

#### 1) Criar aplicativo

(1) Acessar **Kakao Developers**

→ [https://developers.kakao.com/](https://developers.kakao.com/)

(2) Login → **Meus aplicativos → Adicionar app**

(3) Preencher:

- Nome do app, empresa
- Categoria
- Aceitar política operacional

(4) **Salvar**

#### 2) Ativar Kakao Login

(1) **Configurações do produto > Kakao Login**

(2) **Ativar Kakao Login** → **LIGADO**

(3) **Registrar URI de redirecionamento**

- `https://{dominio}/?social_login=kakao`
- **Salvar**

(4) O domínio deve coincidir **com o domínio da plataforma**

#### 3) Itens de consentimento (Escopos)

(1) **Itens de consentimento**

(2) Adicionar e configurar:

| Escopo             | Descrição      | Tipo de consentimento | Nota                       |
| ------------------ | -------------- | --------------------- | -------------------------- |
| `profile_nickname` | Apelido        | Obrigatório/Opcional  | Básico                     |
| `profile_image`    | Foto de perfil | Obrigatório/Opcional  | Básico                     |
| `account_email`    | E-mail         | **Opcional**          | **Verificação necessária** |

(3) Informar **finalidade** claramente

(4) **Salvar**

> Escopos sensíveis exigem **verificação**

#### 4) Registrar plataforma Web

(1) **Configurações do app > Plataforma**

(2) **Registrar plataforma Web**

(3) Domínio do site: `https://{dominio}`

(4) **Salvar** → Deve coincidir com URI de redirecionamento

#### 5) Segurança – Gerar e ativar Client Secret

(1) **Configurações do produto > Segurança**

(2) **Usar Client Secret** → **LIGADO**

(3) **Gerar Secret** → Copiar valor

(4) **Estado de ativação** → **Ativo**

(5) **Salvar**

> **Obrigatório ativar após gerar**

#### 6) Obter chave REST API (Client ID)

(1) **Chaves do app**

(2) Copiar **Chave REST API** → Usar como **Client ID**

#### 7) Configuração no WordPress

(1) WP Admin → **SESLP Settings → Kakao**

(2) **Client ID** = Chave REST API  
 **Client Secret** = Secret gerado

(3) **Salvar**

(4) Testar com **botão Kakao Login**

#### 8) Solução de problemas

(1) **redirect_uri_mismatch** → Correspondência 100 %

(2) **invalid_client** → Secret não ativado ou erro

(3) **email vazio** → Recusado pelo usuário ou não verificado

(4) **Domínio não coincide** → Plataforma vs URI

(5) **HTTP proibido** → **Somente HTTPS**

> **Logs:**
>
> - `/wp-content/seslp-logs/seslp-debug.log`
> - `/wp-content/debug.log`

#### 9) Checklist

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

> - **Redirect URI:** `https://{dominio}/?social_login=line`
> - **Obrigatório:** Ativar OpenID Connect, **solicitar e obter aprovação para permissão de e-mail**
> - **Escopos recomendados:** `openid`, `profile`, `email`
> - **HTTPS obrigatório**, **e-mail exige aprovação prévia**

---

#### 1) Criar Provider e Canal

(1) Acessar **LINE Developers Console**  
 → [https://developers.line.biz/console/](https://developers.line.biz/console/)

(2) Fazer login com **conta LINE Business** (conta pessoal não permitida)

(3) Clicar em **Criar novo provider** → Digitar nome → **Create**

(4) Sob o provider → aba **Channels**

(5) Selecionar **Criar canal LINE Login**

(6) Configurar:

- **Tipo de canal:** `LINE Login`
- **Provider:** Selecionar criado
- **Região:** País alvo (ex. `South Korea`, `Japan`)
- **Nome / descrição / ícone:** Exibido na tela de consentimento

(7) Aceitar termos → **Create**

#### 2) Ativar OpenID Connect e solicitar permissão de e-mail

(1) Menu **OpenID Connect**

(2) Clicar em **Apply** ao lado de **Email address permission**

(3) Preencher solicitação:

- **URL da política de privacidade** (deve ser pública)
- **Print da política de privacidade**
- Marcar acordo → **Submit**

(4) **O escopo `email` só funciona após aprovação**  
 → Aprovação: 1–3 dias úteis

#### 3) Registrar Callback URL e publicar canal

(1) Menu **LINE Login**

(2) Inserir **Callback URL**:  
 → `https://{dominio}/?social_login=line`

(3) **Correspondência exata exigida**:

- Protocolo: `https://` (**HTTP não permitido**)
- Domínio, caminho, query string **100% iguais**

(4) **Salvar**

(5) Alterar status do canal para **Published**

- **Development:** apenas teste
- **Published:** em produção

#### 4) Obter Channel ID / Secret

(1) Topo do canal ou **Basic settings**

(2) **Channel ID** → SESLP **Client ID**  
 **Channel Secret** → SESLP **Client Secret**

#### 5) Configuração no WordPress

(1) WP Admin → **SESLP Settings → LINE**

(2) **Client ID** ← Channel ID  
 **Client Secret** ← Channel Secret

(3) **Salvar**

(4) Testar com **botão LINE Login** no frontend

#### 6) Solução de problemas

(1) **redirect_uri_mismatch** → Qualquer diferença → erro → **100% igual**

(2) **invalid_client** → Secret errado ou **não publicado**

(3) **email NULL** → **Permissão de e-mail não aprovada** ou recusa do usuário

(4) **HTTP proibido** → **Somente HTTPS** (localhost HTTPS OK)

(5) **Modo Development** → Apenas contas de teste podem logar

> **Logs:**
>
> - `/wp-content/seslp-logs/seslp-debug.log`
> - `/wp-content/debug.log`

#### 7) Checklist

- [ ] Provider + canal LINE Login criado com conta Business
- [ ] Permissão de e-mail **solicitada e aprovada**
- [ ] **Callback URL** registrada exatamente
- [ ] **HTTPS usado**, status **Published**
- [ ] Channel ID/Secret salvos no SESLP
- [ ] Teste de login no frontend concluído

> **Nota:** SESLP suporta totalmente
>
> - **LINE Login v2.1 + OpenID Connect**.
> - **Coleta de e-mail exige aprovação prévia**.

</details>
