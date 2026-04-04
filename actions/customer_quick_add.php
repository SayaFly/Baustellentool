<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$pdo  = getDB();
$name = trim($_POST['name'] ?? '');

if ($name === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Name ist erforderlich.']);
    exit;
}

$address = trim($_POST['address'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$email   = trim($_POST['email'] ?? '');

$stmt = $pdo->prepare('INSERT INTO customers (name, address, phone, email) VALUES (?, ?, ?, ?)');
$stmt->execute([$name, $address ?: null, $phone ?: null, $email ?: null]);

$id = (int)$pdo->lastInsertId();

echo json_encode(['id' => $id, 'name' => $name]);
