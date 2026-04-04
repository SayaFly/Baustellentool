<?php
require_once __DIR__ . '/../config/database.php';

$pdo    = getDB();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        $name          = trim($_POST['name'] ?? '');
        $unit          = $_POST['unit'] ?? 'Stück';
        $purchasePrice = (($_POST['purchase_price'] ?? '') !== '') ? (float)$_POST['purchase_price'] : null;
        $sellingPrice  = (($_POST['selling_price']  ?? '') !== '') ? (float)$_POST['selling_price']  : null;

        if ($name === '') {
            redirect('materials', 'Name ist erforderlich.', 'error');
        }
        if (!in_array($unit, ['m²', 'm³', 'Stück'], true)) {
            $unit = 'Stück';
        }

        $stmt = $pdo->prepare('INSERT INTO materials (name, unit, purchase_price, selling_price) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $unit, $purchasePrice, $sellingPrice]);
        redirect('materials', 'Material erfolgreich hinzugefügt.');
        break;

    case 'edit':
        $id            = (int)($_POST['id'] ?? 0);
        $name          = trim($_POST['name'] ?? '');
        $unit          = $_POST['unit'] ?? 'Stück';
        $purchasePrice = (($_POST['purchase_price'] ?? '') !== '') ? (float)$_POST['purchase_price'] : null;
        $sellingPrice  = (($_POST['selling_price']  ?? '') !== '') ? (float)$_POST['selling_price']  : null;

        if (!$id || $name === '') {
            redirect('materials', 'Ungültige Eingaben.', 'error');
        }
        if (!in_array($unit, ['m²', 'm³', 'Stück'], true)) {
            $unit = 'Stück';
        }

        $stmt = $pdo->prepare('UPDATE materials SET name=?, unit=?, purchase_price=?, selling_price=? WHERE id=?');
        $stmt->execute([$name, $unit, $purchasePrice, $sellingPrice, $id]);
        redirect('materials', 'Material erfolgreich aktualisiert.');
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            redirect('materials', 'Ungültige ID.', 'error');
        }
        try {
            $stmt = $pdo->prepare('DELETE FROM materials WHERE id = ?');
            $stmt->execute([$id]);
            redirect('materials', 'Material erfolgreich gelöscht.');
        } catch (PDOException $e) {
            redirect('materials', 'Material kann nicht gelöscht werden – es wird in Baustellen verwendet.', 'error');
        }
        break;

    default:
        redirect('materials', 'Ungültige Aktion.', 'error');
}

function redirect(string $page, string $msg, string $type = 'success'): void {
    header('Location: index.php?page=' . $page . '&msg=' . urlencode($msg) . '&type=' . $type);
    exit;
}
