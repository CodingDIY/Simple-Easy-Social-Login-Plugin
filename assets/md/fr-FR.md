> Ce document explique comment configurer chaque fournisseur de connexion  
> (Google, Facebook, LinkedIn, Naver, Kakao, LINE) dans le plugin **Simple Easy Social Login (SESLP)**.  
> Toutes les connexions reposent sur **OAuth 2.0 / OpenID Connect (OIDC)**.  
> Vous devez créer une application (client) dans la console de chaque fournisseur et saisir le **Client ID / Client Secret** dans SESLP.

---

## 🔧 Guide de configuration commun

### 1) **Règle de Redirect URI :**

`https://{votre-domaine}/?social_login={provider}`

Exemples :

- Google → `https://example.com/?social_login=google`
- Facebook → `https://example.com/?social_login=facebook`
- LinkedIn → `https://example.com/?social_login=linkedin`
- Naver → `https://example.com/?social_login=naver`
- Kakao → `https://example.com/?social_login=kakao`
- LINE → `https://example.com/?social_login=line`

#### 2) **HTTPS requis**

La plupart des fournisseurs exigent HTTPS et refusent les redirections `http://`.

#### 3) **Correspondance exacte**

La Redirect URI enregistrée dans la console doit correspondre **à 100 %** à celle envoyée par SESLP  
 (protocole, sous-domaine, chemin, slash final et chaîne de requête).

#### 4) **L’e-mail peut être indisponible**

Certains fournisseurs permettent à l’utilisateur de refuser le partage de l’e-mail. SESLP peut alors utiliser l’ID stable du fournisseur pour lier les comptes.

#### 5) **Où consulter les journaux (logs)**

- `/wp-content/seslp-logs/seslp-debug.log`
- `/wp-content/debug.log` (`WP_DEBUG_LOG = true`)

---

## 🌍 Guides par fournisseur

> Déployez chaque fournisseur ci-dessous et collez le guide correspondant en français lorsque vous l’aurez préparé.

---

<details open>
  <summary><strong>Google</strong></summary>

> - **Scopes recommandés :** `openid email profile`
> - **Règle de Redirect URI :** `https://{domaine}/?social_login=google`

---

#### 1) Préparation (liste obligatoire)

(1) **HTTPS recommandé/obligatoire** (certificat de développement fiable).

(2) La Redirect URI doit correspondre **exactement à 100 %**. Ex) `https://example.com/?social_login=google`

(3) En mode test, seuls les **utilisateurs de test** peuvent se connecter (jusqu’à 100).

(4) Si vous utilisez des URLs de politique/confidentialité, ajoutez des **Authorized domains** et vérifiez la propriété du domaine.

#### 2) Configuration du projet et de l’écran de consentement

(1) Accédez à la **Google Cloud Console**  
[https://console.cloud.google.com/apis/credentials](https://console.cloud.google.com/apis/credentials)

(2) Sélectionnez un projet dans la barre supérieure → ou cliquez sur **Create new project** si nécessaire.

(3) Dans le menu latéral, allez à **APIs & Services → OAuth consent screen**.

(4) Choisissez le **User Type** : en général **External**.

(5) Renseignez les **informations de l’application** : nom de l’application, adresse e-mail de support, logo (optionnel).

(6) Dans la section **App domain** :

- Saisissez l’URL de la page d’accueil, l’URL de la politique de confidentialité et l’URL des conditions d’utilisation.
- Ajoutez le **domaine racine (par ex. example.com)** dans **Authorized domains** → **Save**.
- Si nécessaire, effectuez la **vérification de propriété du domaine** via Google Search Console.

(7) Configurez les **Scopes** :

- **Recommandés :** `openid`, `email`, `profile`
- Les scopes sensibles ou restreints peuvent nécessiter une révision avant la mise en production.

(8) Ajoutez des **utilisateurs de test** (adresses e-mail autorisées à se connecter en mode test).

(9) Cliquez sur **Save**.

> Remarque : l’utilisation uniquement des scopes de base (`openid email profile`) permet souvent la mise en ligne **sans demande de révision**.

#### 3) Créer un client OAuth (application Web)

(1) Dans le menu latéral : **APIs & Services → Credentials**.

(2) En haut : **+ Create Credentials → OAuth client ID**.

(3) Type d’application : `Web application`.

(4) Saisissez un **nom distinctif** (ex. `SESLP – Front`).

(5) Ajoutez les **Authorized redirect URIs**

- `https://{domaine}/?social_login=google`

(6) Cliquez sur **Create**, puis copiez le **Client ID / Client Secret** affichés.

> (Optionnel) Les _Authorized JavaScript origins_ ne sont généralement pas nécessaires pour ce plugin utilisant le _authorization code grant_.

#### 4) Configurer dans WordPress

(1) WP Admin → **SESLP Settings → Google**.

(2) Collez **Client ID / Secret** → **Enregistrer**.

(3) Testez avec le bouton Google.

#### 5) Passage du mode test à la production

(1) Vérifiez **OAuth consent screen → Publishing status**.

(2) Pour passer du mode test à la production :

- Vérifiez que les informations de l’application (logo/domaine/politiques/conditions) sont correctes.
- Supprimez les scopes inutiles et conservez uniquement ceux qui sont nécessaires.
- Soumettez une demande de révision si vous utilisez des scopes sensibles.

(3) Après le passage en production, **tous les comptes Google** peuvent se connecter.

#### 6) Erreurs courantes et solutions

(1) **redirect_uri_mismatch**

→ Se produit lorsque la Redirect URI enregistrée dans la console et l’URI réelle de la requête diffèrent, même légèrement (protocole, sous-domaine, slash final, chaîne de requête). Corrigez pour qu’elles correspondent **exactement**.

(2) **access_denied / disallowed_useragent**

→ Restrictions liées au navigateur ou à l’environnement intégré (in-app). Réessayez dans un navigateur classique.

(3) **invalid_client / unauthorized_client**

→ Erreur dans le Client ID/Client Secret ou statut incorrect de l’application (supprimée/désactivée). Régénérez/vérifiez les identifiants.

(4) **Email vide**

→ Vérifiez que le scope `email` est inclus, que l’e-mail apparaît bien sur l’écran de consentement et les paramètres de visibilité/sécurité de l’e-mail du compte. Expliquez clairement l’usage de l’e-mail sur l’écran de consentement.

> **Vérifier les logs :**
>
> - `wp-content/seslp-logs/seslp-debug.log` (debug du plugin activé)
> - `wp-content/debug.log` (`WP_DEBUG` et `WP_DEBUG_LOG` à true)

#### 7) Liste de vérification (résumé)

- [ ] Écran de consentement OAuth : configurer infos de l’app / domaine / politiques / conditions / scopes / utilisateurs de test
- [ ] Identifiants : créer un client **Application Web**
- [ ] Enregistrer la Redirect URI : `https://{domaine}/?social_login=google`
- [ ] SESLP : enregistrer le Client ID/Secret et tester la connexion
- [ ] Modifier le statut de publication lors de la mise en production (soumettre une révision si nécessaire)

</details>

---

<details>
  <summary><strong>Facebook</strong></summary>

> - **Redirect URI :** `https://{domaine}/?social_login=facebook`
> - **Autorisations recommandées :** `public_profile`, `email`
> - Facebook n’utilise pas `openid`.

---

#### 1) Créer l’application et ajouter un produit

(1) Connectez-vous à **Meta for Developers**
[https://developers.facebook.com/](https://developers.facebook.com/)

(2) Cliquez sur **Créer une application** → Type général (Consumer) → Créez

(3) Dans le menu gauche, ajoutez **Facebook Login** sous **Produits**

(4) Accédez à **Paramètres** → Vérifiez :

- **Client OAuth Login :** Activé
- **Web OAuth Login :** Activé
- **Valid OAuth Redirect URIs :**
  - Ajoutez `https://{domaine}/?social_login=facebook`
- (Optionnel) **Forcer HTTPS :** Recommandé

#### 2) Paramètres de base de l’application (App Settings → Basic)

(1) **App Domains :** `example.com` (domaine utilisé pour la page d’accueil, la politique de confidentialité et les conditions d’utilisation de l’app)

(2) **Privacy Policy URL :** Page de politique de confidentialité accessible publiquement

(3) **Terms of Service URL :** Page de conditions d’utilisation accessible publiquement

(4) **User Data Deletion :** Fournir une URL de procédure de suppression des données ou un endpoint dédié

(5) **Category / App Icon :** Configurer de manière appropriée, puis cliquer sur **Save**

#### 3) Permissions et révision

(1) Permissions de base : **`public_profile`**, optionnelle : **`email`**

(2) **`email`** est souvent utilisable sans révision

(3) Les permissions avancées requièrent **App Review** et **Business Verification**

#### 4) Passer de développement à production

- En haut : **Mode : Development → Live**

#### 5) Vérifications :

- [ ] Politiques/TOS/URL de suppression disponibles
- [ ] URI correcte
- [ ] Permissions minimales
- [ ] App Review effectuée

#### 6) Paramètres WordPress (SESLP)

(1) WP Admin → **SESLP Settings → Facebook**

(2) Saisissez **App ID / Secret** → Enregistrez

(3) Testez avec le bouton Facebook

#### 7) Dépannage

(1) **Can't Load URL / redirect_uri error**

→ Assurez-vous que **l’URI est exactement identique** à celle enregistrée dans **Valid OAuth Redirect URIs** (y compris protocole, sous-domaine, slash final et chaîne de requête).

(2) **email null**

→ L’utilisateur n’a pas d’e-mail enregistré auprès de Facebook ou l’adresse est définie comme privée. Préparez une **logique de liaison de compte basée sur l’ID** et expliquez clairement, sur l’écran de consentement, l’usage qui sera fait de l’autorisation e-mail.

(3) **Erreurs liées aux permissions**

→ Si le scope demandé dépasse les permissions de base, une **App Review / Business Verification** est requise.

(4) **Impossible de passer en mode Live**

→ Cela se produit lorsque l’URL de la politique, des conditions d’utilisation ou des directives de suppression des données est **manquante ou non publique**. Vous devez fournir une URL accessible publiquement.

</details>

---

<details>
  <summary><strong>LinkedIn</strong></summary>

> - **Redirect URI :** `https://{domaine}/?social_login=linkedin`
> - **Paramètre requis :** Activer OpenID Connect (OIDC)
> - **Scopes recommandés :** `openid`, `profile`, `email`

---

#### 1) Créer une application

(1) Accédez à la **LinkedIn Developers Console**

→ [https://www.linkedin.com/developers/apps](https://www.linkedin.com/developers/apps)

(2) Connectez-vous avec votre compte LinkedIn

(3) Cliquez sur **Create app**

(4) Renseignez les champs obligatoires :

- **App name :** par ex. `MySite LinkedIn Login`
- **LinkedIn Page :** Sélectionner une page ou « None »
- **App logo :** PNG/JPG 100×100 ou plus
- **Privacy Policy URL / Business Email :** Valides et publics

(5) Cliquez sur **Create app**

> **Development Mode** activé par défaut → permet de tester immédiatement la connexion avec `openid`, `profile`, `email` **sans publication**

#### 2) Activer OpenID Connect (OIDC)

(1) Onglet **Products**

(2) Ajouter **Sign In with LinkedIn using OpenID Connect**

(3) Cliquez sur **Add product** → approuvé immédiatement

(4) Les paramètres OIDC apparaissent dans l’onglet **Auth**

> **Scopes OIDC requis**
>
> - `openid` → Jeton ID
> - `profile` → Nom, photo, titre
> - `email` → Adresse e-mail

#### 3) Paramètres OAuth 2.0 (onglet Auth)

(1) Accédez à **Auth → OAuth 2.0 settings**

(2) Ajoutez dans **Redirect URLs** :

→ `https://{domaine}/?social_login=linkedin`

(3) **Correspondance exacte requise** (protocole, sous-domaine, slash final, chaîne de requête)

(4) Enregistrez plusieurs URL si nécessaire :

- Local : `https://localhost:3000/?social_login=linkedin`
- Staging : `https://staging.example.com/?social_login=linkedin`
- Production : `https://example.com/?social_login=linkedin`

(5) Cliquez sur **Save**

#### 4) Récupérer le Client ID / Client Secret

(1) Dans l’onglet **Auth**, trouvez :

- **Client ID**
- **Client Secret**

(2) WordPress Admin → **SESLP Settings → LinkedIn**

(3) Collez les deux valeurs → **Enregistrer**

(4) Testez avec le **bouton de connexion LinkedIn** sur le frontend

> **Sécurité :**
>
> - Ne divulguez jamais le Client Secret
> - Utilisez **Regenerate secret** en cas de compromission

#### 5) Scopes

| Scope     | Description       | Note       |
| --------- | ----------------- | ---------- |
| `openid`  | Jeton ID OIDC     | **Requis** |
| `profile` | Nom, photo, titre | **Requis** |
| `email`   | Adresse e-mail    | **Requis** |

> **Anciens scopes (`r_liteprofile`, `r_emailaddress`)**
>
> - **Obsolètes après 2024**
> - **Non disponibles pour les nouvelles applications**

#### 6) Dépannage

(1) **redirect_uri_mismatch**

→ Les URIs diffèrent même très légèrement → assurez-vous d’une **correspondance à 100 %**

(2) **invalid_client**

→ ID/Secret incorrects ou application inactive → revérifiez ou régénérez les identifiants

(3) **email NULL**

→ L’utilisateur a refusé ou le scope `email` est manquant → expliquez l’utilisation de l’e-mail sur l’écran de consentement

(4) **insufficient_scope**

→ Le scope demandé n’est pas approuvé → vérifiez que l’OIDC est bien activé

(5) **OIDC not enabled**

→ **Sign In with LinkedIn using OpenID Connect** est absent dans la section Products

> **Journaux (logs) :**
>
> - `/wp-content/seslp-logs/seslp-debug.log`
> - `/wp-content/debug.log`

#### 7) Liste de vérification (résumé)

- [ ] Application créée
- [ ] Produit **OpenID Connect** ajouté
- [ ] Redirect URI enregistrée avec correspondance exacte
- [ ] Client ID/Secret enregistrés dans SESLP
- [ ] Scopes : `openid profile email` (sans anciens scopes)
- [ ] Test effectué sur le frontend en HTTPS

---

> **Remarque :**
>
> - SESLP prend entièrement en charge le **flux OIDC**.
> - L’ancien OAuth 2.0 **n’est plus pris en charge**.
> - Utilisez toujours **OpenID Connect** pour les nouvelles intégrations.

</details>

---

<details>
  <summary><strong>Naver</strong></summary>

> - **Redirect URI :** `https://{domaine}/?social_login=naver`
> - **Scopes recommandés :** `name`, `email`
> - Naver utilise l’API **Naver Login (네아로)**, **HTTPS obligatoire**

---

#### 1) Enregistrement de l’application

(1) Accédez au **Naver Developer Center**

→ [https://developers.naver.com/apps/](https://developers.naver.com/apps/)

(2) Connectez-vous avec votre compte Naver

(3) Cliquez sur **Application → Register Application**

(4) Renseignez les champs obligatoires :

- **Application Name :** par ex. `MySite Naver Login`
- **API Usage :** Sélectionnez `Naver Login (네아로)`
- **Add Environment → Web**
- **Service URL :** `https://example.com`
- **Callback URL :** `https://example.com/?social_login=naver`

(5) Acceptez les conditions → **Register**

> **Remarque :**
>
> - **HTTPS obligatoire** → HTTP non autorisé
> - **Les sous-domaines doivent être enregistrés séparément**

#### 2) Obtenir le Client ID / Client Secret

(1) Allez dans **My Applications**

(2) Cliquez sur l’application → copiez le **Client ID** et le **Client Secret**

#### 3) Paramétrage dans WordPress (plugin)

(1) WP Admin → **SESLP Settings → Naver**

(2) Collez le **Client ID / Client Secret**

(3) Vérifiez que la **Redirect URI** correspond exactement : `https://{domaine}/?social_login=naver`

(4) **Enregistrez** → Testez avec le **bouton de connexion Naver** sur le frontend

#### 4) Autorisations

| Donnée              | Scope   | Note                 |
| ------------------- | ------- | -------------------- |
| Nom                 | `name`  | Par défaut           |
| E-mail              | `email` | Par défaut           |
| Genre, anniversaire | Séparé  | **Révision requise** |

> - Les utilisateurs peuvent **accepter ou refuser** sur l’écran de consentement
> - Si l’e-mail est refusé → `email = null` → utilisez une **liaison de compte basée sur l’ID**
> - Les données sensibles nécessitent une **révision de l’application Naver**

#### 5) Dépannage

(1) **Redirect URI mismatch**

→ Même une légère différence provoque une erreur → assurez-vous d’une **correspondance à 100 %**

(2) **HTTP error**

→ Vous devez utiliser **HTTPS**

(3) **Subdomain error**

→ Enregistrez chaque sous-domaine séparément

(4) **email NULL**

→ L’utilisateur a refusé ou l’e-mail est privé → préparez une logique de liaison basée sur l’ID

(5) **Review needed**

→ Connexion de base : **aucune révision**  
→ Données supplémentaires : **révision requise**

#### 6) Liste de vérification (résumé)

- [ ] Application enregistrée dans Naver Developer Center
- [ ] **Callback URL** enregistrée exactement
- [ ] Utilisation de **HTTPS** vérifiée
- [ ] Sous-domaines enregistrés séparément (si nécessaire)
- [ ] Client ID/Secret enregistrés dans SESLP
- [ ] Comportement d’acceptation/refus de l’e-mail testé
- [ ] Test de connexion sur le frontend terminé

---

> **Remarque :**
>
> - SESLP prend entièrement en charge **Naver Login (네아로)**.
> - La connexion de base (`name`, `email`) est **disponible sans révision**.

</details>

---

<details>
  <summary><strong>Kakao</strong></summary>

> - **Redirect URI :** `https://{domaine}/?social_login=kakao`
> - **Scopes recommandés :** `profile_nickname`, `profile_image`, `account_email`
> - `account_email` disponible **après vérification d’identité ou enregistrement d’entreprise**
> - **HTTPS obligatoire**, **Client Secret doit être activé**

---

#### 1) Créer une application

(1) Accéder à **Kakao Developers**

→ [https://developers.kakao.com/](https://developers.kakao.com/)

(2) Connexion → **Mes applications → Ajouter une application**

(3) Saisir :

- Nom de l’application, nom de l’entreprise
- Catégorie
- Accepter la politique d’exploitation

(4) **Enregistrer**

#### 2) Activer Kakao Login

(1) **Paramètres produit > Kakao Login**

(2) Activer **Kakao Login** → **ON**

(3) **Enregistrer l’URI de redirection**

- `https://{domaine}/?social_login=kakao`
- **Enregistrer**

(4) Le domaine doit correspondre **au domaine du site dans Plateforme**

#### 3) Paramètres de consentement (Scopes)

(1) **Éléments de consentement**

(2) Ajouter et configurer :

| Scope              | Description     | Type de consentement  | Note                     |
| ------------------ | --------------- | --------------------- | ------------------------ |
| `profile_nickname` | Pseudo          | Obligatoire/Optionnel | Basique                  |
| `profile_image`    | Image de profil | Obligatoire/Optionnel | Basique                  |
| `account_email`    | E-mail          | **Optionnel**         | **Vérification requise** |

(3) Indiquer clairement **l’objectif** pour chaque

(4) **Enregistrer**

> Les scopes sensibles nécessitent une **vérification**

#### 4) Enregistrer la plateforme Web

(1) **Paramètres app > Plateforme**

(2) **Enregistrer plateforme Web**

(3) Domaine du site : `https://{domaine}`

(4) **Enregistrer** → Doit correspondre au domaine de l’URI de redirection

#### 5) Sécurité – Générer et activer Client Secret

(1) **Paramètres produit > Sécurité**

(2) **Utiliser Client Secret** → **ON**

(3) **Générer Secret** → Copier la valeur

(4) **État d’activation** → **Actif**

(5) **Enregistrer**

> **Obligation d’activer** après génération

#### 6) Obtenir la clé REST API (Client ID)

(1) **Clés de l’application**

(2) Copier **Clé REST API** → Utiliser comme **Client ID**

#### 7) Paramètres WordPress

(1) WP Admin → **SESLP Settings → Kakao**

(2) **Client ID** = Clé REST API  
 **Client Secret** = Secret généré

(3) **Enregistrer**

(4) Tester avec le **bouton Kakao Login**

#### 8) Dépannage

(1) **redirect_uri_mismatch** → Correspondance 100 % requise

(2) **invalid_client** → Secret non activé ou erreur

(3) **email vide** → Refus utilisateur ou non vérifié

(4) **Incohérence de domaine** → Plateforme vs URI

(5) **HTTP interdit** → **HTTPS uniquement**

> **Journaux :**
>
> - `/wp-content/seslp-logs/seslp-debug.log`
> - `/wp-content/debug.log`

#### 9) Checklist

- [ ] Kakao Login activé
- [ ] URI de redirection enregistrée
- [ ] Domaine de plateforme Web enregistré
- [ ] Éléments de consentement configurés
- [ ] Client Secret généré + activé
- [ ] Clé REST API / Secret dans SESLP
- [ ] Testé en HTTPS

</details>

---

<details>
  <summary><strong>LINE</strong></summary>

> - **Redirect URI :** `https://{domaine}/?social_login=line`
> - **Requis :** Activer OpenID Connect, **demander et obtenir l’approbation pour la permission e-mail**
> - **Scopes recommandés :** `openid`, `profile`, `email`
> - **HTTPS obligatoire**, **approbation préalable requise pour e-mail**

---

#### 1) Créer un Provider et un Canal

(1) Accéder à **LINE Developers Console**

→ [https://developers.line.biz/console/](https://developers.line.biz/console/)

(2) Connexion avec **compte LINE Business** (compte personnel non autorisé)

(3) Cliquer sur **Créer un nouveau provider** → Saisir le nom → **Create**

(4) Sous le provider → onglet **Channels**

(5) Sélectionner **Créer un canal LINE Login**

(6) Configurer :

- **Type de canal :** `LINE Login`
- **Provider :** Sélectionner le provider créé
- **Région :** Pays cible (ex. `South Korea`, `Japan`)
- **Nom / description / icône :** Affichés sur l’écran de consentement

(7) Accepter les conditions → **Create**

#### 2) Activer OpenID Connect & Demander la permission e-mail

(1) Aller dans le menu **OpenID Connect**

(2) Cliquer sur **Apply** à côté de **Email address permission**

(3) Remplir le formulaire :

- **URL de la politique de confidentialité** (doit être accessible publiquement)
- **Capture d’écran de la politique de confidentialité**
- Cocher l’accord et **Submit**

(4) **Le scope `email` ne fonctionne qu’après approbation**  
 → Délai habituel : 1 à 3 jours ouvrables

#### 3) Enregistrer l’URI de redirection & Publier le canal

(1) Aller dans **LINE Login**

(2) Saisir **Callback URL** :

→ `https://{domaine}/?social_login=line`

(3) **Correspondance exacte requise** :

- Protocole : `https://` (**HTTP interdit**)
- Domaine, chemin, chaîne de requête doivent être **100 % identiques**

(4) Cliquer sur **Save**

(5) Changer le statut du canal en **Published**

- **Mode Development :** test uniquement
- **Published :** service en production

#### 4) Récupérer Channel ID / Secret

(1) En haut de la page du canal ou onglet **Basic settings**

(2) **Channel ID** → **Client ID** dans SESLP  
 **Channel Secret** → **Client Secret** dans SESLP

#### 5) Configuration WordPress

(1) WP Admin → **SESLP Settings → LINE**

(2) **Client ID** ← Channel ID  
 **Client Secret** ← Channel Secret

(3) **Enregistrer**

(4) Tester avec le **bouton LINE Login** en frontend

#### 6) Dépannage

(1) **redirect_uri_mismatch** → Toute différence → erreur → **100 % identique**

(2) **invalid_client** → Secret erroné ou canal **non publié**

(3) **email NULL** → **Permission e-mail non approuvée** ou refus utilisateur

(4) **HTTP interdit** → **HTTPS obligatoire** (localhost HTTPS accepté)

(5) **Limite mode Development** → Seuls les comptes de test peuvent se connecter

> **Journaux :**
>
> - `/wp-content/seslp-logs/seslp-debug.log`
> - `/wp-content/debug.log`

#### 7) Checklist

- [ ] Provider + canal LINE Login créés avec compte Business
- [ ] Permission e-mail **demandée et approuvée**
- [ ] **Callback URL** enregistrée exactement
- [ ] **HTTPS utilisé**, statut **Published**
- [ ] Channel ID/Secret saisis dans SESLP
- [ ] Test de connexion frontend effectué

> **Remarque :** SESLP prend en charge
>
> - **LINE Login v2.1 + OpenID Connect**.
> - **La collecte d’e-mail nécessite une approbation préalable**.

</details>
