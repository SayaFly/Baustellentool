# Baustellentool

Eine webbasierte Baustellenverwaltungs-App mit Kunden-, Material- und Projektverwaltung sowie dynamischer Netto-/Brutto-Preisberechnung (MwSt optional).

## Features

- **Dashboard** – Kennzahlen: Kunden, Baustellen, offene Zahlungen, Umsatz, Gewinn
- **Kundenverwaltung** – CRUD mit Zahlungsstatus (offen / bezahlt)
- **Baustellenverwaltung** – Projektstatus, Fläche (m²), Kundenzuweisung
- **Materialverwaltung** – Globale Materialliste (ohne feste Preise)
- **Projektmaterialien** – Menge, Einkaufs- und Verkaufspreis pro Baustelle
- **MwSt-Einstellungen** – Satz konfigurierbar, global ein-/ausschaltbar

## Technologie

| Schicht | Technologie |
|---|---|
| Backend | PHP 8.x (kein Framework, PDO) |
| Frontend | HTML + Bootstrap 5 + Bootstrap Icons |
| Datenbank | MariaDB |
| Webserver | IIS (Windows Server) |

## Installation

Hinweis: Offiziell unterstützt wird nur der Betrieb auf Windows Server mit IIS.

📖 **Vollständige Schritt-für-Schritt-Anleitung:**  
→ **[INSTALLATION.md](INSTALLATION.md)**

### Inbetriebnahme (Kurz)

1. IIS mit aktiviertem CGI-Modul installieren
2. PHP 8.x (NTS, x64) installieren und als FastCGI in IIS einbinden
3. MariaDB installieren und `database.sql` importieren
4. Projekt nach `C:\inetpub\wwwroot\baustellentool\` deployen
5. Zugangsdaten in `config/database.php` setzen
6. IIS-Website auf das Projektverzeichnis konfigurieren
7. Anwendung im Browser über `http://localhost/` öffnen

Kurzübersicht:
1. IIS mit CGI-Modul aktivieren
2. PHP (NTS, x64) installieren und als FastCGI in IIS einrichten
3. MariaDB installieren und `database.sql` importieren
4. Dateien nach `C:\inetpub\wwwroot\baustellentool\` kopieren
5. Zugangsdaten in `config/database.php` eintragen
6. IIS-Website auf das Verzeichnis zeigen lassen
