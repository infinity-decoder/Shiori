<?php
// Views/users/index.php
?>
<div class="container-fluid">
  <div class="row mb-4">
    <div class="col-md-6">
      <h1 class="h3 mb-0 text-gray-800">
        <i class="bi bi-people-fill me-2 text-primary"></i>User Management
      </h1>
      <p class="text-muted small mt-1">Manage system users, roles, and access.</p>
    </div>
    <div class="col-md-6 text-md-end">
      <a href="<?= BASE_URL; ?>/users/create" class="btn btn-primary">
        <i class="bi bi-person-plus-fill me-2"></i>Add New User
      </a>
    </div>
  </div>

  <!-- Users Table -->
  <div class="card card-soft border-0 shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-light">
            <tr>
              <th class="ps-4">ID</th>
              <th>Name / Email</th>
              <th>Username</th>
              <th>Role</th>
              <th>Status</th>
              <th>Created</th>
              <th class="text-end pe-4">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($users)): ?>
              <tr><td colspan="7" class="text-center py-5 text-muted">No users found.</td></tr>
            <?php else: ?>
              <?php foreach ($users as $u): ?>
              <tr>
                <td class="ps-4 text-muted">#<?= $u['id']; ?></td>
                <td>
                  <div class="fw-bold text-dark"><?= htmlspecialchars($u['name']); ?></div>
                  <div class="small text-muted"><?= htmlspecialchars($u['email']); ?></div>
                </td>
                <td>
                  <span class="badge bg-light text-dark border px-2">@<?= htmlspecialchars($u['username']); ?></span>
                </td>
                <td>
                  <?php
                    $roleBadges = [
                      'super_admin' => 'bg-danger',
                      'admin'       => 'bg-success',
                      'staff'       => 'bg-info text-dark',
                      'viewer'      => 'bg-secondary'
                    ];
                    $badge = $roleBadges[$u['role']] ?? 'bg-secondary';
                    $roleLabel = ucwords(str_replace('_', ' ', $u['role']));
                  ?>
                  <span class="badge <?= $badge; ?> rounded-pill"><?= $roleLabel; ?></span>
                </td>
                <td>
                  <?php if (!empty($u['is_active'])): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Active</span>
                  <?php else: ?>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">Inactive</span>
                  <?php endif; ?>
                </td>
                <td class="small text-muted"><?= date('M j, Y', strtotime($u['created_at'])); ?></td>
                <td class="text-end pe-4">
                  <?php if ($u['id'] == Auth::user()['id']): ?>
                    <a href="<?= BASE_URL; ?>/users/edit?id=<?= $u['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit Profile">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <button class="btn btn-sm btn-outline-secondary" disabled title="Cannot delete yourself">
                      <i class="bi bi-trash"></i>
                    </button>
                  <?php else: ?>
                    <div class="d-flex gap-1 justify-content-end">
                      <a href="<?= BASE_URL; ?>/users/edit?id=<?= $u['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                        <i class="bi bi-pencil"></i>
                      </a>
                      <form method="POST" action="<?= BASE_URL; ?>/users/delete" onsubmit="return confirm('Delete this user? This cannot be undone.');">
                        <?= CSRF::field(); ?>
                        <input type="hidden" name="id" value="<?= $u['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                          <i class="bi bi-trash"></i>
                        </button>
                      </form>
                    </div>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
