-- ============================================================
-- Baustellenverwaltung - Datenbankschema
-- MariaDB / MySQL
-- ============================================================

CREATE DATABASE IF NOT EXISTS baustellentool
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE baustellentool;

-- -------------------------
-- Einstellungen
-- -------------------------
CREATE TABLE IF NOT EXISTS settings (
    id          INT          PRIMARY KEY AUTO_INCREMENT,
    vat_enabled TINYINT(1)   NOT NULL DEFAULT 1,
    vat_rate    DECIMAL(5,2) NOT NULL DEFAULT 19.00
);

-- -------------------------
-- Kunden
-- -------------------------
CREATE TABLE IF NOT EXISTS customers (
    id             INT          PRIMARY KEY AUTO_INCREMENT,
    name           VARCHAR(255) NOT NULL,
    address        TEXT,
    phone          VARCHAR(50),
    email          VARCHAR(255),
    payment_status ENUM('offen','bezahlt') NOT NULL DEFAULT 'offen',
    created_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------
-- Baustellen
-- -------------------------
CREATE TABLE IF NOT EXISTS projects (
    id          INT           PRIMARY KEY AUTO_INCREMENT,
    customer_id INT           NOT NULL,
    name        VARCHAR(255)  NOT NULL,
    description TEXT,
    status      ENUM('geplant','in Arbeit','abgeschlossen') NOT NULL DEFAULT 'geplant',
    area_size   DECIMAL(10,2),
    created_at  DATE          NOT NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- -------------------------
-- Materialien (global, ohne Preise)
-- -------------------------
CREATE TABLE IF NOT EXISTS materials (
    id   INT          PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    unit ENUM('m²','m³','Stück') NOT NULL DEFAULT 'Stück'
);

-- -------------------------
-- Baustellen-Materialien
-- -------------------------
CREATE TABLE IF NOT EXISTS project_materials (
    id             INT           PRIMARY KEY AUTO_INCREMENT,
    project_id     INT           NOT NULL,
    material_id    INT           NOT NULL,
    quantity       DECIMAL(10,2) NOT NULL,
    purchase_price DECIMAL(10,2) NOT NULL,
    selling_price  DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (project_id)  REFERENCES projects(id)  ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE RESTRICT
);

-- ============================================================
-- Beispieldaten
-- ============================================================

INSERT INTO settings (vat_enabled, vat_rate) VALUES (1, 19.00);

-- Kunden
INSERT INTO customers (name, address, phone, email, payment_status) VALUES
('Max Mustermann',  'Musterstraße 1, 12345 Musterstadt', '0151-11111111', 'max@example.com',   'bezahlt'),
('Erika Musterfrau','Beispielweg 42, 54321 Beispielstadt','0152-22222222', 'erika@example.com', 'offen');

-- Baustellen
INSERT INTO projects (customer_id, name, description, status, area_size, created_at) VALUES
(1, 'Einfahrt Pflasterung',    'Einfahrt mit Granitpflaster neu belegt.',        'abgeschlossen', 45.00, '2024-03-15'),
(2, 'Parkplatz Erweiterung',   'Erweiterung des Firmenparkplatzes um 12 Plätze.', 'in Arbeit',    240.00, '2024-05-01');

-- Materialien
INSERT INTO materials (name, unit) VALUES
('Beton C25/30',     'm³'),
('Granitpflaster',   'm²'),
('Bordstein grau',   'Stück'),
('Quarzsand',        'm³'),
('Schotter 0/32',    'm³');

-- Baustellen-Materialien (Projekt 1: Einfahrt Pflasterung)
INSERT INTO project_materials (project_id, material_id, quantity, purchase_price, selling_price) VALUES
(1, 2, 45.00,  28.00,  55.00),   -- Granitpflaster 45 m²
(1, 3, 22.00,   8.50,  18.00),   -- Bordstein 22 Stück
(1, 4,  3.50,  18.00,  35.00);   -- Quarzsand 3.5 m³

-- Baustellen-Materialien (Projekt 2: Parkplatz)
INSERT INTO project_materials (project_id, material_id, quantity, purchase_price, selling_price) VALUES
(2, 1,  18.00, 95.00, 165.00),   -- Beton 18 m³
(2, 5,  35.00, 22.00,  42.00);   -- Schotter 35 m³
