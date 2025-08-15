<?php
// Uses layout background gradient when not authenticated
?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-5">
      <div class="p-4 p-md-5 auth-card card-soft">
        <div class="text-center mb-4">
          <div class="display-6 mb-1"><i class="bi bi-shield-lock"></i></div>
          <h1 class="h4 mb-0">Welcome to Shiori</h1>
          <small class="text-muted">Sign in to continue</small>
        </div>
        <form method="POST" action="<?= $baseUrl; ?>/login" autocomplete="off">
          <?= CSRF::field(); ?>
          <div class="mb-3">
            <label class="form-label">Username</label>
            <input name="username" type="text" class="form-control form-control-lg" required autofocus>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <div class="input-group input-group-lg">
              <input id="password" name="password" type="password" class="form-control" required>
              <button class="btn btn-outline-secondary" type="button" id="togglePwd">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>
          <div class="d-grid">
            <button class="btn btn-primary btn-lg" type="submit">
              <i class="bi bi-box-arrow-in-right me-1"></i> Login
            </button>
          </div>
        </form>
      </div>
      <p class="text-center text-white-50 small mt-3">© <?= date('Y'); ?> Shiori</p>
    </div>
  </div>
</div>

<script>
  const btn = document.getElementById('togglePwd');
  const pwd = document.getElementById('password');
  btn?.addEventListener('click', () => {
    if (pwd.type === 'password') {
      pwd.type = 'text';
      btn.innerHTML = '<i class="bi bi-eye-slash"></i>';
    } else {
      pwd.type = 'password';
      btn.innerHTML = '<i class="bi bi-eye"></i>';
    }
  });
</script>
