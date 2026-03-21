<?php
$currentPage = $_GET['page'] ?? 'dashboard';
?>
<nav class="sidebar d-flex flex-column py-3">
    <div class="sidebar-brand">
        <i class="bi bi-building-gear"></i>
        Baustellentool
    </div>
    <ul class="nav flex-column mt-3">
        <li class="nav-item">
            <a class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>" href="index.php?page=dashboard">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentPage === 'customers' ? 'active' : '' ?>" href="index.php?page=customers">
                <i class="bi bi-people"></i> Kunden
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentPage === 'projects' ? 'active' : '' ?>" href="index.php?page=projects">
                <i class="bi bi-hammer"></i> Baustellen
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentPage === 'materials' ? 'active' : '' ?>" href="index.php?page=materials">
                <i class="bi bi-box-seam"></i> Materialien
            </a>
        </li>
        <li class="nav-item mt-auto">
            <a class="nav-link <?= $currentPage === 'settings' ? 'active' : '' ?>" href="index.php?page=settings">
                <i class="bi bi-gear"></i> Einstellungen
            </a>
        </li>
    </ul>
</nav>
