<?php View::partial('layouts/main.php', ['title' => $title]); ?>

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
            <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">User Management</button>
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
                                    <select name="type" class="form-select">
                                        <option value="text">Text</option>
                                        <option value="number">Number</option>
                                        <option value="date">Date</option>
                                        <option value="textarea">Text Area</option>
                                    </select>
                                </div>
                                <button class="btn btn-primary w-100">Add Field</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Tab -->
        <div class="tab-pane fade" id="users" role="tabpanel">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">Users</div>
                        <div class="card-body p-0">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                    <tr>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Created</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($u['name'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($u['username']) ?></td>
                                        <td><?= htmlspecialchars($u['email'] ?? '') ?></td>
                                        <td>
                                            <span class="badge bg-<?= $u['role'] === 'admin' ? 'danger' : ($u['role'] === 'staff' ? 'primary' : 'secondary') ?>">
                                                <?= htmlspecialchars(ucfirst($u['role'])) ?>
                                            </span>
                                        </td>
                                        <td><?= $u['created_at'] ?></td>
                                        <td>
                                            <?php if ($u['id'] != Auth::user()['id']): ?>
                                            <form method="POST" action="<?= BASE_URL ?>/settings/users/delete" class="d-inline" onsubmit="return confirm('Delete user?');">
                                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                            </form>
                                            <?php else: ?>
                                                <span class="text-muted small">(You)</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">Add User</div>
                        <div class="card-body">
                            <form method="POST" action="<?= BASE_URL ?>/settings/users/store">
                                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" name="username" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Role</label>
                                    <select name="role" class="form-select">
                                        <option value="viewer">Viewer</option>
                                        <option value="staff">Staff</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                                <button class="btn btn-primary w-100">Create User</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php View::partial('layouts/footer.php'); ?>
