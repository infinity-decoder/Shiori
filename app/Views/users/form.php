<?php
// Views/users/form.php
$isEdit = ($mode === 'edit');
$action = $isEdit ? BASE_URL . '/users/update' : BASE_URL . '/users/store';
$u = $user ?? [];

// Default values
$name = Auth::getOldInput('name', $u['name'] ?? '');
$username = Auth::getOldInput('username', $u['username'] ?? '');
$email = Auth::getOldInput('email', $u['email'] ?? '');
$role = Auth::getOldInput('role', $u['role'] ?? 'viewer');
$isActive = isset($u['is_active']) ? $u['is_active'] : 1; 
// If old input exists for is_active?? (Checkbox handling is tricky with old input if unchecked, but we only set default 1 for create)
// For edit, trust database unless old input
?>
<div class="container" style="max-width: 700px;">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">
      <?= $isEdit ? 'Edit User' : 'Add New User'; ?>
    </h1>
    <a href="<?= BASE_URL; ?>/users" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left me-1"></i> Back to Users
    </a>
  </div>

  <div class="card card-soft border-0 shadow-sm">
    <div class="card-body p-4">
      <form method="POST" action="<?= $action; ?>">
        <?= CSRF::field(); ?>
        <?php if ($isEdit): ?>
          <input type="hidden" name="id" value="<?= $u['id']; ?>">
        <?php endif; ?>

        <div class="mb-3">
          <label class="form-label fw-bold small text-uppercase text-muted">Full Name</label>
          <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name); ?>" required autofocus>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label fw-bold small text-uppercase text-muted">Username</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0">@</span>
              <input type="text" name="username" class="form-control border-start-0 ps-0" value="<?= htmlspecialchars($username); ?>" required>
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold small text-uppercase text-muted">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email); ?>">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold small text-uppercase text-muted">
            Password 
            <?php if ($isEdit): ?>
              <span class="fw-normal text-muted text-lowercase ms-1">(leave blank to keep current)</span>
            <?php endif; ?>
          </label>
          <input type="password" name="password" class="form-control" <?= $isEdit ? '' : 'required'; ?>>
          <?php if (!$isEdit): ?>
            <div class="form-text">Minimum 6 characters recommended.</div>
          <?php endif; ?>
        </div>

        <hr class="my-4 text-muted">

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label fw-bold small text-uppercase text-muted">Role</label>
            <select name="role" class="form-select">
              <option value="viewer" <?= $role === 'viewer' ? 'selected' : ''; ?>>Viewer (Read Only)</option>
              <option value="staff" <?= $role === 'staff' ? 'selected' : ''; ?>>Staff (Limited Ops)</option>
              <option value="admin" <?= $role === 'admin' ? 'selected' : ''; ?>>Admin (Manage Students)</option>
              <option value="super_admin" <?= $role === 'super_admin' ? 'selected' : ''; ?>>Super Admin (Full Access)</option>
            </select>
          </div>
          <div class="col-md-6 d-flex align-items-end">
            <?php 
               // Prevent deactivating own account
               $disabled = ($isEdit && $u['id'] == Auth::user()['id']) ? 'disabled' : '';
               $checked = $isActive ? 'checked' : '';
            ?>
            <div class="form-check form-switch mb-2">
              <input class="form-check-input" type="checkbox" id="isActiveSwitch" name="is_active" value="1" <?= $checked; ?> <?= $disabled; ?>>
              <label class="form-check-label" for="isActiveSwitch">Account Active</label>
            </div>
            <?php if ($disabled): ?>
              <input type="hidden" name="is_active" value="1">
              <div class="ms-2 mb-2 text-muted small">(It's you!)</div>
            <?php endif; ?>
          </div>
        </div>

        <div class="alert alert-light border small text-muted">
          <i class="bi bi-info-circle me-1"></i>
          <strong>Super Admins</strong> can access Settings and User Management.<br>
          <strong>Admins</strong> can manage Students and Lookups.<br>
          <strong>Active</strong> status is required to login.
        </div>

        <div class="mt-4 text-end">
           <a href="<?= BASE_URL; ?>/users" class="btn btn-light me-2">Cancel</a>
           <button type="submit" class="btn btn-primary px-4">
             <i class="bi bi-save me-1"></i> Save User
           </button>
        </div>

      </form>
    </div>
  </div>
</div>
