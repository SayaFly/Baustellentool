<?php
require_once __DIR__ . '/../config/database.php';
$pdo      = getDB();
$settings = getSettings();

$projectId = (int)($_GET['id'] ?? 0);
if (!$projectId) {
    header('Location: index.php?page=projects');
    exit;
}

$stmtP = $pdo->prepare('SELECT p.*, c.name AS customer_name FROM projects p JOIN customers c ON c.id = p.customer_id WHERE p.id = ?');
$stmtP->execute([$projectId]);
$project = $stmtP->fetch();
if (!$project) {
    header('Location: index.php?page=projects&msg=Baustelle+nicht+gefunden&type=error');
    exit;
}

$stmtPM = $pdo->prepare('
    SELECT pm.*, m.name AS material_name, m.unit
    FROM project_materials pm
    JOIN materials m ON m.id = pm.material_id
    WHERE pm.project_id = ?
    ORDER BY m.name
');
$stmtPM->execute([$projectId]);
$projectMaterials = $stmtPM->fetchAll();

$globalMaterials = $pdo->query('SELECT id, name, unit FROM materials ORDER BY name')->fetchAll();

$vatEnabled = (bool)$settings['vat_enabled'];
$vatRate    = (float)$settings['vat_rate'];

$totalPurchase = 0;
$totalNetto    = 0;
foreach ($projectMaterials as $pm) {
    $totalPurchase += (float)$pm['purchase_price'] * (float)$pm['quantity'];
    $totalNetto    += (float)$pm['selling_price']  * (float)$pm['quantity'];
}
$totalProfit = $totalNetto - $totalPurchase;
$vatAmount   = $vatEnabled ? $totalNetto * ($vatRate / 100) : 0;
$totalBrutto = $totalNetto + $vatAmount;

$areaSize = (float)($project['area_size'] ?? 0);
$pricePerM2Netto  = ($areaSize > 0) ? $totalNetto  / $areaSize : 0;
$pricePerM2Brutto = ($areaSize > 0) ? $totalBrutto / $areaSize : 0;

$statusMap = ['geplant' => 'primary', 'in Arbeit' => 'warning', 'abgeschlossen' => 'success'];

$editPM = null;
if (isset($_GET['edit_pm'])) {
    $stmtEM = $pdo->prepare('SELECT * FROM project_materials WHERE id = ? AND project_id = ?');
    $stmtEM->execute([(int)$_GET['edit_pm'], $projectId]);
    $editPM = $stmtEM->fetch();
}
?>
<div class="top-navbar d-flex align-items-center gap-2">
    <a href="index.php?page=projects" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h5 class="mb-0 fw-semibold"><i class="bi bi-building me-2 text-primary"></i><?= htmlspecialchars($project['name']) ?></h5>
    <span class="badge bg-<?= $statusMap[$project['status']] ?? 'secondary' ?> ms-1">
        <?= htmlspecialchars($project['status']) ?>
    </span>
</div>

<?php if (isset($_GET['msg'])): ?>
<div class="flash-message">
    <div class="alert alert-<?= ($_GET['type'] ?? 'success') === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_GET['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<div class="p-4">
    <!-- Project Info -->
    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-3">Projektinformationen</h6>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Kunde</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($project['customer_name']) ?></dd>
                        <dt class="col-sm-4">Datum</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($project['created_at']) ?></dd>
                        <dt class="col-sm-4">Fläche</dt>
                        <dd class="col-sm-8"><?= $areaSize > 0 ? number_format($areaSize, 2, ',', '.') . ' m²' : '–' ?></dd>
                        <?php if ($project['description']): ?>
                        <dt class="col-sm-4">Beschreibung</dt>
                        <dd class="col-sm-8"><?= nl2br(htmlspecialchars($project['description'])) ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-3">Kostenübersicht</h6>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Einkauf gesamt:</span>
                        <strong><?= number_format($totalPurchase, 2, ',', '.') ?> €</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Verkauf Netto:</span>
                        <strong><?= number_format($totalNetto, 2, ',', '.') ?> €</strong>
                    </div>
                    <?php if ($vatEnabled): ?>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">MwSt (<?= number_format($vatRate, 0) ?>%):</span>
                        <strong><?= number_format($vatAmount, 2, ',', '.') ?> €</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Verkauf Brutto:</span>
                        <strong class="text-info"><?= number_format($totalBrutto, 2, ',', '.') ?> €</strong>
                    </div>
                    <?php endif; ?>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between">
                        <span class="fw-semibold">Gewinn:</span>
                        <strong class="<?= $totalProfit >= 0 ? 'text-success' : 'text-danger' ?>">
                            <?= number_format($totalProfit, 2, ',', '.') ?> €
                        </strong>
                    </div>
                    <?php if ($areaSize > 0): ?>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Preis/m² Netto:</span>
                        <strong><?= number_format($pricePerM2Netto, 2, ',', '.') ?> €</strong>
                    </div>
                    <?php if ($vatEnabled): ?>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Preis/m² Brutto:</span>
                        <strong class="text-info"><?= number_format($pricePerM2Brutto, 2, ',', '.') ?> €</strong>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Materials Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex align-items-center justify-content-between border-0 pt-3">
            <span class="fw-semibold"><i class="bi bi-box-seam me-2 text-primary"></i>Materialien</span>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#pmModal" onclick="resetPMForm()">
                <i class="bi bi-plus-lg me-1"></i>Material hinzufügen
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Material</th>
                            <th>Einheit</th>
                            <th class="text-end">Menge</th>
                            <th class="text-end">EK/Einheit</th>
                            <th class="text-end">VK/Einheit</th>
                            <th class="text-end">EK gesamt</th>
                            <th class="text-end">VK Netto</th>
                            <?php if ($vatEnabled): ?>
                            <th class="text-end">VK Brutto</th>
                            <?php endif; ?>
                            <th class="text-end">Gewinn</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projectMaterials as $pm):
                            $linePurchase = (float)$pm['purchase_price'] * (float)$pm['quantity'];
                            $lineNetto    = (float)$pm['selling_price']  * (float)$pm['quantity'];
                            $lineVat      = $vatEnabled ? $lineNetto * ($vatRate / 100) : 0;
                            $lineBrutto   = $lineNetto + $lineVat;
                            $lineProfit   = $lineNetto - $linePurchase;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($pm['material_name']) ?></td>
                            <td><?= htmlspecialchars($pm['unit']) ?></td>
                            <td class="text-end"><?= number_format((float)$pm['quantity'], 2, ',', '.') ?></td>
                            <td class="text-end"><?= number_format((float)$pm['purchase_price'], 2, ',', '.') ?> €</td>
                            <td class="text-end"><?= number_format((float)$pm['selling_price'], 2, ',', '.') ?> €</td>
                            <td class="text-end"><?= number_format($linePurchase, 2, ',', '.') ?> €</td>
                            <td class="text-end"><?= number_format($lineNetto, 2, ',', '.') ?> €</td>
                            <?php if ($vatEnabled): ?>
                            <td class="text-end"><?= number_format($lineBrutto, 2, ',', '.') ?> €</td>
                            <?php endif; ?>
                            <td class="text-end <?= $lineProfit >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= number_format($lineProfit, 2, ',', '.') ?> €
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary"
                                    onclick="editPM(<?= (int)$pm['id'] ?>, <?= (int)$pm['material_id'] ?>, <?= (float)$pm['quantity'] ?>, <?= (float)$pm['purchase_price'] ?>, <?= (float)$pm['selling_price'] ?>)"
                                    title="Bearbeiten">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="actions/project_material_actions.php" class="d-inline"
                                      onsubmit="return confirm('Material wirklich entfernen?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int)$pm['id'] ?>">
                                    <input type="hidden" name="project_id" value="<?= $projectId ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Löschen">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($projectMaterials)): ?>
                        <tr><td colspan="<?= $vatEnabled ? 10 : 9 ?>" class="text-center text-muted py-3">Keine Materialien vorhanden</td></tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($projectMaterials)): ?>
                    <tfoot class="table-light fw-semibold">
                        <tr>
                            <td colspan="5">Summen</td>
                            <td class="text-end"><?= number_format($totalPurchase, 2, ',', '.') ?> €</td>
                            <td class="text-end"><?= number_format($totalNetto, 2, ',', '.') ?> €</td>
                            <?php if ($vatEnabled): ?>
                            <td class="text-end text-info"><?= number_format($totalBrutto, 2, ',', '.') ?> €</td>
                            <?php endif; ?>
                            <td class="text-end <?= $totalProfit >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= number_format($totalProfit, 2, ',', '.') ?> €
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Project Material Modal -->
<div class="modal fade" id="pmModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="actions/project_material_actions.php" id="pmForm">
                <input type="hidden" name="action" value="add" id="pmAction">
                <input type="hidden" name="id" value="" id="pmId">
                <input type="hidden" name="project_id" value="<?= $projectId ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="pmModalLabel">Material hinzufügen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Material <span class="text-danger">*</span></label>
                        <select name="material_id" id="pmMaterial" class="form-select" required>
                            <option value="">Bitte wählen...</option>
                            <?php foreach ($globalMaterials as $m): ?>
                            <option value="<?= (int)$m['id'] ?>" data-unit="<?= htmlspecialchars($m['unit']) ?>">
                                <?= htmlspecialchars($m['name']) ?> (<?= htmlspecialchars($m['unit']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Menge <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="quantity" id="pmQty" class="form-control" step="0.01" min="0.01" required>
                                <span class="input-group-text" id="pmUnit">–</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Einkaufspreis/Einheit (€) <span class="text-danger">*</span></label>
                            <input type="number" name="purchase_price" id="pmPurchase" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Verkaufspreis/Einheit (€) <span class="text-danger">*</span></label>
                            <input type="number" name="selling_price" id="pmSelling" class="form-control" step="0.01" min="0" required>
                        </div>
                    </div>
                    <!-- Live Calculation Preview -->
                    <div class="card bg-light border-0 d-none" id="calcPreview">
                        <div class="card-body py-2">
                            <div class="row text-center">
                                <div class="col">
                                    <div class="text-muted small">EK gesamt</div>
                                    <div class="fw-bold" id="previewPurchase">0,00 €</div>
                                </div>
                                <div class="col">
                                    <div class="text-muted small">VK Netto</div>
                                    <div class="fw-bold text-primary" id="previewNetto">0,00 €</div>
                                </div>
                                <?php if ($vatEnabled): ?>
                                <div class="col">
                                    <div class="text-muted small">VK Brutto</div>
                                    <div class="fw-bold text-info" id="previewBrutto">0,00 €</div>
                                </div>
                                <?php endif; ?>
                                <div class="col">
                                    <div class="text-muted small">Gewinn</div>
                                    <div class="fw-bold" id="previewProfit">0,00 €</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn btn-primary">Speichern</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const VAT_ENABLED = <?= $vatEnabled ? 'true' : 'false' ?>;
const VAT_RATE    = <?= $vatRate ?>;

function formatEuro(val) {
    return val.toLocaleString('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' €';
}

function recalc() {
    const qty      = parseFloat(document.getElementById('pmQty').value)      || 0;
    const purchase = parseFloat(document.getElementById('pmPurchase').value) || 0;
    const selling  = parseFloat(document.getElementById('pmSelling').value)  || 0;

    const totalPurchase = qty * purchase;
    const totalNetto    = qty * selling;
    const totalBrutto   = VAT_ENABLED ? totalNetto * (1 + VAT_RATE / 100) : totalNetto;
    const profit        = totalNetto - totalPurchase;

    document.getElementById('previewPurchase').textContent = formatEuro(totalPurchase);
    document.getElementById('previewNetto').textContent    = formatEuro(totalNetto);
    if (VAT_ENABLED) document.getElementById('previewBrutto').textContent = formatEuro(totalBrutto);

    const profitEl = document.getElementById('previewProfit');
    profitEl.textContent = formatEuro(profit);
    profitEl.className = 'fw-bold ' + (profit >= 0 ? 'text-success' : 'text-danger');

    document.getElementById('calcPreview').classList.remove('d-none');
}

['pmQty','pmPurchase','pmSelling'].forEach(id => {
    document.getElementById(id).addEventListener('input', recalc);
});

document.getElementById('pmMaterial').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    document.getElementById('pmUnit').textContent = opt.dataset.unit || '–';
});

function resetPMForm() {
    document.getElementById('pmAction').value = 'add';
    document.getElementById('pmId').value = '';
    document.getElementById('pmModalLabel').textContent = 'Material hinzufügen';
    document.getElementById('pmForm').reset();
    document.getElementById('pmUnit').textContent = '–';
    document.getElementById('calcPreview').classList.add('d-none');
}

function editPM(id, materialId, qty, purchase, selling) {
    document.getElementById('pmAction').value = 'edit';
    document.getElementById('pmId').value = id;
    document.getElementById('pmModalLabel').textContent = 'Material bearbeiten';
    document.getElementById('pmMaterial').value = materialId;
    document.getElementById('pmMaterial').dispatchEvent(new Event('change'));
    document.getElementById('pmQty').value = qty;
    document.getElementById('pmPurchase').value = purchase;
    document.getElementById('pmSelling').value = selling;
    recalc();
    new bootstrap.Modal(document.getElementById('pmModal')).show();
}
</script>
