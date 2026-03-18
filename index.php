<?php
require_once __DIR__ . '/config/database.php';

session_start();

$allowedPages = ['dashboard', 'customers', 'projects', 'project_detail', 'materials', 'settings'];
$page         = $_GET['page'] ?? 'dashboard';

if (!in_array($page, $allowedPages, true)) {
    $page = 'dashboard';
}

$pageFile = __DIR__ . '/pages/' . $page . '.php';
if (!file_exists($pageFile)) {
    $page     = 'dashboard';
    $pageFile = __DIR__ . '/pages/dashboard.php';
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<?php include __DIR__ . '/includes/sidebar.php'; ?>
<div class="main-content">
<?php include $pageFile; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
