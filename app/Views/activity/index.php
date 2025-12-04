<?php View::partial('layouts/main.php', ['title' => $title]); ?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Activity Log</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Activity</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-history me-1"></i>
            Recent Activity
        </div>
        <div class="card-body">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Entity</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="text-nowrap"><?= $log['created_at'] ?></td>
                            <td><?= htmlspecialchars($log['username'] ?? 'Unknown') ?></td>
                            <td>
                                <span class="badge bg-<?= $log['action'] === 'delete' ? 'danger' : ($log['action'] === 'create' ? 'success' : 'primary') ?>">
                                    <?= htmlspecialchars(strtoupper($log['action'])) ?>
                                </span>
                            </td>
                            <td>
                                <?= htmlspecialchars($log['entity_type']) ?> #<?= $log['entity_id'] ?>
                            </td>
                            <td>
                                <?php 
                                    $details = json_decode($log['details'], true);
                                    if ($details) {
                                        foreach ($details as $k => $v) {
                                            echo '<strong>' . htmlspecialchars($k) . ':</strong> ' . htmlspecialchars($v) . '<br>';
                                        }
                                    }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php View::partial('layouts/footer.php'); ?>
