<?php
require_once __DIR__ . '/../config/database.php';

$pdo      = getDB();
$settings = getSettings();

// Dashboard stats
$totalCustomers = (int)$pdo->query('SELECT COUNT(*) FROM customers')->fetchColumn();
$totalProjects  = (int)$pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn();
$openPayments   = (int)$pdo->query("SELECT COUNT(*) FROM customers WHERE payment_status = 'offen'")->fetchColumn();

$revenueRow = $pdo->query('
    SELECT
        SUM(pm.selling_price * pm.quantity)  AS total_netto,
        SUM(pm.purchase_price * pm.quantity) AS total_purchase
    FROM project_materials pm
')->fetch();

$totalNetto    = (float)($revenueRow['total_netto']    ?? 0);
$totalPurchase = (float)($revenueRow['total_purchase'] ?? 0);
$totalProfit   = $totalNetto - $totalPurchase;

$vatEnabled = (bool)$settings['vat_enabled'];
$vatRate    = (float)$settings['vat_rate'];
$vatAmount  = $vatEnabled ? $totalNetto * ($vatRate / 100) : 0;
$totalBrutto = $totalNetto + $vatAmount;
?>
<div class="top-navbar d-flex align-items-center justify-content-between">
    <h5 class="mb-0 fw-semibold"><i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard</h5>
</div>

<?php if (isset($_GET['msg'])): ?>
<div class="flash-message">
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_GET['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<div class="p-4">
    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                        <i class="bi bi-people-fill text-primary fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Kunden gesamt</div>
                        <div class="fs-3 fw-bold"><?= $totalCustomers ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                        <i class="bi bi-hammer text-warning fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Baustellen gesamt</div>
                        <div class="fs-3 fw-bold"><?= $totalProjects ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                        <i class="bi bi-exclamation-circle-fill text-danger fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Offene Zahlungen</div>
                        <div class="fs-3 fw-bold"><?= $openPayments ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                        <i class="bi bi-graph-up-arrow text-success fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Gewinn gesamt</div>
                        <div class="fs-3 fw-bold <?= $totalProfit >= 0 ? 'text-success' : 'text-danger' ?>">
                            <?= number_format($totalProfit, 2, ',', '.') ?> €
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title text-muted">Umsatz Netto</h6>
                    <div class="fs-2 fw-bold text-primary"><?= number_format($totalNetto, 2, ',', '.') ?> €</div>
                    <small class="text-muted">Einkauf: <?= number_format($totalPurchase, 2, ',', '.') ?> €</small>
                </div>
            </div>
        </div>
        <?php if ($vatEnabled): ?>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title text-muted">Umsatz Brutto (inkl. <?= number_format($vatRate, 0) ?>% MwSt)</h6>
                    <div class="fs-2 fw-bold text-info"><?= number_format($totalBrutto, 2, ',', '.') ?> €</div>
                    <small class="text-muted">MwSt-Anteil: <?= number_format($vatAmount, 2, ',', '.') ?> €</small>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Quick VAT Toggle -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex align-items-center gap-3">
            <i class="bi bi-percent fs-4 text-secondary"></i>
            <div>
                <div class="fw-semibold">MwSt-Berechnung</div>
                <div class="text-muted small">Derzeit: <strong><?= $vatEnabled ? 'Aktiviert (' . number_format($vatRate, 0) . '%)' : 'Deaktiviert' ?></strong></div>
            </div>
            <form method="POST" action="actions/settings_actions.php" class="ms-auto">
                <input type="hidden" name="action" value="toggle_vat">
                <input type="hidden" name="vat_enabled" value="<?= $vatEnabled ? 0 : 1 ?>">
                <input type="hidden" name="vat_rate" value="<?= htmlspecialchars($vatRate) ?>">
                <input type="hidden" name="redirect" value="index.php?page=dashboard">
                <button type="submit" class="btn btn-<?= $vatEnabled ? 'outline-danger' : 'outline-success' ?> btn-sm">
                    <i class="bi bi-toggle-<?= $vatEnabled ? 'on' : 'off' ?>"></i>
                    MwSt <?= $vatEnabled ? 'deaktivieren' : 'aktivieren' ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Recent Projects -->
    <?php
    $recentProjects = $pdo->query('
        SELECT p.*, c.name AS customer_name
        FROM projects p
        JOIN customers c ON c.id = p.customer_id
        ORDER BY p.created_at DESC
        LIMIT 5
    ')->fetchAll();
    ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold border-0 pt-3">
            <i class="bi bi-clock-history me-2 text-primary"></i>Letzte Baustellen
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Kunde</th>
                            <th>Status</th>
                            <th>Datum</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentProjects as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td><?= htmlspecialchars($p['customer_name']) ?></td>
                            <td>
                                <?php
                                $statusMap = ['geplant' => 'primary', 'in Arbeit' => 'warning', 'abgeschlossen' => 'success'];
                                $badge = $statusMap[$p['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $badge ?>"><?= htmlspecialchars($p['status']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($p['created_at']) ?></td>
                            <td>
                                <a href="index.php?page=project_detail&id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentProjects)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-3">Keine Baustellen vorhanden</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
