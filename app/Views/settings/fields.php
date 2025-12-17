
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1>Manage Fields</h1>
            <p class="text-muted">Customize the student record form with dynamic fields.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">Existing Fields</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Label</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fields as $f): ?>
                                <tr>
                                    <td><?= htmlspecialchars($f['label']) ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($f['type']) ?></span></td>
                                    <td>
                                        <?php if ($f['is_active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Hidden</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <!-- Reorder -->
                                            <form method="POST" action="<?= BASE_URL ?>/fields/reorder" class="d-inline">
                                                <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                                                <input type="hidden" name="direction" value="up">
                                                <button class="btn btn-sm btn-outline-secondary" title="Move Up"><i class="bi bi-arrow-up"></i></button>
                                            </form>
                                            <form method="POST" action="<?= BASE_URL ?>/fields/reorder" class="d-inline">
                                                <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                                                <input type="hidden" name="direction" value="down">
                                                <button class="btn btn-sm btn-outline-secondary" title="Move Down"><i class="bi bi-arrow-down"></i></button>
                                            </form>

                                            <!-- Toggle -->
                                            <form method="POST" action="<?= BASE_URL ?>/fields/toggle" class="d-inline">
                                                <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                                                <button class="btn btn-sm btn-outline-primary" title="<?= $f['is_active'] ? 'Hide' : 'Show' ?>">
                                                    <i class="bi <?= $f['is_active'] ? 'bi-eye' : 'bi-eye-slash' ?>"></i>
                                                </button>
                                            </form>

                                            <!-- Edit (All fields can have label edited) -->
                                            <button type="button" 
                                                class="btn btn-sm btn-outline-warning edit-field-btn" 
                                                data-id="<?= $f['id'] ?>" 
                                                data-label="<?= htmlspecialchars($f['label']) ?>"
                                                data-type="<?= $f['type'] ?>"
                                                data-options="<?= htmlspecialchars($f['options'] ?? '') ?>"
                                                title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <!-- Delete (Custom only) -->
                                            <?php if ($f['is_custom']): ?>
                                                <form method="POST" action="<?= BASE_URL ?>/fields/delete" class="d-inline" onsubmit="return confirm('Delete this field? Data will be lost.');">
                                                    <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                                    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                                                    <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">Add Custom Field</div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/fields/store">
                        <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                        <div class="mb-3">
                            <label class="form-label">Field Label</label>
                            <input type="text" name="label" class="form-control" required placeholder="e.g. Blood Group">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Field Type</label>
                            <select name="type" id="field_type" class="form-select">
                                <option value="text">Text</option>
                                <option value="number">Number</option>
                                <option value="date">Date</option>
                                <option value="textarea">Text Area</option>
                                <option value="select">Dropdown (Select)</option>
                                <option value="radio">Radio Buttons</option>
                            </select>
                        </div>
                        <div class="mb-3" id="options_field" style="display:none;">
                            <label class="form-label">Options <span class="text-danger">*</span></label>
                            <textarea name="options" id="options_textarea" class="form-control" placeholder="Comma-separated values (e.g. A+, A-, B+, O+)"></textarea>
                            <div class="form-text">Enter comma-separated options for dropdown or radio buttons</div>
                        </div>
                        <button class="btn btn-primary w-100">Add Field</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Field Modal -->
<div class="modal fade" id="editFieldModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Field</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="<?= BASE_URL ?>/fields/update">
          <div class="modal-body">
            <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="mb-3">
                <label class="form-label">Field Label</label>
                <input type="text" name="label" id="edit_label" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Field Type</label>
                <input type="text" id="edit_type_display" class="form-control" readonly disabled>
                <div class="form-text">Field type cannot be changed after creation.</div>
            </div>

            <div class="mb-3" id="edit_options_container" style="display:none;">
                <label class="form-label">Options</label>
                <textarea name="options" id="edit_options" class="form-control" rows="3"></textarea>
                <div class="form-text">Comma-separated values.</div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary">Save Changes</button> <!-- Changed type to submit in JS or fix HTML -->
            <!-- Wait, button type="submit" is better -->
            <button class="btn btn-primary">Save Changes</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add Field Form Logic
    const fieldTypeSelect = document.getElementById('field_type');
    const optionsField = document.getElementById('options_field');
    const optionsTextarea = document.getElementById('options_textarea');
    
    if (fieldTypeSelect && optionsField) {
        fieldTypeSelect.addEventListener('change', function() {
            if (this.value === 'select' || this.value === 'radio') {
                optionsField.style.display = 'block';
                if (optionsTextarea) optionsTextarea.required = true;
            } else {
                optionsField.style.display = 'none';
                if (optionsTextarea) {
                    optionsTextarea.required = false;
                    optionsTextarea.value = '';
                }
            }
        });
        fieldTypeSelect.dispatchEvent(new Event('change'));
    }

    // Edit Modal Logic
    const editModal = document.getElementById('editFieldModal');
    if (editModal) {
        const bsModal = new bootstrap.Modal(editModal);
        const editButtons = document.querySelectorAll('.edit-field-btn');
        const editId = document.getElementById('edit_id');
        const editLabel = document.getElementById('edit_label');
        const editTypeDisplay = document.getElementById('edit_type_display');
        const editOptions = document.getElementById('edit_options');
        const editOptionsContainer = document.getElementById('edit_options_container');

        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const label = this.getAttribute('data-label');
                const type = this.getAttribute('data-type');
                const options = this.getAttribute('data-options');

                editId.value = id;
                editLabel.value = label;
                editTypeDisplay.value = type.toUpperCase();
                editOptions.value = options;

                if (type === 'select' || type === 'radio') {
                    editOptionsContainer.style.display = 'block';
                } else {
                    editOptionsContainer.style.display = 'none';
                }

                bsModal.show();
            });
        });
    }
});
</script>
