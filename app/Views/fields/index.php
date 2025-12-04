<?php View::partial('layouts/main.php', ['title' => $title]); ?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Manage Fields</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Fields</li>
    </ol>

    <?php if ($msg = Auth::getFlash('success')): ?>
        <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <?php if ($msg = Auth::getFlash('error')): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table me-1"></i>
                    Existing Fields
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Label</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fields as $field): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($field['label']) ?>
                                        <?php if ($field['is_custom']): ?>
                                            <span class="badge bg-info text-dark">Custom</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($field['type']) ?></td>
                                    <td>
                                        <?php if ($field['is_active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Hidden</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/fields/toggle?id=<?= $field['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <?= $field['is_active'] ? 'Hide' : 'Show' ?>
                                        </a>
                                        <?php if ($field['is_custom']): ?>
                                            <a href="<?= BASE_URL ?>/fields/delete?id=<?= $field['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-plus me-1"></i>
                    Add Custom Field
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>/fields/store" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                        <div class="mb-3">
                            <label class="form-label">Field Label</label>
                            <input type="text" name="label" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                <option value="text">Text</option>
                                <option value="number">Number</option>
                                <option value="date">Date</option>
                                <option value="textarea">Text Area</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Field</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php View::partial('layouts/footer.php'); ?>
