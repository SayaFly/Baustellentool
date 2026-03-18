<?php
require_once __DIR__ . '/../config/database.php';
$settings = getSettings();
?>
<div class="top-navbar d-flex align-items-center">
    <h5 class="mb-0 fw-semibold"><i class="bi bi-gear me-2 text-primary"></i>Einstellungen</h5>
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
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3">
                    <h6 class="fw-semibold mb-0"><i class="bi bi-percent me-2 text-primary"></i>Mehrwertsteuer</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="actions/settings_actions.php">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="redirect" value="index.php?page=settings">

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       name="vat_enabled" id="vatEnabled" value="1"
                                       <?= $settings['vat_enabled'] ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="vatEnabled">
                                    MwSt-Berechnung aktivieren
                                </label>
                            </div>
                            <div class="form-text">Wenn aktiviert, werden Brutto-Preise mit MwSt berechnet.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold" for="vatRate">MwSt-Satz (%)</label>
                            <div class="input-group" style="max-width: 200px;">
                                <input type="number" class="form-control" name="vat_rate" id="vatRate"
                                       value="<?= htmlspecialchars($settings['vat_rate']) ?>"
                                       step="0.01" min="0" max="100" required>
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="form-text">Standard: 19% (Deutschland)</div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Speichern
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
