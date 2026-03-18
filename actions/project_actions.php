<?php
require_once __DIR__ . '/../config/database.php';

$pdo    = getDB();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        $name        = trim($_POST['name'] ?? '');
        $customerId  = (int)($_POST['customer_id'] ?? 0);
        $status      = $_POST['status'] ?? 'geplant';
        $areaSize    = $_POST['area_size'] !== '' ? (float)$_POST['area_size'] : null;
        $createdAt   = trim($_POST['created_at'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($name === '' || !$customerId || $createdAt === '') {
            redirect('projects', 'Pflichtfelder fehlen.', 'error');
        }
        if (!in_array($status, ['geplant', 'in Arbeit', 'abgeschlossen'], true)) {
            $status = 'geplant';
        }
        if (!validateDate($createdAt)) {
            redirect('projects', 'Ungültiges Datum.', 'error');
        }

        $stmt = $pdo->prepare('INSERT INTO projects (customer_id, name, description, status, area_size, created_at) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$customerId, $name, $description ?: null, $status, $areaSize, $createdAt]);
        redirect('projects', 'Baustelle erfolgreich hinzugefügt.');
        break;

    case 'edit':
        $id          = (int)($_POST['id'] ?? 0);
        $name        = trim($_POST['name'] ?? '');
        $customerId  = (int)($_POST['customer_id'] ?? 0);
        $status      = $_POST['status'] ?? 'geplant';
        $areaSize    = $_POST['area_size'] !== '' ? (float)$_POST['area_size'] : null;
        $createdAt   = trim($_POST['created_at'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (!$id || $name === '' || !$customerId || $createdAt === '') {
            redirect('projects', 'Ungültige Eingaben.', 'error');
        }
        if (!in_array($status, ['geplant', 'in Arbeit', 'abgeschlossen'], true)) {
            $status = 'geplant';
        }
        if (!validateDate($createdAt)) {
            redirect('projects', 'Ungültiges Datum.', 'error');
        }

        $stmt = $pdo->prepare('UPDATE projects SET customer_id=?, name=?, description=?, status=?, area_size=?, created_at=? WHERE id=?');
        $stmt->execute([$customerId, $name, $description ?: null, $status, $areaSize, $createdAt, $id]);
        redirect('projects', 'Baustelle erfolgreich aktualisiert.');
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            redirect('projects', 'Ungültige ID.', 'error');
        }
        $stmt = $pdo->prepare('DELETE FROM projects WHERE id = ?');
        $stmt->execute([$id]);
        redirect('projects', 'Baustelle erfolgreich gelöscht.');
        break;

    default:
        redirect('projects', 'Ungültige Aktion.', 'error');
}

function validateDate(string $date): bool {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

function redirect(string $page, string $msg, string $type = 'success'): void {
    header('Location: index.php?page=' . $page . '&msg=' . urlencode($msg) . '&type=' . $type);
    exit;
}
