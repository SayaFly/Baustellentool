<?php
require_once __DIR__ . '/../config/database.php';
$pdo = getDB();

$customers = $pdo->query('SELECT id, name FROM customers ORDER BY name')->fetchAll();

$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $stmt = $pdo->prepare('
        SELECT p.*, c.name AS customer_name
        FROM projects p
        JOIN customers c ON c.id = p.customer_id
        WHERE p.name LIKE ? OR c.name LIKE ?
        ORDER BY p.created_at DESC
    ');
    $like = '%' . $search . '%';
    $stmt->execute([$like, $like]);
} else {
    $stmt = $pdo->query('
        SELECT p.*, c.name AS customer_name
        FROM projects p
        JOIN customers c ON c.id = p.customer_id
        ORDER BY p.created_at DESC
    ');
}
$projects = $stmt->fetchAll();

$editProject = null;
if (isset($_GET['edit'])) {
    $stmtE = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
    $stmtE->execute([(int)$_GET['edit']]);
    $editProject = $stmtE->fetch();
}

$statusMap = ['geplant' => 'primary', 'in Arbeit' => 'warning', 'abgeschlossen' => 'success'];
?>
<div class="top-navbar d-flex align-items-center justify-content-between">
    <h5 class="mb-0 fw-semibold"><i class="bi bi-hammer me-2 text-primary"></i>Baustellen</h5>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#projectModal" onclick="resetForm()">
        <i class="bi bi-plus-lg me-1"></i>Neue Baustelle
    </button>
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
    <form method="GET" action="index.php" class="mb-3 d-flex gap-2">
        <input type="hidden" name="page" value="projects">
        <input type="text" name="search" class="form-control form-control-sm w-auto" placeholder="Suchen..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-search"></i></button>
        <?php if ($search): ?>
        <a href="index.php?page=projects" class="btn btn-outline-danger btn-sm"><i class="bi bi-x-lg"></i></a>
        <?php endif; ?>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Kunde</th>
                            <th>Status</th>
                            <th>Fläche (m²)</th>
                            <th>Datum</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $p): ?>
                        <tr>
                            <td><?= (int)$p['id'] ?></td>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td><?= htmlspecialchars($p['customer_name']) ?></td>
                            <td>
                                <span class="badge bg-<?= $statusMap[$p['status']] ?? 'secondary' ?>">
                                    <?= htmlspecialchars($p['status']) ?>
                                </span>
                            </td>
                            <td><?= $p['area_size'] !== null ? number_format((float)$p['area_size'], 2, ',', '.') : '–' ?></td>
                            <td><?= htmlspecialchars($p['created_at']) ?></td>
                            <td>
                                <a href="index.php?page=project_detail&id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline-primary" title="Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-secondary btn-edit-project"
                                    data-id="<?= (int)$p['id'] ?>"
                                    data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>"
                                    data-customer-id="<?= (int)$p['customer_id'] ?>"
                                    data-status="<?= htmlspecialchars($p['status'], ENT_QUOTES) ?>"
                                    data-area="<?= htmlspecialchars($p['area_size'] ?? '', ENT_QUOTES) ?>"
                                    data-date="<?= htmlspecialchars($p['created_at'], ENT_QUOTES) ?>"
                                    data-description="<?= htmlspecialchars($p['description'] ?? '', ENT_QUOTES) ?>"
                                    title="Bearbeiten">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="actions/project_actions.php" class="d-inline"
                                      onsubmit="return confirm('Baustelle wirklich löschen?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Löschen">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($projects)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-3">Keine Baustellen gefunden</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Project Modal -->
<div class="modal fade" id="projectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="actions/project_actions.php" id="projectForm">
                <input type="hidden" name="action" value="add" id="formAction">
                <input type="hidden" name="id" value="" id="projectId">
                <div class="modal-header">
                    <h5 class="modal-title" id="projectModalLabel">Neue Baustelle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="fieldName" class="form-control" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kunde <span class="text-danger">*</span></label>
                        <select name="customer_id" id="fieldCustomer" class="form-select" required>
                            <option value="">Bitte wählen...</option>
                            <?php foreach ($customers as $c): ?>
                            <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col">
                            <label class="form-label">Status</label>
                            <select name="status" id="fieldStatus" class="form-select">
                                <option value="geplant">Geplant</option>
                                <option value="in Arbeit">In Arbeit</option>
                                <option value="abgeschlossen">Abgeschlossen</option>
                            </select>
                        </div>
                        <div class="col">
                            <label class="form-label">Fläche (m²)</label>
                            <input type="number" name="area_size" id="fieldArea" class="form-control" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Datum <span class="text-danger">*</span></label>
                        <input type="date" name="created_at" id="fieldDate" class="form-control" required
                               value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Beschreibung</label>
                        <textarea name="description" id="fieldDescription" class="form-control" rows="3"></textarea>
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
function resetForm() {
    document.getElementById('formAction').value = 'add';
    document.getElementById('projectId').value = '';
    document.getElementById('projectModalLabel').textContent = 'Neue Baustelle';
    document.getElementById('projectForm').reset();
    document.getElementById('fieldDate').value = new Date().toISOString().split('T')[0];
}

document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-edit-project');
    if (!btn) return;
    const d = btn.dataset;
    document.getElementById('formAction').value = 'edit';
    document.getElementById('projectId').value = d.id;
    document.getElementById('projectModalLabel').textContent = 'Baustelle bearbeiten';
    document.getElementById('fieldName').value = d.name;
    document.getElementById('fieldCustomer').value = d.customerId;
    document.getElementById('fieldStatus').value = d.status;
    document.getElementById('fieldArea').value = d.area;
    document.getElementById('fieldDate').value = d.date;
    document.getElementById('fieldDescription').value = d.description;
    new bootstrap.Modal(document.getElementById('projectModal')).show();
});
</script>
