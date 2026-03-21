<?php
require_once __DIR__ . '/../config/database.php';

$pdo      = getDB();
$action   = $_POST['action'] ?? '';

$allowedRedirects = [
    'index.php?page=settings',
    'index.php?page=dashboard',
];
$rawRedirect = $_POST['redirect'] ?? 'index.php?page=settings';
$redirect    = in_array($rawRedirect, $allowedRedirects, true) ? $rawRedirect : 'index.php?page=settings';

switch ($action) {
    case 'update':
    case 'toggle_vat':
        $vatEnabled = isset($_POST['vat_enabled']) ? (int)$_POST['vat_enabled'] : 0;
        $vatRate    = (float)($_POST['vat_rate'] ?? 19.00);

        if ($vatEnabled !== 0 && $vatEnabled !== 1) {
            $vatEnabled = 0;
        }
        if ($vatRate < 0 || $vatRate > 100) {
            $vatRate = 19.00;
        }

        $count = (int)$pdo->query('SELECT COUNT(*) FROM settings')->fetchColumn();
        if ($count === 0) {
            $stmt = $pdo->prepare('INSERT INTO settings (vat_enabled, vat_rate) VALUES (?, ?)');
            $stmt->execute([$vatEnabled, $vatRate]);
        } else {
            $stmt = $pdo->prepare('UPDATE settings SET vat_enabled=?, vat_rate=? LIMIT 1');
            $stmt->execute([$vatEnabled, $vatRate]);
        }

        $msg = $action === 'toggle_vat'
            ? 'MwSt wurde ' . ($vatEnabled ? 'aktiviert' : 'deaktiviert') . '.'
            : 'Einstellungen gespeichert.';

        header('Location: ' . $redirect . (str_contains($redirect, '?') ? '&' : '?') . 'msg=' . urlencode($msg));
        exit;

    default:
        header('Location: ' . $redirect);
        exit;
}
