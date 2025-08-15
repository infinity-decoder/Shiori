<?php
// $viewFile (path of the view) is made available by View::render
$flashError   = Auth::getFlash('error');
$flashSuccess = Auth::getFlash('success');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Shiori', ENT_QUOTES, 'UTF-8'); ?></title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body.bg-gradient {
            background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%);
            min-height: 100vh;
        }
        .auth-card {
            backdrop-filter: blur(8px);
            background: rgba(255,255,255,0.9);
            border-radius: 1.25rem;
        }
        .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.3px;
        }
        .card-soft {
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body class="<?= Auth::check() ? '' : 'bg-gradient'; ?>">

<?php if (Auth::check()): ?>
<nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom">
  <div class="container">
    <a class="navbar-brand" href="#"><?= htmlspecialchars((require BASE_PATH.'/config/app.php')['name'], ENT_QUOTES, 'UTF-8'); ?></a>
    <div class="d-flex align-items-center">
        <span class="me-3">Hello, <strong><?= htmlspecialchars(Auth::user()['username']); ?></strong></span>
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
    title: 'Oops…',
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
    timer: 1600,
    showConfirmButton: false
});
</script>
<?php endif; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
