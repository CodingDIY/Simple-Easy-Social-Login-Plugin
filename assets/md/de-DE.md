> Dieses Dokument erklärt, wie Sie jeden Social-Login-Anbieter  
> (Google, Facebook, LinkedIn, Naver, Kakao, LINE) im Plugin **Simple Easy Social Login (SESLP)** konfigurieren.  
> Alle Anmeldungen basieren auf **OAuth 2.0 / OpenID Connect (OIDC)**.  
> In der Konsole jedes Anbieters erstellen Sie eine App (Client) und tragen **Client-ID / Client-Secret** in SESLP ein.

---

## 🔧 Allgemeine Einrichtung

#### 1) **Regel für die Redirect-URI:**

`https://{Ihre-Domain}/?social_login={provider}`

Beispiele:

- Google → `https://example.com/?social_login=google`
- Facebook → `https://example.com/?social_login=facebook`
- LinkedIn → `https://example.com/?social_login=linkedin`
- Naver → `https://example.com/?social_login=naver`
- Kakao → `https://example.com/?social_login=kakao`
- LINE → `https://example.com/?social_login=line`

#### 2) **HTTPS ist erforderlich**

Die meisten Anbieter verlangen HTTPS und lehnen `http://`-Weiterleitungen ab.

#### 3) **Exakte Übereinstimmung**

Die in der Konsole eingetragene Redirect-URI muss zu **100 %** mit der von SESLP gesendeten URI übereinstimmen  
 (Protokoll, Subdomain, Pfad, abschließender Slash und Query-String).

#### 4) **E-Mail kann fehlen**

Einige Anbieter erlauben es Nutzern, die Weitergabe der E-Mail zu verweigern. SESLP kann dann stabile Anbieter-IDs zur Kontoverknüpfung verwenden.

#### 5) **Log-Dateien**

- `/wp-content/seslp-logs/seslp-debug.log`
- `/wp-content/debug.log` (`WP_DEBUG_LOG = true`)

---

## 🌍 Anbieter-Leitfäden

> Klappen Sie unten den jeweiligen Anbieter auf und fügen Sie den vorbereiteten Leitfaden für diesen Anbieter ein.

---

<details open>
  <summary><strong>Google</strong></summary>

> - **Empfohlene Scopes:** `openid email profile`
> - **Redirect-URI-Regel:** `https://{domain}/?social_login=google`

---

#### 1) Vorbereitung (Pflicht-Checkliste)

(1) **HTTPS empfohlen / erforderlich** (vertrauenswürdiges Zertifikat für lokale Umgebungen).

(2) Die Redirect-URI muss **zu 100 %** mit dem in der Konsole registrierten Wert übereinstimmen.  
 Beispiel: `https://example.com/?social_login=google`

(3) Im Testmodus können nur **Testbenutzer** sich anmelden (max. 100).

(4) Bei Verwendung von Startseite-/Datenschutz-/Nutzungsbedingungs-URLs: **Authorized domains** registrieren und **Domaininhaberschaft prüfen**.

#### 2) Projekt- und Einverständnisbildschirm konfigurieren

(1) Öffnen Sie die **Google Cloud Console**
[https://console.cloud.google.com/apis/credentials](https://console.cloud.google.com/apis/credentials)

(2) Projekt auswählen oder **neues Projekt erstellen**.

(3) Seitenleiste: **APIs & Services → OAuth consent screen**.

(4) **Benutzertyp:** meist **External**.

(5) **App-Informationen** eingeben

- Name, Support-E-Mail, (optional) Logo.

(6) **App domain**

- URLs für Startseite, Datenschutz, Nutzungsbedingungen
- **Root-Domain (z. B. example.com)** zu **Authorized domains** hinzufügen → **Speichern**
- _Falls nötig:_ Inhaberschaft über Search Console prüfen.

(7) **Scopes** konfigurieren

- **Empfohlen:** `openid`, `email`, `profile`
- Sensible Scopes erfordern ggf. Prüfung vor Veröffentlichung.

(8) **Testbenutzer** hinzufügen.

(9) **Speichern**.

> Hinweis: Nur Basis-Scopes → oft **ohne Prüfung** veröffentlichen.

#### 3) OAuth-Client (Webanwendung) erstellen

(1) Seitenleiste: **APIs & Services → Credentials**.

(2) **+ Create Credentials → OAuth client ID**.

(3) Anwendungstyp: `Web application`.

(4) Name: z. B. `SESLP – Front`.

(5) **Authorized redirect URIs**:

- `https://{domain}/?social_login=google`

(6) **Erstellen** → **Client ID / Secret** kopieren.

> (Optional) JavaScript-Ursprünge meist nicht nötig.

#### 4) WordPress-Plugin einrichten

(1) WP-Admin → **SESLP Settings → Google**.

(2) **Client ID / Secret** einfügen → **Speichern**.

(3) Mit Google-Button im Frontend testen.

#### 5) Von Test zu Produktion wechseln

(1) **Publishing status** prüfen.

(2) Vor Veröffentlichung:

- App-Infos prüfen.
- Unnötige Scopes entfernen.
- Bei sensiblen Scopes Prüfung beantragen.

(3) Danach können alle Google-Konten sich anmelden.

#### 6) Häufige Fehler & Lösungen

(1) **redirect_uri_mismatch**

→ URIs unterschiedlich (Protokoll, Subdomain, etc.) → Exakt angleichen.

(2) **access_denied / disallowed_useragent**

→ Browser-/App-Einschränkung → Standard-Browser verwenden.

(3) **invalid_client / unauthorized_client**

→ ID/Secret-Fehler oder App deaktiviert → Prüfen/erneuern.

(4) **E-Mail leer**

→ Scope `email` prüfen, Einwilligungsbildschirm, Sichtbarkeit. Verwendung der E-Mail-Berechtigung im Einwilligungsbildschirm klar erklären.

> **Logs:**
>
> - `wp-content/seslp-logs/seslp-debug.log` (Plugin-Debug-Modus aktiviert)
> - `wp-content/debug.log` (WP_DEBUG, WP_DEBUG_LOG = true)

#### 7) Zusammenfassungs-Checkliste

- [ ] Einverständnisbildschirm: Infos, Domain, Richtlinien, Scopes, Testnutzer
- [ ] Web-Client erstellt
- [ ] Exakte Redirect-URI: `https://{domain}/?social_login=google`
- [ ] SESLP: Speichern & Test
- [ ] Veröffentlichung (ggf. Prüfung)

</details>

---

<details>
  <summary><strong>Facebook</strong></summary>

> - **Redirect URI:** `https://{domain}/?social_login=facebook`
> - **Empfohlene Berechtigungen:** `public_profile`, `email`
> - Facebook verwendet kein `openid`.

---

#### 1) App erstellen und Produkt hinzufügen

(1) Gehen Sie zu **Meta for Developers** → Anmelden

(2) Klicken Sie auf **App erstellen** → Wählen Sie „Consumer“ (oder allgemein) → App erstellen

(3) In der linken Seitenleiste unter **Produkte** → **Facebook Login** hinzufügen

(4) Navigieren Sie zu **Einstellungen** → Überprüfen Sie Folgendes:

- **Client OAuth Login:** EIN
- **Web OAuth Login:** EIN
- **Gültige OAuth-Redirect-URIs:**
  - Fügen Sie `https://{domain}/?social_login=facebook` hinzu
- (Optional) **HTTPS erzwingen:** Standardmäßig empfohlen

#### 2) Grundeinstellungen der App (App Settings → Basic)

(1) **App Domains:** `example.com` (Domain Ihrer Datenschutz-/Nutzungsseiten)

(2) **Privacy Policy URL:** Öffentlich zugängliche Datenschutzseite

(3) **Terms of Service URL:** Öffentlich zugängliche Nutzungsbedingungen

(4) **User Data Deletion:** URL für Richtlinien oder Endpunkt zur Datenlöschung

(5) **Kategorie / App-Icon:** Entsprechend festlegen → **Speichern**

#### 3) Berechtigungen (Scopes) & App-Überprüfung

(1) Standardberechtigungen: **`public_profile`**, optionale: **`email`**

(2) In den meisten Fällen kann **`email` ohne Überprüfung** verwendet werden, aber es können regionale/Konto-spezifische Ausnahmen geben

(3) Erweiterte Berechtigungen (z. B. Seiten/Anzeigen) erfordern **App Review** und **Business Verification**

#### 4) Modus wechseln (Entwicklung → Live)

- Oben oder in den App-Einstellungen **Modus: Development → Live** umschalten

#### 5) Vor dem Wechsel überprüfen:

- [ ] Privacy Policy / Terms / Data Deletion URL vorbereitet
- [ ] Redirect URIs korrekt eingegeben
- [ ] Nur notwendige Berechtigungen aktiv
- [ ] (Falls nötig) App Review/Business Verification abgeschlossen

#### 6) WordPress-Einstellungen (SESLP)

(1) WP-Admin → **SESLP Settings → Facebook**

(2) **App-ID / App-Secret** eingeben → **Speichern**

(3) Testen Sie den **Facebook-Login-Button** im Frontend

#### 6) Fehlerbehebung

(1) **Can't Load URL / redirect_uri-Fehler**

→ Prüfen Sie, ob die **exakte URI** in **Valid OAuth Redirect URIs** registriert ist (einschließlich Protokoll, Subdomain, Slash, Query-String)

(2) **E-Mail fehlt (null)**

→ Benutzer hat keine E-Mail registriert oder sie ist privat. Bereiten Sie eine **ID-basierte Kontoverknüpfungslogik** vor und erklären Sie die Verwendung der E-Mail-Berechtigung klar im Einwilligungsbildschirm

(3) **Berechtigungsfehler**

→ Wenn der angeforderte Scope den Basisbereich überschreitet, ist **App Review/Business Verification** erforderlich

(4) **Live-Modus nicht verfügbar**

→ Wenn Datenschutz-/Nutzungsbedingungen-/Datenlöschungs-URLs **fehlen oder nicht öffentlich** sind. Öffentliche URLs sind obligatorisch

</details>

---

<details>
  <summary><strong>LinkedIn</strong></summary>

> - **Redirect URI:** `https://{domain}/?social_login=linkedin`
> - **Erforderlich:** OpenID Connect (OIDC) aktivieren
> - **Empfohlene Scopes:** `openid`, `profile`, `email`

---

#### 1) App erstellen

(1) **LinkedIn Developers Console**

→ [https://www.linkedin.com/developers/apps](https://www.linkedin.com/developers/apps)

(2) Einloggen

(3) **Create app**

(4) Pflichtfelder:

- Name, Seite, Logo, Datenschutz-URL, E-Mail

(5) **Erstellen**

> **Development Mode** standardmäßig aktiviert → sofortiges Testen der Anmeldung mit `openid`, `profile`, `email` **ohne Veröffentlichung**

#### 2) OpenID Connect (OIDC) aktivieren

(1) Zum Reiter **Products** wechseln

(2) **Sign In with LinkedIn using OpenID Connect** finden

(3) Auf **Add product** klicken → wird in der Regel sofort genehmigt

(4) Die OIDC-Einstellungen erscheinen im Reiter **Auth**

> **Erforderliche OIDC-Scopes**
>
> - `openid` → ID-Token
> - `profile` → Name, Foto, Überschrift
> - `email` → E-Mail-Adresse

#### 3) OAuth-Einstellungen

(1) **Auth → OAuth 2.0 settings**

(2) Redirect URL: `https://{domain}/?social_login=linkedin`

(3) Exakte Übereinstimmung

(4) **Speichern**

#### 4) Client ID / Client Secret abrufen

(1) Im Reiter **Auth** finden Sie:

- **Client ID**
- **Client Secret**

(2) WordPress-Admin → **SESLP Settings → LinkedIn**

(3) Beide Werte einfügen → **Speichern**

(4) Mit dem **LinkedIn-Login-Button** im Frontend testen

> **Sicherheit:**
>
> - Geben Sie das Client Secret niemals öffentlich preis
> - Verwenden Sie **Regenerate secret**, wenn der Schlüssel kompromittiert wurde

### 5) Scopes

| Scope     | Beschreibung        | Hinweis     |
| --------- | ------------------- | ----------- |
| `openid`  | ID-Token            | **Pflicht** |
| `profile` | Profil (Name, Foto) | **Pflicht** |
| `email`   | E-Mail              | **Pflicht** |

> **Legacy-Scopes (`r_liteprofile`, `r_emailaddress`)**
>
> - **Seit 2024 als veraltet eingestuft**
> - **Für neue Apps nicht mehr verfügbar**

#### 6) Fehlerbehebung

(1) **redirect_uri_mismatch**

→ URIs weichen auch nur minimal voneinander ab → auf **100 % exakte Übereinstimmung** achten

(2) **invalid_client**

→ Falsche ID / falsches Secret oder App inaktiv → Werte prüfen oder neu erzeugen

(3) **email NULL**

→ Benutzer hat abgelehnt oder Scope `email` fehlt → Verwendung im Einwilligungsbildschirm erklären

(4) **insufficient_scope**

→ Angeforderter Scope nicht genehmigt → prüfen, ob OIDC korrekt aktiviert ist

(5) **OIDC nicht aktiviert**

→ **Sign In with LinkedIn using OpenID Connect** fehlt unter „Products“

> **Logs:**
>
> - `/wp-content/seslp-logs/seslp-debug.log`
> - `/wp-content/debug.log`

#### 7) Checkliste

- [ ] App erstellt
- [ ] OIDC aktiviert
- [ ] Redirect URI registriert
- [ ] ID/Secret in SESLP
- [ ] HTTPS-Test

---

> **Hinweis:**
>
> - SESLP unterstützt den **OIDC-Flow** vollständig.
> - Das alte OAuth 2.0 wird **nicht mehr unterstützt**.
> - Verwenden Sie für neue Integrationen immer **OpenID Connect**.

</details>

---

<details>
  <summary><strong>Naver</strong></summary>

> - **Redirect URI:** `https://{domain}/?social_login=naver`
> - **Empfohlene Scopes:** `name`, `email`
> - Naver nutzt **Naver Login (네아로)**, **HTTPS erforderlich**

---

#### 1) App registrieren

(1) **Naver Developer Center**

→ [https://developers.naver.com/apps/](https://developers.naver.com/apps/)

(2) Einloggen

(3) **Anwendung registrieren**

(4) Pflichtfelder:

- Name, API: `Naver Login`
- Web: Service-URL, **Callback URL**

(5) **Registrieren**

> **Hinweis:**
>
> - **HTTPS zwingend erforderlich** → HTTP ist nicht erlaubt
> - **Subdomains müssen jeweils separat registriert werden**

#### 2) Client ID / Secret

(1) Gehen Sie zu **Meine Anwendungen**

(2) Klicken Sie auf die App → **Client ID** und **Client Secret** kopieren

#### 3) WordPress (Plugin) Einstellungen

(1) WP-Admin → **SESLP → Naver**

(2) ID/Secret einfügen

(3) URI exakt prüfen

(4) **Speichern** → Testen

#### 4) Berechtigungen

| Daten                  | Scope   | Hinweis                  |
| ---------------------- | ------- | ------------------------ |
| Name                   | `name`  | Standard                 |
| E-Mail                 | `email` | Standard                 |
| Geschlecht, Geburtstag | Separat | **Prüfung erforderlich** |

> - Benutzer können der Datenweitergabe im Zustimmungsbildschirm **zustimmen oder sie ablehnen**
> - Wenn die E-Mail abgelehnt wird → `email = null` → **ID-basierte Kontoverknüpfung** verwenden
> - Sensible Daten erfordern eine **Naver-App-Prüfung**

#### 5) Fehlerbehebung

(1) **Redirect-URI stimmt nicht überein**

→ Bereits kleinste Abweichungen führen zu Fehlern → auf **100 % exakte Übereinstimmung** achten

(2) **HTTP-Fehler**

→ **HTTPS ist zwingend erforderlich**

(3) **Subdomain-Fehler**

→ Jede Subdomain muss **separat registriert** werden

(4) **email NULL**

→ Benutzer hat abgelehnt oder E-Mail ist privat → **ID-basierte Verknüpfungslogik** vorbereiten

(5) **Prüfung erforderlich**

→ Basis-Login: **keine Prüfung notwendig**  
→ Zusätzliche Daten: **Prüfung erforderlich**

> **Logs:**
>
> - `/wp-content/seslp-logs/seslp-debug.log`
> - `/wp-content/debug.log`

### 6) Checkliste

- [ ] App im Naver Developer Center registriert
- [ ] **Callback-URL** exakt registriert
- [ ] **HTTPS** verwendet
- [ ] Subdomains bei Bedarf separat registriert
- [ ] Client ID/Secret in SESLP gespeichert
- [ ] Zustimmungs-/Ablehnungsverhalten für E-Mail getestet
- [ ] Frontend-Login-Test abgeschlossen

---

> **Hinweis:**
>
> - SESLP unterstützt **Naver Login (네아로)** vollständig.
> - Basis-Login (`name`, `email`) ist **ohne gesonderte Prüfung** verfügbar.

</details>

---

<details>
  <summary><strong>Kakao</strong></summary>

> - **Redirect URI:** `https://{domain}/?social_login=kakao`
> - **Empfohlene Scopes:** `profile_nickname`, `profile_image`, `account_email`
> - `account_email` nur nach **Identitäts- oder Unternehmensverifizierung**
> - **HTTPS erforderlich**, **Client Secret aktivieren**

---

#### 1) Anwendung erstellen

(1) Zu **Kakao Developers** gehen

→ [https://developers.kakao.com/](https://developers.kakao.com/)

(2) Einloggen → **Meine Anwendungen → Neue App hinzufügen**

(3) Eingaben:

- App-Name, Firmenname
- Kategorie
- Betriebsrichtlinie akzeptieren

(4) **Speichern**

#### 2) Kakao Login aktivieren

(1) **Produkteinstellungen > Kakao Login**

(2) **Kakao Login aktivieren** → **EIN**

(3) **Redirect URI registrieren**

- `https://{domain}/?social_login=kakao`
- **Speichern**

(4) Domain muss mit **Plattform-Site-Domain** übereinstimmen

#### 3) Zustimmungs-Elemente (Scopes)

(1) **Zustimmungs-Elemente**

(2) Hinzufügen und konfigurieren:

| Scope              | Beschreibung | Zustimmungstyp   | Hinweis                        |
| ------------------ | ------------ | ---------------- | ------------------------------ |
| `profile_nickname` | Nickname     | Pflicht/Optional | Basis                          |
| `profile_image`    | Profilbild   | Pflicht/Optional | Basis                          |
| `account_email`    | E-Mail       | **Optional**     | **Verifizierung erforderlich** |

(3) **Zweck** klar angeben

(4) **Speichern**

> Sensible Scopes erfordern **Verifizierung**

#### 4) Web-Plattform registrieren

(1) **App-Einstellungen > Plattform**

(2) **Web-Plattform registrieren**

(3) Site-Domain: `https://{domain}`

(4) **Speichern** → Muss mit Redirect URI übereinstimmen

#### 5) Sicherheit – Client Secret generieren & aktivieren

(1) **Produkteinstellungen > Sicherheit**

(2) **Client Secret verwenden** → **EIN**

(3) **Secret generieren** → Wert kopieren

(4) **Aktivierungsstatus** → **Aktiv**

(5) **Speichern**

> **Aktivieren nach Generierung erforderlich**

#### 6) REST API Key holen (Client ID)

(1) **App-Schlüssel**

(2) **REST API Key** kopieren → Als **Client ID** verwenden

#### 7) WordPress-Einstellungen

(1) WP Admin → **SESLP Settings → Kakao**

(2) **Client ID** = REST API Key  
 **Client Secret** = Generiertes Secret

(3) **Speichern**

(4) Mit **Kakao Login Button** testen

#### 8) Fehlerbehebung

(1) **redirect_uri_mismatch** → 100 % Übereinstimmung

(2) **invalid_client** → Secret nicht aktiviert oder Tippfehler

(3) **email leer** → Benutzer abgelehnt oder nicht verifiziert

(4) **Domain-Fehler** → Plattform vs URI

(5) **HTTP verboten** → **Nur HTTPS**

> **Logs:**
>
> - `/wp-content/seslp-logs/seslp-debug.log`
> - `/wp-content/debug.log`

### 9) Checkliste

- [ ] Kakao Login aktiviert
- [ ] Redirect URI registriert
- [ ] Web-Plattform-Domain registriert
- [ ] Zustimmungen konfiguriert
- [ ] Client Secret generiert + aktiviert
- [ ] REST API Key / Secret in SESLP
- [ ] Auf HTTPS getestet

</details>

---

<details>
  <summary><strong>LINE</strong></summary>

> - **Redirect URI:** `https://{domain}/?social_login=line`
> - **Erforderlich:** OpenID Connect aktivieren, **E-Mail-Berechtigung beantragen und genehmigen lassen**
> - **Empfohlene Scopes:** `openid`, `profile`, `email`
> - **HTTPS erforderlich**, **E-Mail erfordert Vorabgenehmigung**

---

#### 1) Provider und Kanal erstellen

(1) **LINE Developers Console** aufrufen

→ [https://developers.line.biz/console/](https://developers.line.biz/console/)

(2) Mit **LINE Business Account** anmelden (persönliches Konto nicht erlaubt)

(3) **Neuen Provider erstellen** → Name eingeben → **Create**

(4) Unter Provider → **Channels** Reiter

(5) **LINE Login Kanal erstellen** wählen

(6) Konfigurieren:

- **Kanaltyp:** `LINE Login`
- **Provider:** Erstellt
- **Region:** Zielland (z. B. `South Korea`, `Japan`)
- **Name / Beschreibung / Icon:** Auf Zustimmungsbildschirm

(7) Bedingungen akzeptieren → **Create**

#### 2) OpenID Connect aktivieren & E-Mail-Berechtigung beantragen

(1) Menü **OpenID Connect**

(2) Bei **Email address permission** auf **Apply** klicken

(3) Antrag ausfüllen:

- **Datenschutz-URL** (öffentlich erreichbar)
- **Screenshot der Datenschutzerklärung**
- Zustimmung → **Submit**

(4) **`email` Scope nur nach Genehmigung aktiv**  
 → Bearbeitung: 1–3 Werktage

#### 3) Callback URL registrieren & Kanal veröffentlichen

(1) Menü **LINE Login**

(2) **Callback URL** eingeben:

→ `https://{domain}/?social_login=line`

(3) **Exakte Übereinstimmung erforderlich**:

- Protokoll: `https://` (**HTTP nicht erlaubt**)
- Domain, Pfad, Query-String **100 % gleich**

(4) **Speichern**

(5) Kanalstatus auf **Published** setzen

- **Development:** Nur Test
- **Published:** Live-Betrieb

#### 4) Channel ID / Secret abrufen

(1) Oben auf der Kanalseite oder **Basic settings**

(2) **Channel ID** → SESLP **Client ID**  
 **Channel Secret** → SESLP **Client Secret**

#### 5) WordPress-Einstellungen

(1) WP Admin → **SESLP Settings → LINE**

(2) **Client ID** ← Channel ID  
 **Client Secret** ← Channel Secret

(3) **Speichern**

(4) Mit **LINE Login Button** im Frontend testen

#### 6) Fehlerbehebung

(1) **redirect_uri_mismatch** → Kleinste Abweichung → Fehler → **100 % gleich**

(2) **invalid_client** → Secret falsch oder **nicht Published**

(3) **email NULL** → **E-Mail-Berechtigung nicht genehmigt** oder abgelehnt

(4) **HTTP verboten** → **Nur HTTPS** (localhost HTTPS OK)

(5) **Development-Modus** → Nur Testkonten können sich anmelden

> **Logs:**
>
> - `/wp-content/seslp-logs/seslp-debug.log`
> - `/wp-content/debug.log`

#### 7) Checkliste

- [ ] Provider + LINE Login Kanal mit Business Account erstellt
- [ ] E-Mail-Berechtigung **beantragt und genehmigt**
- [ ] **Callback URL** exakt registriert
- [ ] **HTTPS**, Status **Published**
- [ ] Channel ID/Secret in SESLP gespeichert
- [ ] Frontend-Anmeldung getestet

> **Hinweis:** SESLP unterstützt
>
> - **LINE Login v2.1 + OpenID Connect** vollständig.
> - **E-Mail-Erfassung erfordert Vorabgenehmigung**.

</details>
