<?php
require_once __DIR__ . '/../config/database.php';

$pdo    = getDB();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        $name          = trim($_POST['name'] ?? '');
        $address       = trim($_POST['address'] ?? '');
        $phone         = trim($_POST['phone'] ?? '');
        $email         = trim($_POST['email'] ?? '');
        $paymentStatus = $_POST['payment_status'] ?? 'offen';

        if ($name === '') {
            redirect('customers', 'Name ist erforderlich.', 'error');
        }
        if (!in_array($paymentStatus, ['offen', 'bezahlt'], true)) {
            $paymentStatus = 'offen';
        }

        $stmt = $pdo->prepare('INSERT INTO customers (name, address, phone, email, payment_status) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$name, $address ?: null, $phone ?: null, $email ?: null, $paymentStatus]);
        redirect('customers', 'Kunde erfolgreich hinzugefügt.');
        break;

    case 'edit':
        $id            = (int)($_POST['id'] ?? 0);
        $name          = trim($_POST['name'] ?? '');
        $address       = trim($_POST['address'] ?? '');
        $phone         = trim($_POST['phone'] ?? '');
        $email         = trim($_POST['email'] ?? '');
        $paymentStatus = $_POST['payment_status'] ?? 'offen';

        if (!$id || $name === '') {
            redirect('customers', 'Ungültige Eingaben.', 'error');
        }
        if (!in_array($paymentStatus, ['offen', 'bezahlt'], true)) {
            $paymentStatus = 'offen';
        }

        $stmt = $pdo->prepare('UPDATE customers SET name=?, address=?, phone=?, email=?, payment_status=? WHERE id=?');
        $stmt->execute([$name, $address ?: null, $phone ?: null, $email ?: null, $paymentStatus, $id]);
        redirect('customers', 'Kunde erfolgreich aktualisiert.');
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            redirect('customers', 'Ungültige ID.', 'error');
        }
        $stmt = $pdo->prepare('DELETE FROM customers WHERE id = ?');
        $stmt->execute([$id]);
        redirect('customers', 'Kunde erfolgreich gelöscht.');
        break;

    default:
        redirect('customers', 'Ungültige Aktion.', 'error');
}

function redirect(string $page, string $msg, string $type = 'success'): void {
    header('Location: index.php?page=' . $page . '&msg=' . urlencode($msg) . '&type=' . $type);
    exit;
}
