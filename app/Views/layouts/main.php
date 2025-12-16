<?php
// $viewFile (path of the view) is made available by View::render
$flashError   = Auth::getFlash('error');
$flashSuccess = Auth::getFlash('success');
$appCfg       = require BASE_PATH . '/config/app.php';
$baseUrl      = rtrim($appCfg['base_url'], '/');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title ?? $appCfg['name'], ENT_QUOTES, 'UTF-8'); ?></title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Flatpickr (date picker) -->
  <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

  <!-- FilePond (file uploads) -->
  <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet">

  <style>
    :root { 
      --card-radius: 1rem; 
      --primary-gradient: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%);
    }
    body.bg-gradient { 
      background: var(--primary-gradient); 
      min-height:100vh; 
      color:#111827; 
    }
    .auth-card { 
      backdrop-filter: blur(6px); 
      background: rgba(255,255,255,0.95); 
      border-radius: var(--card-radius); 
    }
    .card-soft { 
      border-radius: var(--card-radius); 
      box-shadow: 0 10px 25px rgba(0,0,0,0.06); 
      transition: all 0.3s ease;
    }
    .card-soft:hover {
      box-shadow: 0 15px 35px rgba(0,0,0,0.1);
      transform: translateY(-2px);
    }
    .stat-number { 
      font-size: 1.8rem; 
      font-weight: 700; 
    }
    .navbar-brand { 
      display: flex; 
      align-items: center; 
      gap: 0.5rem;
      font-weight: 600;
      font-size: 1.1rem;
    }
    .navbar-brand img {
      height: 36px;
      width: 36px;
      object-fit: contain;
    }
    pre { 
      white-space: pre-wrap; 
      word-break: break-word; 
    }
    /* Logout button active override (remove blue) */
    .dropdown-item.text-danger:active {
      background-color: #fee2e2;
      color: #dc3545;
    }
  </style>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

  <!-- Flatpickr JS -->
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

  <!-- FilePond JS -->
  <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>

</head>
<body class="<?= Auth::check() ? '' : 'bg-gradient'; ?>">

<?php if (Auth::check()): ?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="<?= $baseUrl; ?>/dashboard">
      <i class="bi bi-mortarboard-fill fs-4 me-2" style="color: #1E40AF;"></i>
      <span><?= htmlspecialchars($appCfg['name'], ENT_QUOTES, 'UTF-8'); ?></span>
    </a>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="<?= $baseUrl; ?>/dashboard">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= $baseUrl; ?>/students">Students</a>
        </li>
        <?php if (Auth::isAdmin()): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              Admin
            </a>
            <ul class="dropdown-menu" aria-labelledby="adminDropdown">
              <li><a class="dropdown-item" href="<?= $baseUrl; ?>/lookups">
                <i class="bi bi-gear me-2"></i>Manage Lookups
              </a></li>
              <?php if (Auth::isSuperAdmin()): ?>
              <li><a class="dropdown-item" href="<?= $baseUrl; ?>/users">
                <i class="bi bi-people me-2"></i>Manage Users
              </a></li>
              <li><a class="dropdown-item" href="<?= $baseUrl; ?>/settings">
                <i class="bi bi-sliders me-2"></i>Settings & Fields
              </a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?= $baseUrl; ?>/admin/backup">
                <i class="bi bi-download me-2"></i>Backup Database
              </a></li>
              <li>
                <form method="POST" action="<?= $baseUrl; ?>/admin/clear-logs" class="dropdown-item p-0" onsubmit="return confirm('Clear all activity logs older than 90 days?');">
                  <?= CSRF::field(); ?>
                  <button type="submit" class="btn btn-link text-decoration-none text-dark w-100 text-start p-2" style="background:none; border:none;">
                    <i class="bi bi-trash me-2"></i>Clear Old Logs
                  </button>
                </form>
              </li>
              <?php endif; ?>
              <li><a class="dropdown-item" href="<?= $baseUrl; ?>/activity">
                <i class="bi bi-clock-history me-2"></i>Activity Log
              </a></li>
            </ul>
          </li>
        <?php endif; ?>
      </ul>
    </div>

    <div class="ms-auto d-flex align-items-center">
      <div class="dropdown">
        <?php
            $u = Auth::user();
            $role = $u['role'] ?? 'viewer';
            $name = !empty($u['name']) ? $u['name'] : $u['username'];
            
            // Define colors
            $roleColors = [
                'super_admin' => '#b91c1c', // Dark Red
                'admin'       => '#15803d', // Green
                'staff'       => '#1d4ed8', // Blue
                'viewer'      => '#f7f306ff', // Dark Yellow/Orange (for visibility)
            ];
            $color = $roleColors[$role] ?? '#333';
        ?>
        <a href="#" class="btn btn-white shadow-sm rounded-pill d-flex align-items-center gap-2 px-3 py-2 text-decoration-none" 
           id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false" 
           style="transition: all 0.2s; border: 2px solid <?= $color; ?>; color: #333;">
            <div class="d-flex align-items-center justify-content-center rounded-circle" 
                 style="width: 24px; height: 24px; color: <?= $color; ?>; background-color: rgba(0,0,0,0.05);">
                <i class="bi bi-person-fill fs-6"></i>
            </div>
            <span class="fw-bold small" style="letter-spacing: 0.3px;">
                <?= htmlspecialchars($name); ?>
            </span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="profileDropdown" style="border-radius: 0.8rem; overflow: hidden;">
            <li><div class="dropdown-header text-muted small text-uppercase">Account</div></li>
            <li>
                <a class="dropdown-item py-2" href="<?= $baseUrl; ?>/profile/change-password">
                    <i class="bi bi-key me-2 text-primary"></i> Change Password
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <form method="POST" action="<?= $baseUrl; ?>/logout" class="m-0 p-0">
                    <?= CSRF::field(); ?>
                    <button class="dropdown-item py-2 text-danger" type="submit">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </button>
                </form>
            </li>
        </ul>
      </div>
    </div>
  </div>
</nav>
<?php endif; ?>

<main class="<?= Auth::check() ? 'py-4' : 'd-flex align-items-center'; ?>">
  <?php include $viewFile; ?>
</main>

<!-- Flash alerts -->
<?php if ($flashError): ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'Error',
    text: <?= json_encode($flashError); ?>,
    confirmButtonText: 'OK'
});
</script>
<?php endif; ?>

<?php if ($flashSuccess): ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Success',
    text: <?= json_encode($flashSuccess); ?>,
    timer: 1500,
    showConfirmButton: false
});
</script>
<?php endif; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
/**
 * Global helpers
 */

// Escape string for HTML insertion (if ever needed in JS)
function escapeHtml(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}

// Global keyboard shortcut: '/' opens search modal if present and not focused on input
document.addEventListener('keydown', function (e) {
  if (e.key === '/' && document.activeElement && ['INPUT', 'TEXTAREA'].indexOf(document.activeElement.tagName) === -1) {
    // find modal
    const modal = document.getElementById('searchModal');
    if (!modal) return;
    e.preventDefault();
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    setTimeout(() => {
      const input = modal.querySelector('#searchInput');
      input?.focus();
    }, 120);
  }
});
</script>
</body>
</html>
