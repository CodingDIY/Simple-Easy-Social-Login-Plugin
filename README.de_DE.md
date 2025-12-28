# Simple Easy Social Login

Simple Easy Social Login ist ein leichtgewichtiges und benutzerfreundliches WordPress-Plugin, mit dem Sie Ihrer Website eine schnelle und nahtlose Social-Login-Funktion hinzufügen können.

Es unterstützt **Google, Facebook und LinkedIn (kostenlos)** sowie **Naver, Kakao und Line (Premium)**  
und wurde speziell für Websites entwickelt, die sich an Nutzer in Asien (Korea, Japan, China) richten, eignet sich jedoch ebenso gut für Europa und Südamerika.

Das Plugin integriert sich nahtlos in die standardmäßigen WordPress-Anmelde- und Registrierungsseiten  
und unterstützt zudem die Anmelde- und Registrierungsformulare von WooCommerce.  
Profilbilder aus sozialen Netzwerken können automatisch mit den WordPress-Benutzerprofilen synchronisiert werden.

Das Plugin basiert auf einer **erweiterbaren Provider-Architektur**,  
die es ermöglicht, bei Bedarf neue OAuth-Provider als separate Add-on-Plugins hinzuzufügen.

---

## ✨ Funktionen

- Google Login (kostenlos)
- Facebook Login (kostenlos)
- LinkedIn Login (kostenlos)
- Naver Login (Premium)
- Kakao Login (Premium)
- Line Login (Premium)
- Automatische Synchronisierung von Benutzer-Avataren
- Automatische Verknüpfung bestehender WordPress-Benutzer anhand der E-Mail-Adresse
- Benutzerdefinierte Weiterleitungs-URLs nach Login, Logout und Registrierung
- Einfache und übersichtliche Admin-Oberfläche zur Konfiguration der Provider
- Unterstützung für Shortcode: [se_social_login]
- Automatische Anzeige auf den WordPress-Anmelde- und Registrierungsformularen
- Unterstützung für WooCommerce-Anmelde- und Registrierungsformulare (optional)
- Leichtgewichtige Struktur gemäß den WordPress-Coding-Standards
- Keine Erstellung unnötiger Datenbanktabellen
- Erweiterbares Provider-System zur Unterstützung neuer OAuth-Provider über Add-on-Plugins

---

## 🐞 Debug-Logging

SESLP verfügt über ein integriertes Debug-Logging-System zur Diagnose von OAuth- und Social-Login-Problemen.

Detaillierte Erläuterungen zu den Logs finden Sie direkt im WordPress-Adminbereich:
**SESLP → Guides → Debug Log & Troubleshooting**

Logdateien werden hier erstellt:

- `/wp-content/SESLP-debug.log`
- `/wp-content/debug.log` (wenn `WP_DEBUG_LOG` aktiviert ist)

---

## 🚀 Installation

1. Laden Sie das Plugin in das Verzeichnis `/wp-content/plugins/simple-easy-social-login/` hoch.
2. Aktivieren Sie das Plugin im WordPress-Adminbereich unter **Plugins → Installierte Plugins**.
3. Navigieren Sie zu **Einstellungen → Simple Easy Social Login**.
4. Geben Sie für jeden Social-Login-Provider die Client ID und das Client Secret ein.
5. Speichern Sie die Einstellungen.
6. Überprüfen Sie im Frontend, ob die Social-Login-Buttons korrekt angezeigt werden.

---

## ❓ Häufig gestellte Fragen

### Funktioniert dieses Plugin mit WooCommerce?

Ja. Es integriert sich in die Anmelde- und Registrierungsformulare von WooCommerce.

### WooCommerce-Anmeldung funktioniert, aber das Weiterleitungsverhalten ist anders. Ist das normal?

Ja. Wenn WooCommerce aktiv ist, werden Benutzer nach der Anmeldung in der Regel auf die Seite **Mein Konto** weitergeleitet.  
Die Weiterleitungs-URL kann in den Plugin-Einstellungen oder über verfügbare Filter angepasst werden.

### Was sollte ich überprüfen, wenn der Social Login auf einer WooCommerce-Seite nicht funktioniert?

Bitte überprüfen Sie Folgendes:

- WooCommerce ist auf eine aktuelle stabile Version aktualisiert
- Der Social-Login-Anbieter ist in den Plugin-Einstellungen aktiviert
- Client-ID und Client-Secret sind korrekt eingegeben
- Redirect- / Callback-URLs sind korrekt in der Entwicklerkonsole des Anbieters registriert
- Benutzerdefinierte Login- oder Checkout-Templates entfernen keine standardmäßigen WooCommerce-Hooks
- Die Debug-Protokollierung ist aktiviert und `/wp-content/SESLP-debug.log` wurde überprüft

### Kann ich nur den Google Login verwenden?

Ja. Jeder Provider kann einzeln aktiviert oder deaktiviert werden.

### Wann benötige ich eine Premium-Lizenz?

Für die Nutzung von **Naver, Kakao und Line** ist eine Premium-Lizenz erforderlich.  
Google, Facebook und LinkedIn stehen kostenlos zur Verfügung.

### Gibt es einen Shortcode?

Ja. Sie können die Social-Login-Buttons an beliebiger Stelle mit folgendem Shortcode einfügen:
[se_social_login]

### Werden Benutzer-Avatare automatisch importiert?

Ja. Bei unterstützten Providern wie Google und Facebook können Profilbilder automatisch importiert und als WordPress-Avatare synchronisiert werden.

---

## 🖼 Screenshots

1. Social-Login-Schaltflächen auf der WordPress-Anmeldeseite (Listenlayout).
2. Nur-Icon-Layout der Social-Login-Schaltflächen auf dem Anmeldebildschirm.
3. Weiterleitungsoptionen nach dem Login (Dashboard, Profil, Startseite oder benutzerdefinierte URL).
4. Debug-Protokollierung, UI-Layout-Optionen, Shortcode- und Deinstallations-Einstellungen.
5. Integrierte Einrichtungsanleitung mit Erklärung der OAuth-Redirect-Regeln und grundlegender Anforderungen.
6. Schritt-für-Schritt-Anleitung zur Einrichtung des Google OAuth-Zustimmungsbildschirms und Clients.
7. Admin-Einstellungen für Google-, Facebook- und LinkedIn-Login-Zugangsdaten.
8. Admin-Einstellungen für Naver-, Kakao- und LINE-Login-Anbieter.
9. Einheitliche Redirect-URI-Regeln für alle unterstützten Anbieter.
10. Speicherort der Debug-Logs und Übersicht zur Fehlerbehebung.
11. Häufige OAuth-Fehler, empfohlene Lösungen und Speicherorte der Debug-Logs.

---

## 📝 Änderungsprotokoll (Changelog)

### 1.9.9

- Screenshots und Dokumentation für die öffentliche Veröffentlichung finalisiert
- Umfassende Screenshot-Beschreibungen für Login-Ablauf, Einstellungen, Anleitungen und Fehlerbehebung hinzugefügt
- Kleinere Bereinigungen und Konsistenzverbesserungen in der Dokumentation

### 1.9.8

- Behebung eines fatalen TypeErrors in `SESLP_Avatar::resolve_user()` durch Sicherstellung eines Rückgabewerts vom Typ `WP_User|null`
- Verbesserte Avatar-Fallback-Behandlung:
  - Sicheres Zurückfallen auf den WordPress-Standard-Avatar, wenn ein Social-Profilbild fehlt oder ungültig ist
  - Vermeidung defekter Avatar-Bilder (z. B. bei LinkedIn-Profilbildern)
- Kleinere Stabilitätsverbesserungen im Zusammenhang mit der Avatar-Darstellung

### 1.9.7

- Debug-Logging-Abschnitt zur README hinzugefügt
- Detaillierte Debug-Log-Anleitung in die Admin-Guides integriert (mehrsprachig)
- Dokumentation des Log-Dateipfads vereinheitlicht (`/wp-content/SESLP-debug.log`)
- Überarbeitung und Vereinheitlichung der Dokumentation

### 1.9.6

- Verbesserung der Benutzerfreundlichkeit der Einstellungsseite
- Hinzufügen eines Schalters zum Anzeigen/Ausblenden der Secret Keys
- Behebung von Konflikten mit WordPress-Core-Styles
- Verbesserung der Erkennung von Pro-/Max-Plänen

### 1.9.5

- Umfassendes Refactoring
- Vereinheitlichung der Helper und Verbesserung der Provider-Architektur
- Bereinigung der Einstellungs-UI
- Verbesserung der Stabilität und Wartbarkeit

### 1.9.3

- Aktualisierung der Übersetzungen für die Guides
- Anzeige des Shortcodes auf der Einstellungsseite hinzugefügt

### 1.9.2

- Bereinigung der internen Struktur
- Hinzufügen einer Guides-Loader-Klasse
- Umstrukturierung der Templates
- Verbesserung der Stabilität des Einstellungs- und CSS-Ladeprozesses

### 1.9.1

- Hinzufügen der Admin-Guide-Seite
- Markdown-basierte mehrsprachige Dokumentendarstellung (Parsedown)
- Verbesserung des UI-Stylings

### 1.9.0

- Vorbereitungsphase für umfangreiches Refactoring
- Erweiterung der i18n-Helper
- Verbesserungen bei sicherer Formatierung und Logging

### 1.7.23

- Übersetzungs-Updates

### 1.7.22

- Verbesserung der Debug-Meldungen zur Anzeige des zuvor verwendeten Providers

### 1.7.21

- Anzeige des Provider-Namens in Fehlermeldungen bei doppelten E-Mail-Adressen
- Automatisches Ausblenden der Fehlermeldungen nach 10 Sekunden per JavaScript

### 1.7.19

- Verhinderung der Erstellung doppelter Konten mit derselben E-Mail-Adresse
- Verbesserung des OAuth-Flows:
  - `get_access_token()`
  - `get_user_info()`
  - `create_or_link_user()`

### 1.7.18

- Entfernung der Tooltips aus den Feldern für Google Client ID / Secret
- Bereinigung der Codestruktur
- Entfernung des Textes „(Email required)“ vom Line-Login-Button

### 1.7.17

- Behebung von Problemen beim Line Login:
  - Vermeidung doppelter Benutzer bei erneuter Anmeldung
  - Behebung des erneuten Erscheinens der Seite `/complete-profile`
  - Zulassen von E-Mail-Updates zur Behebung des Fehlers „Invalid request“
- Vereinheitlichung der Debug-Logs mit `SESLP_Logger`

### 1.7.16

- Maskierung von Lizenzschlüsseln in Debug-Logs (z. B. abc\*\*\*\*123)
- Hinzufügen einer Anleitung zur Überprüfung von `wp_options` für Debugging-Zwecke
- Hinzufügen einer Admin-Benachrichtigung bei fehlgeschriebenen Logs

### 1.7.15

- Behebung von Fehlern beim Schreiben der Debug-Logs
- Anwendung der lokalen WordPress-Zeitzone auf Zeitstempel
- Hinzufügen von Debug-Logs beim Speichern der Einstellungen

### 1.7.5

- Anwendung der neuesten Sicherheits-Patches
- Performance-Optimierungen und Verbesserungen der Benutzererfahrung

### 1.7.0

- Verbesserung der Synchronisierung der Social-Login-Buttons
- Sicherheitsverbesserungen und Fehlerbehebungen

### 1.7.3

- Verbesserung des Debugging-Systems
- Hinzufügen eines dedizierten Debug-Verzeichnisses

### 1.6.0

- Wiederherstellung der Anzeige des Lizenzschlüssel-Bereichs bei Auswahl von Plus / Premium

### 1.5.0

- Registrierung der Option `seslp_license_type`
- Behebung des Problems, bei dem der Lizenztyp beim Speichern auf Free zurückgesetzt wurde

### 1.4.0

- Behebung des Ladeproblems von `style.css` im Adminbereich mittels `admin_enqueue_scripts`

### 1.3.0

- Verbesserung der Radio-Button-UI
- Verschieben von Inline-CSS nach `style.css`

### 1.2.0

- Hinzufügen der Auswahl des Lizenztyps (Free / Plus / Premium)
- Verbesserung der Ausrichtung der Einstellungs-UI

### 1.1.0

- Hinzufügen von Mehrsprachigkeit und Laden von Übersetzungsdateien
- Verbesserung der Authentifizierungslogik

### 1.0.0

- Erste Veröffentlichung
- Hinzufügen von Google-, Facebook-, Naver-, Kakao-, Line- und Weibo-Social-Logins

---

## 📄 Lizenz

GPLv2 or later  
https://www.gnu.org/licenses/gpl-2.0.html
