<?php
require_once __DIR__ . '/../config/database.php';
$pdo = getDB();

$materials = $pdo->query('SELECT * FROM materials ORDER BY name')->fetchAll();
?>
<div class="top-navbar d-flex align-items-center justify-content-between">
    <h5 class="mb-0 fw-semibold"><i class="bi bi-box-seam me-2 text-primary"></i>Materialien</h5>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#materialModal" onclick="resetForm()">
        <i class="bi bi-plus-lg me-1"></i>Neues Material
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
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Einheit</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($materials as $m): ?>
                        <tr>
                            <td><?= (int)$m['id'] ?></td>
                            <td><?= htmlspecialchars($m['name']) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($m['unit']) ?></span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary"
                                    onclick="editMaterial(<?= (int)$m['id'] ?>, <?= htmlspecialchars(json_encode($m['name']), ENT_QUOTES) ?>, '<?= htmlspecialchars($m['unit'], ENT_QUOTES) ?>')">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="actions/material_actions.php" class="d-inline"
                                      onsubmit="return confirm('Material wirklich löschen? Es kann nicht gelöscht werden, wenn es in Baustellen verwendet wird.')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($materials)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3">Keine Materialien vorhanden</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Material Modal -->
<div class="modal fade" id="materialModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="actions/material_actions.php" id="materialForm">
                <input type="hidden" name="action" value="add" id="formAction">
                <input type="hidden" name="id" value="" id="materialId">
                <div class="modal-header">
                    <h5 class="modal-title" id="materialModalLabel">Neues Material</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="fieldName" class="form-control" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Einheit</label>
                        <select name="unit" id="fieldUnit" class="form-select">
                            <option value="Stück">Stück</option>
                            <option value="m²">m²</option>
                            <option value="m³">m³</option>
                        </select>
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
    document.getElementById('materialId').value = '';
    document.getElementById('materialModalLabel').textContent = 'Neues Material';
    document.getElementById('materialForm').reset();
}

function editMaterial(id, name, unit) {
    document.getElementById('formAction').value = 'edit';
    document.getElementById('materialId').value = id;
    document.getElementById('materialModalLabel').textContent = 'Material bearbeiten';
    document.getElementById('fieldName').value = name;
    document.getElementById('fieldUnit').value = unit;
    new bootstrap.Modal(document.getElementById('materialModal')).show();
}
</script>
