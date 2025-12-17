<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Shiori - Step 1/4</title>
    <!-- Local Assets for Offline Support -->
    <link href="../assets/installer/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/installer/css/style.css" rel="stylesheet">
</head>
<body>

<div class="container">
    <div class="brand-logo mt-4">
        <img src="../assets/installer/img/logo.svg" alt="Shiori Logo">
        <div>Shiori Installer</div>
    </div>
    
    <div class="card install-card mx-auto" style="max-width: 600px;">
        <div class="card-body p-5">
            <div class="step-indicator">
                <div class="step active">1. Database</div>
                <div class="step">2. Super Admin</div>
                <div class="step">3. Finish</div>
            </div>

            <h4 class="card-title">Database Configuration</h4>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="?step=1">
                <div class="row">
                    <div class="col-md-9 mb-3">
                        <label class="form-label">Host</label>
                        <input type="text" name="host" class="form-control" value="<?= htmlspecialchars($_SESSION['install_db']['host'] ?? 'localhost') ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Port</label>
                        <input type="number" name="port" class="form-control" value="<?= htmlspecialchars($_SESSION['install_db']['port'] ?? '3306') ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Database Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($_SESSION['install_db']['name'] ?? 'shiori_db') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="user" class="form-control" value="<?= htmlspecialchars($_SESSION['install_db']['user'] ?? 'root') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="pass" class="form-control" placeholder="Leave empty if none" value="<?= htmlspecialchars($_SESSION['install_db']['pass'] ?? '') ?>">
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary px-4">Next: Admin Setup &rarr;</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="text-center mt-3 text-muted" style="font-size: 0.8rem; opacity: 0.7;">
        &copy; <?= date('Y') ?> Shiori SIS
    </div>
</div>

<script src="../assets/installer/js/bootstrap.bundle.min.js"></script>
</body>
</html>
