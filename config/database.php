<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'baustellentool');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('DB connection failed: ' . $e->getMessage());
            die('<div class="alert alert-danger m-4">Datenbankverbindung fehlgeschlagen. Bitte kontaktieren Sie den Administrator.</div>');
        }
        initSettings($pdo);
    }
    return $pdo;
}

function initSettings(PDO $pdo): void {
    $stmt = $pdo->query('SELECT COUNT(*) FROM settings');
    if ($stmt && (int)$stmt->fetchColumn() === 0) {
        $pdo->exec('INSERT INTO settings (vat_enabled, vat_rate) VALUES (1, 19.00)');
    }
}

function getSettings(): array {
    $pdo = getDB();
    $stmt = $pdo->query('SELECT * FROM settings LIMIT 1');
    return $stmt->fetch() ?: ['vat_enabled' => 1, 'vat_rate' => 19.00];
}
