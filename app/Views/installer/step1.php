<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Shiori - Step 1/4</title>
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
                <div class="step active">1. Database</div>
                <div class="step">2. Super Admin</div>
                <div class="step">3. Finish</div>
            </div>

            <h4 class="card-title mb-4">Database Configuration</h4>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="?step=1">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Host</label>
                        <input type="text" name="host" class="form-control" value="<?= htmlspecialchars($_SESSION['install_db']['host'] ?? 'localhost') ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
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
</div>

</body>
</html>
