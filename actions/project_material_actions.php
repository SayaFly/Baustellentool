<?php
require_once __DIR__ . '/../config/database.php';

$pdo    = getDB();
$action = $_POST['action'] ?? '';

function redirectToProject(int $projectId, string $msg, string $type = 'success'): void {
    header('Location: index.php?page=project_detail&id=' . $projectId . '&msg=' . urlencode($msg) . '&type=' . $type);
    exit;
}

switch ($action) {
    case 'add':
        $projectId    = (int)($_POST['project_id']    ?? 0);
        $materialId   = (int)($_POST['material_id']   ?? 0);
        $quantity     = (float)($_POST['quantity']     ?? 0);
        $purchasePrice = (float)($_POST['purchase_price'] ?? 0);
        $sellingPrice  = (float)($_POST['selling_price']  ?? 0);

        if (!$projectId || !$materialId || $quantity <= 0) {
            redirectToProject($projectId, 'Ungültige Eingaben.', 'error');
        }
        if ($purchasePrice < 0 || $sellingPrice < 0) {
            redirectToProject($projectId, 'Preise dürfen nicht negativ sein.', 'error');
        }

        $stmt = $pdo->prepare('INSERT INTO project_materials (project_id, material_id, quantity, purchase_price, selling_price) VALUES (?,?,?,?,?)');
        $stmt->execute([$projectId, $materialId, $quantity, $purchasePrice, $sellingPrice]);
        redirectToProject($projectId, 'Material erfolgreich hinzugefügt.');
        break;

    case 'edit':
        $id           = (int)($_POST['id']            ?? 0);
        $projectId    = (int)($_POST['project_id']    ?? 0);
        $materialId   = (int)($_POST['material_id']   ?? 0);
        $quantity     = (float)($_POST['quantity']     ?? 0);
        $purchasePrice = (float)($_POST['purchase_price'] ?? 0);
        $sellingPrice  = (float)($_POST['selling_price']  ?? 0);

        if (!$id || !$projectId || !$materialId || $quantity <= 0) {
            redirectToProject($projectId, 'Ungültige Eingaben.', 'error');
        }
        if ($purchasePrice < 0 || $sellingPrice < 0) {
            redirectToProject($projectId, 'Preise dürfen nicht negativ sein.', 'error');
        }

        $stmt = $pdo->prepare('UPDATE project_materials SET material_id=?, quantity=?, purchase_price=?, selling_price=? WHERE id=? AND project_id=?');
        $stmt->execute([$materialId, $quantity, $purchasePrice, $sellingPrice, $id, $projectId]);
        redirectToProject($projectId, 'Material erfolgreich aktualisiert.');
        break;

    case 'delete':
        $id        = (int)($_POST['id']         ?? 0);
        $projectId = (int)($_POST['project_id'] ?? 0);

        if (!$id || !$projectId) {
            redirectToProject($projectId, 'Ungültige ID.', 'error');
        }

        $stmt = $pdo->prepare('DELETE FROM project_materials WHERE id = ? AND project_id = ?');
        $stmt->execute([$id, $projectId]);
        redirectToProject($projectId, 'Material erfolgreich entfernt.');
        break;

    default:
        header('Location: index.php?page=projects');
        exit;
}
