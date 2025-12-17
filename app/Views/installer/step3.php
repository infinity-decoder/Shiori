<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Shiori - Review</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/installer-style.css" rel="stylesheet">
</head>
<body>

<div class="container">
    <div class="brand-logo mt-4">
        <img src="../assets/images/logo.svg" alt="Shiori Logo">
        <div>Shiori Installer</div>
    </div>
    
    <div class="card install-card mx-auto" style="max-width: 600px;">
        <div class="card-body p-5">
            <div class="step-indicator">
                <div class="step">1. Database</div>
                <div class="step">2. Super Admin</div>
                <div class="step active">3. Finish</div>
            </div>

            <h4 class="card-title">Ready to Install?</h4>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="list-group mb-4">
                <div class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #4a5568; color: inherit;">
                    <div>
                        <strong>Database</strong><br>
                        <small class="text-muted" style="color: #a0aec0 !important;"><?= htmlspecialchars($_SESSION['install_db']['user'] . '@' . $_SESSION['install_db']['host'] . ' / ' . $_SESSION['install_db']['name']) ?></small>
                    </div>
                    <span class="badge bg-success rounded-pill">Verified</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #4a5568; color: inherit;">
                    <div>
                        <strong>Super Admin</strong><br>
                        <small class="text-muted" style="color: #a0aec0 !important;"><?= htmlspecialchars($_SESSION['install_admin']['user'] . ' (' . $_SESSION['install_admin']['email'] . ')') ?></small>
                    </div>
                </div>
            </div>
            
            <p class="text-center text-muted small">Email settings can be configured in the dashboard after installation.</p>

            <form method="POST" action="?step=3">
                <input type="hidden" name="confirm" value="1">
                <div class="d-flex justify-content-between mt-4">
                    <button type="button" onclick="window.location.href='?step=2'" class="btn btn-outline-secondary">&larr; Back</button>
                    <button type="submit" class="btn btn-success px-5 btn-lg">Install Now</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="text-center mt-3 text-muted" style="font-size: 0.8rem; opacity: 0.7;">
        &copy; <?= date('Y') ?> Shiori SIS
    </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
