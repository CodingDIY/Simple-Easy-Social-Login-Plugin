# Simple Easy Social Login

Simple Easy Social Login est une extension WordPress légère et conviviale qui permet d’ajouter une fonctionnalité de connexion sociale rapide et fluide à votre site.

Elle prend en charge **Google, Facebook et LinkedIn (gratuit)**, ainsi que **Naver, Kakao et Line (Premium)**,  
et a été conçue pour fonctionner particulièrement bien sur les sites ciblant les utilisateurs en Asie (Corée, Japon, Chine), mais aussi en Europe et en Amérique du Sud.

L’extension s’intègre parfaitement aux pages de connexion et d’inscription par défaut de WordPress,  
et prend également en charge les formulaires de connexion et d’inscription de WooCommerce.  
Les avatars des profils sociaux peuvent être automatiquement synchronisés avec les profils utilisateurs WordPress.

L’extension repose sur une **architecture de fournisseurs (Providers) extensible**,  
ce qui permet d’ajouter ultérieurement de nouveaux fournisseurs OAuth sous forme d’extensions Add-on indépendantes, si nécessaire.

---

## ✨ Fonctionnalités

- Connexion Google (Gratuit)
- Connexion Facebook (Gratuit)
- Connexion LinkedIn (Gratuit)
- Connexion Naver (Premium)
- Connexion Kakao (Premium)
- Connexion Line (Premium)
- Synchronisation automatique des avatars utilisateurs
- Association automatique des comptes WordPress existants par e-mail
- URLs de redirection personnalisées après la connexion, la déconnexion et l’inscription
- Interface d’administration simple et claire pour la configuration des fournisseurs
- Prise en charge du shortcode : [se_social_login]
- Affichage automatique sur les formulaires de connexion et d’inscription WordPress
- Prise en charge des formulaires de connexion et d’inscription WooCommerce (optionnelle)
- Structure légère respectant les standards de codage WordPress
- Aucune création inutile de tables dans la base de données
- Système de fournisseurs extensible permettant l’ajout de nouveaux fournisseurs OAuth via des extensions Add-on

---

## 🐞 Journal de débogage

SESLP inclut un système de journalisation intégré pour diagnostiquer les problèmes OAuth et de connexion sociale.

Des explications détaillées sont disponibles directement dans l’administration WordPress :
**SESLP → Guides → Debug Log & Troubleshooting**

Les fichiers de log sont générés ici :

- `/wp-content/SESLP-debug.log`
- `/wp-content/debug.log` (si `WP_DEBUG_LOG` est activé)

---

## 🚀 Installation

1. Téléversez l’extension dans le répertoire `/wp-content/plugins/simple-easy-social-login/`.
2. Activez l’extension depuis **Extensions → Extensions installées** dans l’administration WordPress.
3. Accédez à **Réglages → Simple Easy Social Login**.
4. Saisissez le Client ID et le Client Secret pour chaque fournisseur de connexion sociale.
5. Enregistrez les modifications.
6. Vérifiez que les boutons de connexion sociale s’affichent correctement sur le site.

---

## ❓ Foire aux questions

### Cette extension fonctionne-t-elle avec WooCommerce ?

Oui. Elle s’intègre aux formulaires de connexion et d’inscription de WooCommerce.

### Puis-je utiliser uniquement la connexion Google ?

Oui. Chaque fournisseur peut être activé ou désactivé individuellement.

### Quand ai-je besoin d’une licence Premium ?

Une licence Premium est requise pour utiliser les connexions **Naver, Kakao et Line**.  
Google, Facebook et LinkedIn sont disponibles gratuitement.

### Un shortcode est-il disponible ?

Oui. Vous pouvez insérer les boutons de connexion sociale à n’importe quel endroit à l’aide du shortcode suivant : [se_social_login]

### Les avatars des utilisateurs sont-ils importés automatiquement ?

Oui. Pour certains fournisseurs tels que Google et Facebook, les images de profil peuvent être automatiquement importées et synchronisées comme avatars WordPress.

---

## 🖼 Captures d’écran

1. Page de réglages dans l’administration
2. Exemple de boutons de connexion sociale
3. Fournisseurs Premium (Naver / Kakao / Line)
4. Exemple d’intégration avec le formulaire de connexion WordPress

---

## 📝 Journal des modifications (Changelog)

### 1.9.7

- Ajout d’une section de journal de débogage dans la README
- Intégration du guide détaillé des logs dans les guides d’administration (multilingue)
- Uniformisation du chemin du fichier de log (`/wp-content/SESLP-debug.log`)
- Nettoyage et amélioration de la cohérence de la documentation

### 1.9.6

- Amélioration de l’ergonomie de la page des réglages
- Ajout d’un bouton pour afficher/masquer les clés secrètes
- Correction des conflits avec les styles du cœur WordPress
- Amélioration de la détection des plans Pro / Max

### 1.9.5

- Refonte majeure
- Unification des helpers et amélioration de l’architecture des fournisseurs
- Nettoyage de l’interface des réglages
- Amélioration de la stabilité et de la maintenabilité

### 1.9.3

- Mise à jour des traductions des guides
- Ajout de l’affichage du shortcode sur la page des réglages

### 1.9.2

- Nettoyage de la structure interne
- Ajout de la classe de chargement des guides
- Restructuration des templates
- Amélioration de la stabilité du chargement des réglages et des styles CSS

### 1.9.1

- Ajout de la page Guide administrateur
- Rendu de la documentation multilingue basé sur Markdown (Parsedown)
- Amélioration du style de l’interface

### 1.9.0

- Phase de préparation à une refonte majeure
- Extension des helpers i18n
- Amélioration du formatage sécurisé et du système de journalisation

### 1.7.23

- Mise à jour des traductions

### 1.7.22

- Amélioration des messages de débogage pour afficher le fournisseur précédemment utilisé

### 1.7.21

- Affichage du nom du fournisseur dans les messages d’erreur lors des inscriptions avec e-mail dupliqué
- Masquage automatique des messages d’erreur après 10 secondes via JavaScript

### 1.7.19

- Prévention de la création de comptes en double avec la même adresse e-mail
- Amélioration du flux OAuth :
  - `get_access_token()`
  - `get_user_info()`
  - `create_or_link_user()`

### 1.7.18

- Suppression des info-bulles des champs Google Client ID / Secret
- Nettoyage de la structure du code
- Suppression du texte « (Email required) » du bouton de connexion Line

### 1.7.17

- Correction des problèmes liés à la connexion Line :
  - Prévention de la création de comptes en double lors de la reconnexion
  - Correction du problème de réapparition de la page `/complete-profile`
  - Autorisation de la mise à jour de l’adresse e-mail pour corriger l’erreur « Invalid request »
- Centralisation des logs de débogage avec `SESLP_Logger`

### 1.7.16

- Masquage des clés de licence dans les logs de débogage (ex. : abc\*\*\*\*123)
- Ajout d’un guide pour vérifier `wp_options` lors du débogage
- Ajout d’une notification administrateur en cas d’échec de l’écriture des logs

### 1.7.15

- Correction des échecs d’écriture des logs de débogage
- Application du fuseau horaire local de WordPress aux horodatages
- Ajout de logs de débogage lors de l’enregistrement des réglages

### 1.7.5

- Application des derniers correctifs de sécurité
- Optimisations des performances et amélioration de l’expérience utilisateur

### 1.7.0

- Amélioration de la synchronisation des boutons de connexion sociale
- Renforcement de la sécurité et corrections de bugs

### 1.7.3

- Amélioration du système de débogage
- Ajout d’un répertoire debug dédié

### 1.6.0

- Restauration de l’affichage de la section clé de licence lors de la sélection Plus / Premium

### 1.5.0

- Enregistrement de l’option `seslp_license_type`
- Correction du problème de réinitialisation du type de licence à Free lors de l’enregistrement

### 1.4.0

- Correction du problème de chargement du fichier `style.css` dans l’administration via `admin_enqueue_scripts`

### 1.3.0

- Amélioration de l’interface des boutons radio
- Déplacement du CSS inline vers `style.css`

### 1.2.0

- Ajout de la sélection du type de licence (Free / Plus / Premium)
- Amélioration de l’alignement de l’interface des réglages

### 1.1.0

- Ajout du support multilingue et du chargement des fichiers de traduction
- Amélioration de la logique d’authentification

### 1.0.0

- Première version
- Ajout des connexions sociales Google, Facebook, Naver, Kakao, Line et Weibo

---

## 📄 Licence

GPLv2 or later  
https://www.gnu.org/licenses/gpl-2.0.html
