<?php
require_once __DIR__ . '/../config/database.php';
$pdo = getDB();

$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $stmt = $pdo->prepare('SELECT * FROM customers WHERE name LIKE ? OR email LIKE ? OR phone LIKE ? ORDER BY name');
    $like = '%' . $search . '%';
    $stmt->execute([$like, $like, $like]);
} else {
    $stmt = $pdo->query('SELECT * FROM customers ORDER BY name');
}
$customers = $stmt->fetchAll();

$editCustomer = null;
if (isset($_GET['edit'])) {
    $stmtE = $pdo->prepare('SELECT * FROM customers WHERE id = ?');
    $stmtE->execute([(int)$_GET['edit']]);
    $editCustomer = $stmtE->fetch();
}
?>
<div class="top-navbar d-flex align-items-center justify-content-between">
    <h5 class="mb-0 fw-semibold"><i class="bi bi-people me-2 text-primary"></i>Kunden</h5>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#customerModal" onclick="resetForm()">
        <i class="bi bi-plus-lg me-1"></i>Neuer Kunde
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
    <!-- Search -->
    <form method="GET" action="index.php" class="mb-3 d-flex gap-2">
        <input type="hidden" name="page" value="customers">
        <input type="text" name="search" class="form-control form-control-sm w-auto" placeholder="Suchen..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-search"></i></button>
        <?php if ($search): ?>
        <a href="index.php?page=customers" class="btn btn-outline-danger btn-sm"><i class="bi bi-x-lg"></i></a>
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
                            <th>Adresse</th>
                            <th>Telefon</th>
                            <th>E-Mail</th>
                            <th>Zahlungsstatus</th>
                            <th>Erstellt</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $c): ?>
                        <tr>
                            <td><?= (int)$c['id'] ?></td>
                            <td><?= htmlspecialchars($c['name']) ?></td>
                            <td><?= htmlspecialchars($c['address'] ?? '') ?></td>
                            <td><?= htmlspecialchars($c['phone'] ?? '') ?></td>
                            <td><?= htmlspecialchars($c['email'] ?? '') ?></td>
                            <td>
                                <span class="badge bg-<?= $c['payment_status'] === 'bezahlt' ? 'success' : 'danger' ?>">
                                    <?= htmlspecialchars($c['payment_status']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($c['created_at'] ?? '') ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary btn-edit-customer"
                                    data-id="<?= (int)$c['id'] ?>"
                                    data-name="<?= htmlspecialchars($c['name'], ENT_QUOTES) ?>"
                                    data-address="<?= htmlspecialchars($c['address'] ?? '', ENT_QUOTES) ?>"
                                    data-phone="<?= htmlspecialchars($c['phone'] ?? '', ENT_QUOTES) ?>"
                                    data-email="<?= htmlspecialchars($c['email'] ?? '', ENT_QUOTES) ?>"
                                    data-payment-status="<?= htmlspecialchars($c['payment_status'], ENT_QUOTES) ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="actions/customer_actions.php" class="d-inline"
                                      onsubmit="return confirm('Kunden wirklich löschen?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($customers)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-3">Keine Kunden gefunden</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Customer Modal -->
<div class="modal fade" id="customerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="actions/customer_actions.php" id="customerForm">
                <input type="hidden" name="action" value="add" id="formAction">
                <input type="hidden" name="id" value="" id="customerId">
                <div class="modal-header">
                    <h5 class="modal-title" id="customerModalLabel">Neuer Kunde</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="fieldName" class="form-control" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Adresse</label>
                        <textarea name="address" id="fieldAddress" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col">
                            <label class="form-label">Telefon</label>
                            <input type="text" name="phone" id="fieldPhone" class="form-control" maxlength="50">
                        </div>
                        <div class="col">
                            <label class="form-label">E-Mail</label>
                            <input type="email" name="email" id="fieldEmail" class="form-control" maxlength="255">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Zahlungsstatus</label>
                        <select name="payment_status" id="fieldPaymentStatus" class="form-select">
                            <option value="offen">Offen</option>
                            <option value="bezahlt">Bezahlt</option>
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
    document.getElementById('customerId').value = '';
    document.getElementById('customerModalLabel').textContent = 'Neuer Kunde';
    document.getElementById('customerForm').reset();
}

document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-edit-customer');
    if (!btn) return;
    const d = btn.dataset;
    document.getElementById('formAction').value = 'edit';
    document.getElementById('customerId').value = d.id;
    document.getElementById('customerModalLabel').textContent = 'Kunde bearbeiten';
    document.getElementById('fieldName').value = d.name;
    document.getElementById('fieldAddress').value = d.address;
    document.getElementById('fieldPhone').value = d.phone;
    document.getElementById('fieldEmail').value = d.email;
    document.getElementById('fieldPaymentStatus').value = d.paymentStatus;
    new bootstrap.Modal(document.getElementById('customerModal')).show();
});
</script>
