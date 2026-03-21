# Installationsanleitung – Baustellentool auf Windows Server (IIS)

Diese Anleitung beschreibt die vollständige Installation des Baustellentools auf einem **Windows Server** mit **IIS (Internet Information Services)**, **PHP** und **MariaDB**.

> Hinweis: Diese Anwendung und diese Anleitung sind auf den Betrieb mit IIS unter Windows Server ausgelegt.

---

## Inhaltsverzeichnis

1. [Systemvoraussetzungen](#1-systemvoraussetzungen)
2. [IIS installieren und konfigurieren](#2-iis-installieren-und-konfigurieren)
3. [PHP installieren](#3-php-installieren)
4. [PHP in IIS als FastCGI einrichten](#4-php-in-iis-als-fastcgi-einrichten)
5. [MariaDB installieren](#5-mariadb-installieren)
6. [Datenbank anlegen und befüllen](#6-datenbank-anlegen-und-befüllen)
7. [Anwendung deployen](#7-anwendung-deployen)
8. [IIS-Website einrichten](#8-iis-website-einrichten)
9. [Datenbankverbindung konfigurieren](#9-datenbankverbindung-konfigurieren)
10. [Berechtigungen setzen](#10-berechtigungen-setzen)
11. [Abschlusskontrolle](#11-abschlusskontrolle)
12. [Fehlerbehebung](#12-fehlerbehebung)
13. [Sicherheitshinweise für den Produktivbetrieb](#13-sicherheitshinweise-für-den-produktivbetrieb)

---

## 1. Systemvoraussetzungen

| Komponente | Mindestversion | Empfohlen |
|---|---|---|
| Windows Server | 2016 | 2022 |
| IIS | 10 | 10 |
| PHP | 8.1 | 8.3 (NTS, x64) |
| MariaDB | 10.6 | 11.x |
| RAM | 2 GB | 4 GB+ |
| Speicher | 5 GB frei | 20 GB+ |

> **Hinweis:** PHP muss als **Non-Thread-Safe (NTS)** in der **64-Bit (x64)**-Version installiert werden, da IIS FastCGI verwendet.

---

## 2. IIS installieren und konfigurieren

### 2a. IIS-Rolle hinzufügen (Server Manager)

1. **Server Manager** öffnen → **Verwalten** → **Rollen und Features hinzufügen**
2. Installationstyp: **Rollenbasierte oder featurebasierte Installation** → Weiter
3. Server auswählen → Weiter
4. Serverrollen: Haken bei **Webserver (IIS)** setzen → „Features hinzufügen" bestätigen
5. Features: Standard lassen → Weiter
6. Webserver-Rolle (IIS): Weiter
7. Rollendienste – folgende Punkte aktivieren:

   ```
   Webserver
   ├── Allgemeine HTTP-Features
   │   ├── ✅ Standarddokument
   │   ├── ✅ Verzeichnissuche (optional deaktivieren – sicherer)
   │   ├── ✅ HTTP-Fehler
   │   └── ✅ Statischer Inhalt
   ├── Integrität und Diagnose
   │   ├── ✅ HTTP-Protokollierung
   │   └── ✅ Anforderungsüberwachung
   ├── Leistung
   │   └── ✅ Komprimierung statischer Inhalte
   ├── Sicherheit
   │   └── ✅ Anforderungsfilterung
   └── Anwendungsentwicklung
       ├── ✅ CGI          ← WICHTIG für PHP FastCGI!
       └── ✅ ISAPI-Erweiterungen (optional)
   ```

8. **Installieren** klicken und warten bis abgeschlossen.

### 2b. Überprüfen

Öffne einen Browser auf dem Server und rufe `http://localhost` auf.  
Es sollte die IIS-Standardseite erscheinen.

---

## 3. PHP installieren

### 3a. PHP herunterladen

1. Browser öffnen: **https://windows.php.net/download/**
2. Aktuelle stabile Version auswählen (z. B. **PHP 8.3**)
3. Download: **Non Thread Safe (NTS)** → **x64** → `php-8.3.x-nts-Win32-vs16-x64.zip`

### 3b. Visual C++ Redistributable installieren

PHP für Windows benötigt das passende **Visual C++ Redistributable**:

1. Die benötigte Version steht auf der PHP-Downloadseite (z. B. „VS16" = Visual Studio 2019)
2. Herunterladen von: **https://aka.ms/vs/17/release/vc_redist.x64.exe**
3. Installieren und neu starten falls nötig

### 3c. PHP entpacken und einrichten

1. ZIP-Datei entpacken nach: `C:\PHP\`  
   *(Pfad darf keine Leerzeichen enthalten!)*

2. PHP-Konfigurationsdatei kopieren:
   ```
   C:\PHP\php.ini-production  →  kopieren als  →  C:\PHP\php.ini
   ```

3. **`C:\PHP\php.ini`** mit einem Texteditor (z. B. Notepad++) öffnen und folgende Einstellungen anpassen:

   ```ini
   ; Erweiterungsverzeichnis setzen
   extension_dir = "C:\PHP\ext"

   ; Benötigte Erweiterungen aktivieren (Semikolon entfernen):
   extension=pdo_mysql
   extension=mysqli
   extension=mbstring
   extension=openssl
   extension=fileinfo
   extension=intl

   ; Zeitzone setzen
   date.timezone = Europe/Berlin

   ; Fehleranzeige (Produktion: ausschalten)
   display_errors = Off
   log_errors = On
   error_log = "C:\PHP\php_errors.log"

   ; Upload-Limits (bei Bedarf anpassen)
   upload_max_filesize = 20M
   post_max_size = 20M
   max_execution_time = 60
   memory_limit = 256M
   ```

4. **PHP zum System-PATH hinzufügen:**
   - Win + R → `sysdm.cpl` → Erweitert → Umgebungsvariablen
   - Unter „Systemvariablen" die Variable `Path` auswählen → Bearbeiten
   - **Neu** → `C:\PHP` → OK

5. **Überprüfen** (Eingabeaufforderung als Administrator):
   ```cmd
   php -v
   ```
   Ausgabe sollte sein: `PHP 8.3.x (cli) ...`

---

## 4. PHP in IIS als FastCGI einrichten

### 4a. FastCGI-Anwendung registrieren

1. **IIS-Manager** öffnen (Start → `inetmgr`)
2. Auf den Servernamen klicken (oberste Ebene)
3. Doppelklick auf **„Handler-Zuordnungen"**
4. Rechts: **„Modulzuordnung hinzufügen..."**
5. Einstellungen:

   | Feld | Wert |
   |---|---|
   | Anforderungspfad | `*.php` |
   | Modul | `FastCgiModule` |
   | Ausführbare Datei | `C:\PHP\php-cgi.exe` |
   | Name | `PHP_via_FastCGI` |

6. **OK** → Bestätigung „Ja" (FastCGI-Anwendung für diesen ausführbaren Pfad erstellen)

### 4b. FastCGI-Einstellungen optimieren

1. Im IIS-Manager Serverebene → **„FastCGI-Einstellungen"**
2. Auf den Eintrag `C:\PHP\php-cgi.exe` doppelklicken
3. Empfohlene Werte:

   | Einstellung | Wert |
   |---|---|
   | Maximale Instanzen | `10` |
   | Aktivitätstimeout | `70` |
   | Anforderungstimeout | `90` |
   | Umgebungsvariablen → `PHP_FCGI_MAX_REQUESTS` | `10000` |

4. **OK**

### 4c. PHP-Funktion testen

1. Datei erstellen: `C:\inetpub\wwwroot\phpinfo.php`
   ```php
   <?php phpinfo();
   ```
2. Browser: `http://localhost/phpinfo.php`
3. PHP-Infoseite sollte erscheinen ✅
4. **Datei danach sofort löschen** (Sicherheit!)

---

## 5. MariaDB installieren

### 5a. MariaDB herunterladen

1. Browser: **https://mariadb.org/download/**
2. Auswählen: **Windows** → **MSI Package (x86_64)**
3. Aktuelle stabile Version herunterladen (z. B. `mariadb-11.x.x-winx64.msi`)

### 5b. Installation durchführen

1. MSI-Installer starten → **Next**
2. Lizenz akzeptieren → **Next**
3. Installationspfad: Standard `C:\Program Files\MariaDB 11.x\` → **Next**
4. **Root-Passwort setzen** (sicheres Passwort wählen und notieren!):
   - ✅ „Modify password for database user 'root'"
   - Passwort eingeben und bestätigen
   - ✅ „Enable access from remote machines for 'root'" → **DEAKTIVIEREN** (Sicherheit)
5. Standard-Datenbank-Zeichensatz: **UTF-8** ✅
6. Service-Name: `MariaDB` (als Windows-Dienst installieren ✅)
7. **Install** → Warten → **Finish**

### 5c. Überprüfen

```cmd
mysql -u root -p
```
Passwort eingeben → MariaDB-Eingabeaufforderung erscheint:
```
MariaDB [(none)]>
```
Mit `exit` beenden.

---

## 6. Datenbank anlegen und befüllen

### 6a. Datenbank erstellen und SQL-Datei importieren

```cmd
mysql -u root -p < "C:\inetpub\wwwroot\baustellentool\database.sql"
```

> Falls der Pfad Leerzeichen enthält, in Anführungszeichen setzen.

**Alternativ über den MariaDB-Client:**

```sql
mysql -u root -p

-- Im MySQL-Prompt:
SOURCE C:/inetpub/wwwroot/baustellentool/database.sql;
exit
```

### 6b. Datenbank-Benutzer anlegen (empfohlen!)

Für mehr Sicherheit einen eigenen Benutzer statt `root` verwenden:

```sql
mysql -u root -p

CREATE USER 'baustellentool'@'localhost' IDENTIFIED BY 'SICHERES_PASSWORT_HIER';
GRANT ALL PRIVILEGES ON baustellentool.* TO 'baustellentool'@'localhost';
FLUSH PRIVILEGES;
exit
```

> ⚠️ `SICHERES_PASSWORT_HIER` durch ein starkes, einzigartiges Passwort ersetzen!

### 6c. Import überprüfen

```sql
mysql -u baustellentool -p baustellentool

SHOW TABLES;
-- Erwartete Ausgabe:
-- customers
-- materials
-- project_materials
-- projects
-- settings

SELECT * FROM settings;
-- Sollte 1 Zeile zeigen: vat_enabled=1, vat_rate=19.00
exit
```

---

## 7. Anwendung deployen

### 7a. Dateien kopieren

Alle Projektdateien in das Webverzeichnis kopieren:

```
C:\inetpub\wwwroot\baustellentool\
├── actions\
│   ├── customer_actions.php
│   ├── material_actions.php
│   ├── project_actions.php
│   ├── project_material_actions.php
│   └── settings_actions.php
├── config\
│   ├── database.php
│   └── web.config          ← sperrt HTTP-Zugriff auf config/
├── includes\
│   ├── footer.php
│   ├── header.php
│   └── sidebar.php
├── pages\
│   ├── customers.php
│   ├── dashboard.php
│   ├── materials.php
│   ├── project_detail.php
│   ├── projects.php
│   └── settings.php
├── database.sql
├── index.php
└── web.config              ← IIS-Konfiguration
```

**Empfohlene Methode:** Git (falls Git für Windows installiert):
```cmd
cd C:\inetpub\wwwroot
git clone https://github.com/SayaFly/Baustellentool baustellentool
```

Oder manuell per ZIP-Datei entpacken.

---

## 8. IIS-Website einrichten

### 8a. Neue Website erstellen

1. **IIS-Manager** öffnen (`Start → inetmgr`)
2. Linkes Panel: **Sites** → Rechtsklick → **Website hinzufügen...**
3. Einstellungen:

   | Feld | Wert (Beispiel) |
   |---|---|
   | Websitename | `Baustellentool` |
   | Anwendungspool | Neuen Pool erstellen (s. u.) |
   | Physischer Pfad | `C:\inetpub\wwwroot\baustellentool` |
   | Bindung – Typ | `http` |
   | Bindung – IP | `Alle nicht zugewiesen` oder konkrete Server-IP |
   | Bindung – Port | `80` (oder z. B. `8080`) |
   | Hostname | z. B. `baustellentool.intern` (optional) |

4. **OK**

### 8b. Anwendungspool konfigurieren

1. Linkes Panel: **Anwendungspools** → Den neuen Pool auswählen → **Grundeinstellungen...**
2. Einstellungen:

   | Feld | Wert |
   |---|---|
   | .NET CLR-Version | **Kein verwalteter Code** |
   | Verwalteter Pipelinemodus | **Integriert** |

3. **OK**

### 8c. Standarddokument prüfen

1. Die neue Website auswählen → Doppelklick **„Standarddokument"**
2. Sicherstellen, dass `index.php` in der Liste vorhanden ist  
   (falls nicht: rechts **„Hinzufügen..."** → `index.php`)

---

## 9. Datenbankverbindung konfigurieren

Datei öffnen: `C:\inetpub\wwwroot\baustellentool\config\database.php`

```php
<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'baustellentool');
define('DB_USER', 'baustellentool');   // ← anpassen
define('DB_PASS', 'SICHERES_PASSWORT_HIER');  // ← anpassen
define('DB_CHARSET', 'utf8mb4');
```

> **DB_USER** und **DB_PASS** auf die in Schritt 6b angelegten Werte setzen.

---

## 10. Berechtigungen setzen

IIS läuft unter dem Konto **`IIS_IUSRS`** (bzw. dem Identitätskonto des Anwendungspools). Dieses Konto benötigt Lesezugriff auf die Anwendungsdateien.

### 10a. Berechtigungen im Explorer setzen

1. Rechtsklick auf `C:\inetpub\wwwroot\baustellentool` → **Eigenschaften**
2. Reiter **Sicherheit** → **Bearbeiten...**
3. **Hinzufügen...** → `IIS_IUSRS` eingeben → **Namen überprüfen** → **OK**
4. Berechtigungen für `IIS_IUSRS`:
   - ✅ Lesen und Ausführen
   - ✅ Ordnerinhalt auflisten
   - ✅ Lesen
5. **OK** → **OK**

### 10b. Berechtigungen per Kommandozeile (alternativ)

```cmd
icacls "C:\inetpub\wwwroot\baustellentool" /grant "IIS_IUSRS:(OI)(CI)RX" /T
```

> `/T` = rekursiv für alle Unterordner und Dateien

### 10c. Schreibzugriff einschränken

Die Anwendung schreibt selbst **keine** Dateien, daher reicht reiner Lesezugriff (RX).  
Das `config\`-Verzeichnis ist über `config\web.config` zusätzlich vor direktem HTTP-Zugriff geschützt.

---

## 11. Abschlusskontrolle

### Checkliste

- [ ] IIS läuft: `http://localhost` zeigt eine Seite
- [ ] PHP funktioniert: `phpinfo.php`-Test erfolgreich (danach löschen!)
- [ ] MariaDB läuft: `services.msc` → Dienst `MariaDB` = Wird ausgeführt
- [ ] Datenbank angelegt: `SHOW TABLES` zeigt 5 Tabellen
- [ ] `config/database.php` enthält korrekte Zugangsdaten
- [ ] Berechtigungen gesetzt: `IIS_IUSRS` hat Lesezugriff
- [ ] Website im IIS-Manager erstellt und gestartet

### Abschlusstest

Browser öffnen und aufrufen:

```
http://localhost/
   oder
http://SERVER-IP/
   oder (wenn Hostname konfiguriert)
http://baustellentool.intern/
```

**Erwartetes Ergebnis:** Das Dashboard der Baustellenverwaltung wird angezeigt, mit Kunden- und Baustellen-Statistiken aus den Beispieldaten.

---

## 12. Fehlerbehebung

### ❌ „HTTP Error 500.0 – Internal Server Error"

**Ursache:** PHP-Fehler oder falsche FastCGI-Konfiguration.

**Lösung:**
1. PHP-Fehlerlog prüfen: `C:\PHP\php_errors.log`
2. IIS-Log prüfen: `C:\inetpub\logs\LogFiles\W3SVC1\`
3. In `C:\PHP\php.ini` temporär setzen: `display_errors = On`  
   → Danach **unbedingt wieder auf `Off` setzen!**

---

### ❌ „HTTP Error 403.14 – Directory Listing Denied"

**Ursache:** IIS findet `index.php` nicht als Standarddokument.

**Lösung:**
1. IIS-Manager → Website → **Standarddokument**
2. `index.php` hinzufügen (falls nicht vorhanden)
3. Website neu starten

---

### ❌ „Datenbankverbindung fehlgeschlagen"

**Ursache:** Falsche Zugangsdaten oder MariaDB-Dienst nicht gestartet.

**Lösung:**
1. Dienst prüfen: `services.msc` → `MariaDB` → Status = „Wird ausgeführt"
2. Zugangsdaten in `config/database.php` überprüfen
3. Verbindung testen:
   ```cmd
   mysql -u baustellentool -p baustellentool
   ```

---

### ❌ „HTTP Error 404.3 – Not Found" (PHP-Dateien werden nicht ausgeführt)

**Ursache:** CGI-Modul nicht installiert oder Handler-Zuordnung fehlt.

**Lösung:**
1. IIS-Manager → Serverebene → **Handler-Zuordnungen** → `PHP_via_FastCGI` vorhanden?
2. Falls nicht: Schritt 4 dieser Anleitung wiederholen
3. CGI-Feature nachinstallieren:
   ```
   Server Manager → Rollen und Features → Webserver (IIS)
   → Anwendungsentwicklung → CGI ✅
   ```

---

### ❌ PHP-Seite wird als Download angeboten (nicht ausgeführt)

**Ursache:** Handler-Zuordnung wurde nicht auf Serverebene, sondern nur auf Site-Ebene gesetzt.

**Lösung:**  
Schritt 4a auf **Serverebene** (nicht Site-Ebene) im IIS-Manager wiederholen.

---

### ❌ „Access denied" / Seite lädt nicht (Berechtigungsfehler)

**Ursache:** `IIS_IUSRS` hat keinen Lesezugriff.

**Lösung:**
```cmd
icacls "C:\inetpub\wwwroot\baustellentool" /grant "IIS_IUSRS:(OI)(CI)RX" /T
```

---

## 13. Sicherheitshinweise für den Produktivbetrieb

> ⚠️ Die folgenden Punkte sind **Pflicht**, wenn die Anwendung im Netzwerk oder Internet erreichbar ist.

1. **Datenbank-Root-Passwort** setzen (falls nicht bei Installation gesetzt):
   ```sql
   ALTER USER 'root'@'localhost' IDENTIFIED BY 'STARKES_PASSWORT';
   ```

2. **Separaten DB-Benutzer** verwenden (kein `root` in `database.php`) → siehe Schritt 6b

3. **`display_errors = Off`** in `php.ini` (Fehlerdetails nicht an Browser senden)

4. **`phpinfo.php`** sofort nach dem Test löschen

5. **HTTPS einrichten:** TLS-Zertifikat in IIS konfigurieren (Let's Encrypt via [win-acme](https://www.win-acme.com/) oder eigenes Zertifikat)

6. **Windows Firewall:** Nur Port 80/443 von außen freigeben; MariaDB-Port 3306 **nicht** öffentlich freigeben

7. **Regelmäßige Backups** der Datenbank:
   ```cmd
   mysqldump -u baustellentool -p baustellentool > C:\Backups\baustellentool_%DATE%.sql
   ```

8. **Windows Update** und **PHP-Updates** regelmäßig einspielen

---

## Schnellreferenz: Wichtige Pfade und Befehle

| Was | Pfad / Befehl |
|---|---|
| Anwendung | `C:\inetpub\wwwroot\baustellentool\` |
| PHP-Installation | `C:\PHP\` |
| PHP-Konfiguration | `C:\PHP\php.ini` |
| PHP-Fehlerlog | `C:\PHP\php_errors.log` |
| IIS-Logs | `C:\inetpub\logs\LogFiles\` |
| MariaDB-Daten | `C:\Program Files\MariaDB 11.x\data\` |
| IIS neustarten | `iisreset` (als Administrator) |
| MariaDB-Dienst | `net start MariaDB` / `net stop MariaDB` |
| DB-Import | `mysql -u root -p < database.sql` |
| DB-Backup | `mysqldump -u root -p baustellentool > backup.sql` |

---

*Letzte Aktualisierung: März 2026 · Getestet mit Windows Server 2022, IIS 10, PHP 8.3 NTS x64, MariaDB 11.x*
