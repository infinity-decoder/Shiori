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
    :root { --card-radius: 1rem; }
    body.bg-gradient { background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%); min-height:100vh; color:#111827; }
    .auth-card { backdrop-filter: blur(6px); background: rgba(255,255,255,0.95); border-radius: var(--card-radius); }
    .card-soft { border-radius: var(--card-radius); box-shadow: 0 10px 25px rgba(0,0,0,0.06); }
    .stat-number { font-size: 1.6rem; font-weight: 700; }
    pre { white-space: pre-wrap; word-break: break-word; }
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
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
  <div class="container">
    <a class="navbar-brand" href="<?= $baseUrl; ?>/dashboard">
      <i class="bi bi-journal-bookmark-fill text-primary"></i>
      <span class="ms-1"><?= htmlspecialchars($appCfg['name'], ENT_QUOTES, 'UTF-8'); ?></span>
    </a>

    <div class="ms-auto d-flex align-items-center">
      <div class="me-3 text-muted small">Signed in as <strong><?= htmlspecialchars(Auth::user()['username']); ?></strong></div>
      <form method="POST" action="<?= $baseUrl; ?>/logout" class="mb-0">
        <?= CSRF::field(); ?>
        <button class="btn btn-outline-danger btn-sm" type="submit">
          <i class="bi bi-box-arrow-right me-1"></i> Logout
        </button>
      </form>
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
