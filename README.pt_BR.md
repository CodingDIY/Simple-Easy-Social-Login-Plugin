# Simple Easy Social Login – OAuth Login

Simple Easy Social Login é um plugin WordPress leve e fácil de usar que adiciona uma funcionalidade de login social rápida e fluida ao seu site.

Ele oferece suporte a **Google, Facebook e LinkedIn (Gratuito)**, bem como **Naver, Kakao e Line (Premium)**,  
e foi projetado para funcionar especialmente bem em sites voltados para usuários da Ásia (Coreia, Japão, China), além de Europa e América do Sul.

O plugin se integra perfeitamente às páginas padrão de login e registro do WordPress,  
e também oferece suporte aos formulários de login e registro do WooCommerce.  
As imagens de perfil das redes sociais podem ser sincronizadas automaticamente com os perfis de usuário do WordPress.

O plugin é construído com uma **arquitetura de Providers extensível**,  
permitindo que novos Providers OAuth sejam adicionados posteriormente como plugins Add-on independentes, se necessário.

---

## ✨ Recursos

- Login com Google (Gratuito)
- Login com Facebook (Gratuito)
- Login com LinkedIn (Gratuito)
- Login com Naver (Premium)
- Login com Kakao (Premium)
- Login com Line (Premium)
- Sincronização automática de avatares de usuários
- Vinculação automática de usuários WordPress existentes por e-mail
- URLs de redirecionamento personalizadas após login, logout e registro
- Interface administrativa simples e limpa para configuração dos Providers
- Suporte a shortcode: [se_social_login]
- Exibição automática nos formulários de login e registro do WordPress
- Suporte aos formulários de login e registro do WooCommerce (opcional)
- Estrutura leve que segue os padrões de codificação do WordPress
- Não cria tabelas desnecessárias no banco de dados
- Sistema de Providers extensível para adicionar novos Providers OAuth por meio de plugins Add-on

---

## 🐞 Log de depuração

O SESLP inclui um sistema de log de depuração integrado para ajudar a diagnosticar problemas de OAuth e login social.

Você pode consultar explicações detalhadas diretamente no painel administrativo do WordPress:
**SESLP → Guides → Debug Log & Troubleshooting**

Os arquivos de log são gerados em:

- `/wp-content/SESLP-debug.log`
- `/wp-content/debug.log` (quando `WP_DEBUG_LOG` está ativado)

---

## 🚀 Instalação

1. Envie o plugin para o diretório `/wp-content/plugins/simple-easy-social-login/`.
2. Ative o plugin em **Plugins → Plugins instalados** no painel administrativo do WordPress.
3. Acesse **Configurações → Simple Easy Social Login**.
4. Insira o Client ID e o Client Secret de cada Provider de login social.
5. Salve as alterações.
6. Verifique se os botões de login social estão sendo exibidos corretamente no frontend.

---

## ❓ Perguntas frequentes

### Este plugin funciona com WooCommerce?

Sim. Ele se integra aos formulários de login e registro do WooCommerce.

### O login do WooCommerce funciona, mas o comportamento de redirecionamento é diferente. Isso é esperado?

Sim. Quando o WooCommerce está ativo, os usuários normalmente são redirecionados para a página **Minha conta** após o login.  
A URL de redirecionamento pode ser personalizada nas configurações do plugin ou por meio de filtros disponíveis.

### O que devo verificar se o login social não funcionar em um site WooCommerce?

Verifique os seguintes itens:

- O WooCommerce está atualizado para uma versão estável recente
- O provedor de login social está ativado nas configurações do plugin
- Os valores de Client ID e Client Secret estão corretos
- As URLs de redirecionamento / callback estão corretamente registradas no console do desenvolvedor do provedor
- Templates personalizados de login ou checkout não removem os hooks padrão do WooCommerce
- O log de depuração está ativado e o arquivo `/wp-content/SESLP-debug.log` foi revisado

### Posso usar apenas o login com Google?

Sim. Cada Provider pode ser ativado ou desativado individualmente.

### Quando preciso de uma licença Premium?

É necessária uma licença Premium para utilizar os logins **Naver, Kakao e Line**.  
Google, Facebook e LinkedIn estão disponíveis gratuitamente.

### Existe um shortcode disponível?

Sim. Você pode inserir os botões de login social em qualquer lugar usando o seguinte shortcode:
[se_social_login]

### Os avatares dos usuários são importados automaticamente?

Sim. Para Providers compatíveis, como Google e Facebook, as imagens de perfil podem ser importadas automaticamente e sincronizadas como avatares do WordPress.

---

## 🖼 Capturas de tela

1. Botões de login social exibidos na página de login do WordPress (layout em lista).
2. Layout somente com ícones para os botões de login social na tela de login.
3. Opções de redirecionamento após o login (painel, perfil, página inicial ou URL personalizada).
4. Registro de depuração, opções de layout da interface, shortcode e configurações de desinstalação.
5. Guia de configuração integrado explicando regras de redirecionamento OAuth e requisitos comuns.
6. Guia passo a passo para configurar a tela de consentimento OAuth e o cliente do Google.
7. Configurações administrativas para credenciais de login do Google, Facebook e LinkedIn.
8. Configurações administrativas para provedores de login Naver, Kakao e LINE.
9. Regras unificadas de URI de redirecionamento usadas em todos os provedores compatíveis.
10. Localização dos logs de depuração e visão geral de solução de problemas.
11. Erros comuns de OAuth, soluções recomendadas e local dos logs de depuração.

---

## 📝 Registro de alterações (Changelog)

### 1.9.9

- Finalização das capturas de tela e da documentação para o lançamento público
- Adição de descrições completas das capturas de tela, abrangendo fluxo de login, configurações, guias e solução de problemas
- Pequenas melhorias de limpeza e consistência na documentação

### 1.9.8

- Correção de um erro fatal de tipo em `SESLP_Avatar::resolve_user()`, garantindo o retorno `WP_User|null`
- Melhoria no tratamento de fallback de avatar:
  - Uso seguro do avatar padrão do WordPress quando a imagem do perfil social estiver ausente ou inválida
  - Prevenção de imagens de avatar quebradas (por exemplo, problemas com imagens de perfil do LinkedIn)
- Pequenas melhorias de estabilidade relacionadas à renderização de avatares

### 1.9.7

- Adicionada seção de log de depuração ao README
- Guia detalhado de logs integrado às guias administrativas (multilíngue)
- Padronização do caminho do arquivo de log (`/wp-content/SESLP-debug.log`)
- Limpeza e melhoria da consistência da documentação

### 1.9.6

- Melhoria na usabilidade da página de configurações
- Adição de alternância para mostrar/ocultar chaves secretas
- Correção de conflitos com estilos do núcleo do WordPress
- Melhoria na detecção dos planos Pro / Max

### 1.9.5

- Refatoração em larga escala
- Unificação de helpers e melhoria da arquitetura de Providers
- Limpeza da interface de configurações
- Melhoria da estabilidade e da manutenção

### 1.9.3

- Atualização das traduções dos Guides
- Adição da exibição do shortcode na página de configurações

### 1.9.2

- Limpeza da estrutura interna
- Adição da classe de carregamento de Guides
- Reestruturação dos templates
- Melhoria da estabilidade do carregador de configurações e CSS

### 1.9.1

- Adição da página de Guia do Administrador
- Renderização de documentação multilíngue baseada em Markdown (Parsedown)
- Melhoria do estilo da interface do usuário

### 1.9.0

- Fase de preparação para uma grande refatoração
- Expansão dos helpers de i18n
- Melhoria na formatação segura e no sistema de logs

### 1.7.23

- Atualizações de tradução

### 1.7.22

- Melhoria das mensagens de depuração para exibir o Provider utilizado anteriormente

### 1.7.21

- Exibição do nome do Provider nas mensagens de erro ao detectar e-mails duplicados
- Ocultação automática das mensagens de erro após 10 segundos via JavaScript

### 1.7.19

- Prevenção da criação de contas duplicadas com o mesmo endereço de e-mail
- Melhoria do fluxo OAuth:
  - `get_access_token()`
  - `get_user_info()`
  - `create_or_link_user()`

### 1.7.18

- Remoção dos tooltips dos campos Google Client ID / Secret
- Limpeza da estrutura do código
- Remoção do texto “(Email required)” do botão de login do Line

### 1.7.17

- Correção de problemas relacionados ao login do Line:
  - Prevenção de usuários duplicados ao fazer login novamente
  - Correção do reaparecimento da página `/complete-profile`
  - Permitir atualização de e-mail para corrigir o erro “Invalid request”
- Unificação dos logs de depuração com `SESLP_Logger`

### 1.7.16

- Mascaramento de chaves de licença nos logs de depuração (ex.: abc\*\*\*\*123)
- Adição de guia para verificação de `wp_options` durante a depuração
- Adição de notificação administrativa quando a gravação de logs falha

### 1.7.15

- Correção de falhas na gravação de logs de depuração
- Aplicação do fuso horário local do WordPress aos carimbos de data/hora
- Adição de logs de depuração ao salvar configurações

### 1.7.5

- Aplicação dos patches de segurança mais recentes
- Otimizações de desempenho e melhorias na experiência do usuário

### 1.7.0

- Melhoria na sincronização dos botões de login social
- Reforço da segurança e correções de bugs

### 1.7.3

- Melhoria do sistema de depuração
- Adição de um diretório de debug dedicado

### 1.6.0

- Restauração da exibição da seção de chave de licença ao selecionar Plus / Premium

### 1.5.0

- Registro da opção `seslp_license_type`
- Correção do problema em que o tipo de licença era redefinido para Free ao salvar

### 1.4.0

- Correção do problema de carregamento do `style.css` na área administrativa usando `admin_enqueue_scripts`

### 1.3.0

- Melhoria da interface dos botões de opção
- Movimentação do CSS inline para `style.css`

### 1.2.0

- Adição da seleção do tipo de licença (Free / Plus / Premium)
- Melhoria do alinhamento da interface de configurações

### 1.1.0

- Adição de suporte multilíngue e carregamento de arquivos de tradução
- Melhoria da lógica de autenticação

### 1.0.0

- Lançamento inicial
- Adição de logins sociais com Google, Facebook, Naver, Kakao, Line e Weibo

---

## 📄 Licença

GPLv2 or later  
https://www.gnu.org/licenses/gpl-2.0.html
