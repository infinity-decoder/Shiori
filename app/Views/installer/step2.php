<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Shiori - Step 2/4</title>
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
                <div class="step">1. Database</div>
                <div class="step active">2. Super Admin</div>
                <div class="step">3. Finish</div>
            </div>

            <h4 class="card-title">Super Admin Configuration</h4>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="?step=2">
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="admin_name" class="form-control" value="<?= htmlspecialchars($_SESSION['install_admin']['name'] ?? 'Super Admin') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="admin_user" class="form-control" value="<?= htmlspecialchars($_SESSION['install_admin']['user'] ?? 'admin') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email Address (Essential for recovery)</label>
                    <input type="email" name="admin_email" class="form-control" value="<?= htmlspecialchars($_SESSION['install_admin']['email'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="admin_pass" class="form-control" required>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="button" onclick="window.location.href='?step=1'" class="btn btn-outline-secondary">&larr; Back</button>
                    <button type="submit" class="btn btn-primary px-4">Next: Review & Install &rarr;</button>
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
