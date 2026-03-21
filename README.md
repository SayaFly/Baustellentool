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

📖 **Vollständige Schritt-für-Schritt-Anleitung:**  
→ **[INSTALLATION.md](INSTALLATION.md)**

Kurzübersicht:
1. IIS mit CGI-Modul aktivieren
2. PHP (NTS, x64) installieren und als FastCGI in IIS einrichten
3. MariaDB installieren und `database.sql` importieren
4. Dateien nach `C:\inetpub\wwwroot\baustellentool\` kopieren
5. Zugangsdaten in `config/database.php` eintragen
6. IIS-Website auf das Verzeichnis zeigen lassen
