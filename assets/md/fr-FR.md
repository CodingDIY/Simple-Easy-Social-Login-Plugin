# Simple Easy Social Login (SESLP) — Guide de connexion sociale (Français)

> Ce document explique comment configurer chaque fournisseur de connexion  
> (Google, Facebook, LinkedIn, Naver, Kakao, LINE) dans le plugin **Simple Easy Social Login (SESLP)**.  
> Toutes les connexions reposent sur **OAuth 2.0 / OpenID Connect (OIDC)**.  
> Vous devez créer une application (client) dans la console de chaque fournisseur et saisir le **Client ID / Client Secret** dans SESLP.

---

## 🔧 Guide de configuration commun

- **Règle de Redirect URI :**  
  `https://{votre-domaine}/?social_login={provider}`  
  Exemples :

  - Google → `https://example.com/?social_login=google`
  - Facebook → `https://example.com/?social_login=facebook`
  - LinkedIn → `https://example.com/?social_login=linkedin`
  - Naver → `https://example.com/?social_login=naver`
  - Kakao → `https://example.com/?social_login=kakao`
  - LINE → `https://example.com/?social_login=line`

- **HTTPS requis**  
  La plupart des fournisseurs exigent HTTPS et refusent les redirections `http://`.

- **Correspondance exacte**  
  La Redirect URI enregistrée dans la console doit correspondre **à 100 %** à celle envoyée par SESLP  
  (protocole, sous-domaine, chemin, slash final et chaîne de requête).

- **L’e-mail peut être indisponible**  
  Certains fournisseurs permettent à l’utilisateur de refuser le partage de l’e-mail. SESLP peut alors utiliser l’ID stable du fournisseur pour lier les comptes.

- **Où consulter les journaux (logs)**
  - `/wp-content/seslp-logs/seslp-debug.log`
  - `/wp-content/debug.log` (`WP_DEBUG_LOG = true`)

---

## 🌍 Guides par fournisseur

> Déployez chaque fournisseur ci-dessous et collez le guide correspondant en français lorsque vous l’aurez préparé.

---

<details open>
  <summary><strong>Google</strong></summary>

> **Scopes recommandés :** `openid email profile`  
> **Règle de Redirect URI :** `https://{domaine}/?social_login=google`

---

### 1) Préparation (liste obligatoire)

- **HTTPS recommandé/obligatoire** (certificat de développement fiable).
- La Redirect URI doit correspondre **exactement à 100 %**.
- En mode test, seuls les **utilisateurs de test** peuvent se connecter (jusqu’à 100).
- Si vous utilisez des URLs de politique/confidentialité, ajoutez des **Authorized domains** et vérifiez la propriété du domaine.

### 2) Configurer le projet et l’écran de consentement

1. Accédez à la **Google Cloud Console**
   - <https://console.cloud.google.com/apis/credentials>
2. Créez ou sélectionnez un projet.
3. Menu latéral : **APIs & Services → OAuth consent screen**.
4. Type d’utilisateur : **External**.
5. Renseignez les **informations de l’application**.
6. **App domain** : ajoutez vos URLs, domaine racine → Enregistrer.
7. Configurez les **Scopes** (`openid email profile`).
8. Ajoutez des **utilisateurs de test** → Sauvegardez.

> Avec uniquement les scopes de base, la publication se fait souvent **sans révision**.

### 3) Créer le client OAuth (Web application)

1. **APIs & Services → Credentials**.
2. **+ Create Credentials → OAuth client ID**.
3. Type : `Web application`.
4. Nom : `SESLP – Front`.
5. **Authorized redirect URIs :**
   - `https://{domaine}/?social_login=google`
6. Copiez **Client ID / Client Secret**.

### 4) Configurer dans WordPress

1. WP Admin → **SESLP Settings → Google**.
2. Collez **Client ID / Secret** → **Enregistrer**.
3. Testez avec le bouton Google.

### 5) Passer en production

1. Vérifiez le statut de publication.
2. Supprimez les scopes inutiles.
3. Soumettez à révision si scopes sensibles.

### 6) Erreurs courantes

- **redirect_uri_mismatch** – URI différente → corriger.
- **access_denied** – restriction du navigateur → essayer ailleurs.
- **invalid_client** – identifiants erronés → vérifier.
- **E-mail vide** – vérifier scopes et confidentialité.

</details>

---

<details>
  <summary><strong>Facebook (Meta)</strong></summary>

> **Redirect URI :** `https://{domaine}/?social_login=facebook`  
> **Autorisations recommandées :** `public_profile`, `email`  
> ※ Facebook n’utilise pas `openid`.

---

### 1) Créer l’application et ajouter un produit

1. Connectez-vous à **Meta for Developers**
2. Cliquez sur **Créer une application** → Type général (Consumer) → Créez
3. Dans le menu gauche, ajoutez **Facebook Login** sous **Produits**
4. Accédez à **Paramètres** → Vérifiez :
   - **Client OAuth Login :** Activé
   - **Web OAuth Login :** Activé
   - **Valid OAuth Redirect URIs :**
     - Ajoutez `https://{domaine}/?social_login=facebook`
   - (Optionnel) **Forcer HTTPS :** Recommandé

### 2) Paramètres de base

- **App Domains :** `example.com`
- **Privacy Policy URL :** Page publique
- **Terms of Service URL :** Page publique
- **User Data Deletion :** URL ou endpoint public
- **Catégorie / Icône :** Définir → Enregistrer

### 3) Permissions et révision

- Permissions de base : **`public_profile`**, optionnelle : **`email`**
- **`email`** est souvent utilisable sans révision
- Les permissions avancées requièrent **App Review** et **Business Verification**

### 4) Passer de développement à production

- En haut : **Mode : Development → Live**
- Vérifications :
  - [ ] Politiques/TOS/URL de suppression disponibles
  - [ ] URI correcte
  - [ ] Permissions minimales
  - [ ] App Review effectuée

### 5) Paramètres WordPress (SESLP)

1. WP Admin → **SESLP Settings → Facebook**
2. Saisissez **App ID / Secret** → Enregistrez
3. Testez avec le bouton Facebook

### 6) Dépannage

- **redirect_uri error** → Vérifiez l’URI exacte
- **email null** → Pas d’e-mail partagé
- **Erreur de permissions** → App Review requise
- **Impossible de passer en Live** → URLs manquantes ou privées
</details>

---

<details>
  <summary><strong>LinkedIn</strong></summary>

> **Redirect URI :** `https://{domaine}/?social_login=linkedin`  
> **Paramètre requis :** Activer OpenID Connect (OIDC)  
> **Scopes recommandés :** `openid`, `profile`, `email`

---

### 1) Créer une application

1. Accéder au **LinkedIn Developers Console**  
   → [https://www.linkedin.com/developers/apps](https://www.linkedin.com/developers/apps)
2. Connexion avec compte LinkedIn
3. Cliquer sur **Create app**
4. Remplir les champs obligatoires :
   - **App name**, **LinkedIn Page** (ou “None”)
   - **Logo** : 100×100 min
   - **URL de politique de confidentialité / Email professionnel**
5. **Create app**

> Mode développement par défaut → test immédiat sans publication

---

### 2) Activer OpenID Connect (OIDC)

1. Onglet **Products**
2. Ajouter **Sign In with LinkedIn using OpenID Connect**
3. Paramètres OIDC disponibles dans **Auth**

---

### 3) Paramètres OAuth 2.0

1. **Auth → OAuth 2.0 settings**
2. Ajouter : `https://{domaine}/?social_login=linkedin`
3. **Correspondance exacte** requise
4. Enregistrer plusieurs URI si nécessaire
5. **Save**

---

### 4) Client ID / Secret

1. Dans **Auth**, copier **Client ID** et **Client Secret**
2. WP Admin → **SESLP → LinkedIn**
3. Coller → **Enregistrer**
4. Tester le bouton LinkedIn

---

### 5) Scopes

| Scope     | Description       | Note       |
| --------- | ----------------- | ---------- |
| `openid`  | Jeton ID OIDC     | **Requis** |
| `profile` | Nom, photo, titre | **Requis** |
| `email`   | Adresse e-mail    | **Requis** |

> Anciens scopes **obsolètes**

---

### 6) Dépannage

- **redirect_uri_mismatch** → Vérifier l’URI exacte
- **invalid_client** → ID/Secret erronés
- **email NULL** → Scope manquant ou refus utilisateur
- **OIDC non activé** → Ajouter le produit

---

### 7) Checklist

- [ ] App créée
- [ ] OIDC activé
- [ ] URI de redirection enregistrée
- [ ] ID/Secret dans SESLP
- [ ] Test en HTTPS

</details>

---

<details>
  <summary><strong>Naver</strong></summary>

> **Redirect URI :** `https://{domaine}/?social_login=naver`  
> **Scopes recommandés :** `name`, `email`  
> ※ Naver utilise l’API **Naver Login (네아로)**, **HTTPS obligatoire**

---

### 1) Enregistrement de l’application

1. Accéder au **Naver Developer Center**  
   → [https://developers.naver.com/apps/](https://developers.naver.com/apps/)
2. Connexion avec compte Naver
3. **Application → Enregistrer**
4. Renseigner :
   - Nom, API : `Naver Login`
   - Environnement Web : URL du site, **Callback URL**
5. Accepter et **Enregistrer**

> HTTPS obligatoire, sous-domaines séparés

---

### 2) Client ID / Secret

1. **Mes applications** → copier ID et Secret

---

### 3) Configuration WordPress

1. WP Admin → **SESLP → Naver**
2. Coller ID/Secret
3. Vérifier URI exacte
4. **Enregistrer** → Tester

---

### 4) Autorisations

| Donnée              | Scope   | Note                 |
| ------------------- | ------- | -------------------- |
| Nom                 | `name`  | Par défaut           |
| E-mail              | `email` | Par défaut           |
| Genre, anniversaire | Séparé  | **Révision requise** |

> E-mail refusé → `null` → liaison par ID

---

### 5) Dépannage

- **redirect_uri_mismatch** → URI exacte
- **HTTP interdit** → HTTPS uniquement
- **Sous-domaine** → enregistrement séparé
- **email NULL** → refus utilisateur

---

### 6) Checklist

- [ ] App enregistrée
- [ ] Callback URL exacte
- [ ] HTTPS
- [ ] ID/Secret dans SESLP
- [ ] Test consentement e-mail

</details>

---

<details>
  <summary><strong>Kakao</strong></summary>

> **Redirect URI :** `https://{domaine}/?social_login=kakao`  
> **Scopes recommandés :** `profile_nickname`, `profile_image`, `account_email`  
> ※ `account_email` disponible **après vérification d’identité ou enregistrement d’entreprise**  
> ※ **HTTPS obligatoire**, **Client Secret doit être activé**

---

### 1) Créer une application

1. Accéder à **Kakao Developers**  
   → [https://developers.kakao.com/](https://developers.kakao.com/)
2. Connexion → **Mes applications → Ajouter une application**
3. Saisir :
   - Nom de l’application, nom de l’entreprise
   - Catégorie
   - Accepter la politique d’exploitation
4. **Enregistrer**

---

### 2) Activer Kakao Login

1. **Paramètres produit > Kakao Login**
2. Activer **Kakao Login** → **ON**
3. **Enregistrer l’URI de redirection**
   - `https://{domaine}/?social_login=kakao`
   - **Enregistrer**
4. Le domaine doit correspondre **au domaine du site dans Plateforme**

---

### 3) Paramètres de consentement (Scopes)

1. **Éléments de consentement**
2. Ajouter et configurer :

| Scope              | Description     | Type de consentement  | Note                     |
| ------------------ | --------------- | --------------------- | ------------------------ |
| `profile_nickname` | Pseudo          | Obligatoire/Optionnel | Basique                  |
| `profile_image`    | Image de profil | Obligatoire/Optionnel | Basique                  |
| `account_email`    | E-mail          | **Optionnel**         | **Vérification requise** |

3. Indiquer clairement **l’objectif** pour chaque
4. **Enregistrer**

> Les scopes sensibles nécessitent une **vérification**

---

### 4) Enregistrer la plateforme Web

1. **Paramètres app > Plateforme**
2. **Enregistrer plateforme Web**
3. Domaine du site : `https://{domaine}`
4. **Enregistrer** → Doit correspondre au domaine de l’URI de redirection

---

### 5) Sécurité – Générer et activer Client Secret

1. **Paramètres produit > Sécurité**
2. **Utiliser Client Secret** → **ON**
3. **Générer Secret** → Copier la valeur
4. **État d’activation** → **Actif**
5. **Enregistrer**
   > **Obligation d’activer** après génération

---

### 6) Obtenir la clé REST API (Client ID)

1. **Clés de l’application**
2. Copier **Clé REST API** → Utiliser comme **Client ID**

---

### 7) Paramètres WordPress

1. WP Admin → **SESLP Settings → Kakao**
2. **Client ID** = Clé REST API  
   **Client Secret** = Secret généré
3. **Enregistrer**
4. Tester avec le **bouton Kakao Login**

---

### 8) Dépannage

- **redirect_uri_mismatch** → Correspondance 100 % requise
- **invalid_client** → Secret non activé ou erreur
- **email vide** → Refus utilisateur ou non vérifié
- **Incohérence de domaine** → Plateforme vs URI
- **HTTP interdit** → **HTTPS uniquement**

> **Journaux :**  
> `/wp-content/seslp-logs/seslp-debug.log`  
> `/wp-content/debug.log`

---

### 9) Checklist

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

> **Redirect URI :** `https://{domaine}/?social_login=line`  
> **Requis :** Activer OpenID Connect, **demander et obtenir l’approbation pour la permission e-mail**  
> **Scopes recommandés :** `openid`, `profile`, `email`  
> ※ **HTTPS obligatoire**, **approbation préalable requise pour e-mail**

---

### 1) Créer un Provider et un Canal

1. Accéder à **LINE Developers Console**  
   → [https://developers.line.biz/console/](https://developers.line.biz/console/)
2. Connexion avec **compte LINE Business** (compte personnel non autorisé)
3. Cliquer sur **Créer un nouveau provider** → Saisir le nom → **Create**
4. Sous le provider → onglet **Channels**
5. Sélectionner **Créer un canal LINE Login**
6. Configurer :
   - **Type de canal :** `LINE Login`
   - **Provider :** Sélectionner le provider créé
   - **Région :** Pays cible (ex. `South Korea`, `Japan`)
   - **Nom / description / icône :** Affichés sur l’écran de consentement
7. Accepter les conditions → **Create**

---

### 2) Activer OpenID Connect & Demander la permission e-mail

1. Aller dans le menu **OpenID Connect**
2. Cliquer sur **Apply** à côté de **Email address permission**
3. Remplir le formulaire :
   - **URL de la politique de confidentialité** (doit être accessible publiquement)
   - **Capture d’écran de la politique de confidentialité**
   - Cocher l’accord et **Submit**
4. **Le scope `email` ne fonctionne qu’après approbation**  
   → Délai habituel : 1 à 3 jours ouvrables

---

### 3) Enregistrer l’URI de redirection & Publier le canal

1. Aller dans **LINE Login**
2. Saisir **Callback URL** :  
   → `https://{domaine}/?social_login=line`
3. **Correspondance exacte requise** :
   - Protocole : `https://` (**HTTP interdit**)
   - Domaine, chemin, chaîne de requête doivent être **100 % identiques**
4. Cliquer sur **Save**
5. Changer le statut du canal en **Published**
   - **Mode Development :** test uniquement
   - **Published :** service en production

---

### 4) Récupérer Channel ID / Secret

1. En haut de la page du canal ou onglet **Basic settings**
2. **Channel ID** → **Client ID** dans SESLP  
   **Channel Secret** → **Client Secret** dans SESLP

---

### 5) Configuration WordPress

1. WP Admin → **SESLP Settings → LINE**
2. **Client ID** ← Channel ID  
   **Client Secret** ← Channel Secret
3. **Enregistrer**
4. Tester avec le **bouton LINE Login** en frontend

---

### 6) Dépannage

- **redirect_uri_mismatch** → Toute différence → erreur → **100 % identique**
- **invalid_client** → Secret erroné ou canal **non publié**
- **email NULL** → **Permission e-mail non approuvée** ou refus utilisateur
- **HTTP interdit** → **HTTPS obligatoire** (localhost HTTPS accepté)
- **Limite mode Development** → Seuls les comptes de test peuvent se connecter

> **Journaux :**  
> `/wp-content/seslp-logs/seslp-debug.log`  
> `/wp-content/debug.log`

---

### 7) Checklist

- [ ] Provider + canal LINE Login créés avec compte Business
- [ ] Permission e-mail **demandée et approuvée**
- [ ] **Callback URL** enregistrée exactement
- [ ] **HTTPS utilisé**, statut **Published**
- [ ] Channel ID/Secret saisis dans SESLP
- [ ] Test de connexion frontend effectué

---

> **Remarque :** SESLP prend en charge **LINE Login v2.1 + OpenID Connect**.  
> **La collecte d’e-mail nécessite une approbation préalable**.

</details>

---

## 📋 Récapitulatif

| Offre   | Fournisseur  | Scopes requis / recommandés                          | Exemple d’URI de redirection               | Remarques                     |
| ------- | ------------ | ---------------------------------------------------- | ------------------------------------------ | ----------------------------- |
| Gratuit | **Google**   | `openid email profile`                               | `https://{domaine}/?social_login=google`   | Écran de consentement externe |
| Gratuit | **Facebook** | `public_profile`, `email`                            | `https://{domaine}/?social_login=facebook` | `openid` non utilisé          |
| Gratuit | **LinkedIn** | `openid profile email`                               | `https://{domaine}/?social_login=linkedin` | Migration OIDC complète       |
| Payant  | **Naver**    | `email`, `name`                                      | `https://{domaine}/?social_login=naver`    | API « Naver Login »           |
| Payant  | **Kakao**    | `profile_nickname`, `profile_image`, `account_email` | `https://{domaine}/?social_login=kakao`    | Client Secret requis          |
| Payant  | **LINE**     | `openid profile email`                               | `https://{domaine}/?social_login=line`     | Doit être « Published »       |
