<div class="container">
  <div class="row mb-3 align-items-center">
    <div class="col">
      <h1 class="h4 mb-0">Activity Log</h1>
      <p class="text-muted">Track system usage and changes.</p>
    </div>
    <div class="col-auto">
      <a href="<?= $baseUrl; ?>/dashboard" class="btn btn-outline-secondary">Dashboard</a>
    </div>
  </div>

  <div class="card card-soft shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped mb-0">
          <thead class="table-light">
            <tr>
              <th>Time</th>
              <th>User</th>
              <th>Action</th>
              <th>Entity</th>
              <th>Details</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($logs)): ?>
              <tr><td colspan="5" class="text-center py-4">No activity recorded yet.</td></tr>
            <?php else: ?>
              <?php foreach ($logs as $log): ?>
                <tr>
                  <td class="text-nowrap"><?= e($log['created_at']); ?></td>
                  <td><?= e($log['username'] ?? 'Unknown'); ?></td>
                  <td><span class="badge bg-secondary"><?= e($log['action']); ?></span></td>
                  <td><?= e($log['entity_type']); ?> #<?= e($log['entity_id']); ?></td>
                  <td>
                    <?php 
                      $details = json_decode($log['details'] ?? '', true);
                      if ($details) {
                          foreach ($details as $k => $v) {
                              echo '<small class="d-block"><strong>' . e($k) . ':</strong> ' . e($v) . '</small>';
                          }
                      }
                    ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php if ($totalPages > 1): ?>
      <div class="card-footer bg-white">
        <nav>
          <ul class="pagination justify-content-center mb-0">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
              <li class="page-item <?= $p === $page ? 'active' : ''; ?>">
                <a class="page-link" href="<?= $baseUrl; ?>/activity?page=<?= $p; ?>"><?= $p; ?></a>
              </li>
            <?php endfor; ?>
          </ul>
        </nav>
      </div>
    <?php endif; ?>
  </div>
</div>
