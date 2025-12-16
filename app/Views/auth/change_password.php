<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card card-soft border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="feature-icon bg-primary bg-gradient text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 3rem; height: 3rem;">
                            <i class="bi bi-shield-lock-fill fs-4"></i>
                        </div>
                        <h2 class="fw-bold text-dark">Change Password</h2>
                        <p class="text-muted">Ensure your account uses a strong password.</p>
                    </div>

                    <form action="/profile/change-password" method="POST">
                        <?php echo CSRF::field(); ?>

                        <div class="mb-3">
                            <label for="current_password" class="form-label text-muted small fw-semibold text-uppercase">Current Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-key"></i></span>
                                <input type="password" class="form-control bg-light border-start-0 ps-0" id="current_password" name="current_password" required autofocus>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label text-muted small fw-semibold text-uppercase">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control bg-light border-start-0 ps-0" id="new_password" name="new_password" required minlength="8">
                            </div>
                            <div class="form-text small">
                                <i class="bi bi-info-circle me-1"></i> Min 8 chars, at least 1 number.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label text-muted small fw-semibold text-uppercase">Confirm New Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="form-control bg-light border-start-0 ps-0" id="confirm_password" name="confirm_password" required minlength="8">
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" style="background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%); border:none;">
                                Update Password
                            </button>
                            <a href="<?= defined('BASE_URL') ? BASE_URL . '/dashboard' : '/dashboard'; ?>" class="btn btn-link text-decoration-none text-muted">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
