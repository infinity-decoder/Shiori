

<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1>Settings</h1>
            <p class="text-muted">Manage application settings, fields, and users.</p>
        </div>
    </div>

    <ul class="nav nav-tabs mb-4" id="settingsTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="fields-tab" data-bs-toggle="tab" data-bs-target="#fields" type="button" role="tab">Dynamic Fields</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button" role="tab">Recovery Setup</button>
        </li>
    </ul>

    <div class="tab-content" id="settingsTabContent">
        
        <!-- Fields Tab -->
        <div class="tab-pane fade show active" id="fields" role="tabpanel">
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
                                                <form method="POST" action="<?= BASE_URL ?>/fields/toggle" class="d-inline">
                                                    <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                                    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                                                    <button class="btn btn-sm btn-outline-primary">
                                                        <?= $f['is_active'] ? 'Hide' : 'Show' ?>
                                                    </button>
                                                </form>
                                                <?php if ($f['is_custom']): ?>
                                                    <form method="POST" action="<?= BASE_URL ?>/fields/delete" class="d-inline ms-1" onsubmit="return confirm('Delete this field? Data will be lost.');">
                                                        <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                                        <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                <?php endif; ?>
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

        <!-- Email Tab -->
        <div class="tab-pane fade" id="email" role="tabpanel">
            <div class="row">
                <div class="col-md-8 col-lg-6">
                    <div class="card">
                        <div class="card-header">SMTP Configuration</div>
                        <div class="card-body">
                            <form method="POST" action="<?= BASE_URL ?>/settings/email">
                                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                                
                                <div class="row mb-3">
                                    <div class="col-md-8">
                                        <label class="form-label">SMTP Host</label>
                                        <input type="text" name="smtp_host" class="form-control" value="<?= htmlspecialchars($email['smtp_host'] ?? '') ?>" placeholder="e.g. smtp.gmail.com">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Port</label>
                                        <input type="number" name="smtp_port" class="form-control" value="<?= htmlspecialchars($email['smtp_port'] ?? '587') ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">SMTP Username / Email</label>
                                    <input type="text" name="smtp_user" class="form-control" value="<?= htmlspecialchars($email['smtp_user'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">SMTP Password</label>
                                    <input type="password" name="smtp_pass" class="form-control" value="<?= htmlspecialchars($email['smtp_pass'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">From Name</label>
                                    <input type="text" name="from_name" class="form-control" value="<?= htmlspecialchars($email['mail_from_name'] ?? 'Shiori Admin') ?>">
                                </div>
                                
                                <button class="btn btn-primary">Save Settings</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
                </div>
            </div>
        </div>


    </div>
</div>

    </div>
</div>

<script>
// Show/hide options field based on field type
document.addEventListener('DOMContentLoaded', function() {
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
        
        // Trigger on page load
        fieldTypeSelect.dispatchEvent(new Event('change'));
    }
});
</script>
