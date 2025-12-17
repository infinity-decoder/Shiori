<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Shiori - Review</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .install-card { max-width: 600px; margin: 50px auto; border: none; box-shadow: 0 0 20px rgba(0,0,0,0.05); }
        .brand-logo { font-size: 2rem; font-weight: bold; color: #4e73df; text-align: center; margin-bottom: 20px; }
        .step-indicator { display: flex; justify-content: space-between; margin-bottom: 2rem; }
        .step { text-align: center; opacity: 0.5; font-size: 0.9rem; }
        .step.active { opacity: 1; font-weight: bold; color: #4e73df; }
    </style>
</head>
<body>

<div class="container">
    <div class="brand-logo mt-5">Shiori Installer</div>
    
    <div class="card install-card">
        <div class="card-body p-5">
            <div class="step-indicator">
                <div class="step">1. Database</div>
                <div class="step">2. Super Admin</div>
                <div class="step active">3. Finish</div>
            </div>

            <h4 class="card-title mb-4">Ready to Install?</h4>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="list-group mb-4">
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Database</strong><br>
                        <small class="text-muted"><?= htmlspecialchars($_SESSION['install_db']['user'] . '@' . $_SESSION['install_db']['host'] . ' / ' . $_SESSION['install_db']['name']) ?></small>
                    </div>
                    <span class="badge bg-success rounded-pill">Verified</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Super Admin</strong><br>
                        <small class="text-muted"><?= htmlspecialchars($_SESSION['install_admin']['user'] . ' (' . $_SESSION['install_admin']['email'] . ')') ?></small>
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
</div>

</body>
</html>
