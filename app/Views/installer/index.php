<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Shiori</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .install-card { max-width: 500px; margin: 50px auto; border: none; shadow: 0 0 20px rgba(0,0,0,0.1); }
        .brand-logo { font-size: 2rem; font-weight: bold; color: #4e73df; text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="container">
    <div class="brand-logo">Shiori Installer</div>
    
    <div class="card install-card shadow-sm">
        <div class="card-body p-4">
            <h4 class="card-title mb-4 text-center">Database Setup</h4>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Database Host</label>
                    <input type="text" name="host" class="form-control" value="localhost" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Database Port</label>
                    <input type="number" name="port" class="form-control" value="3306" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Database Name</label>
                    <input type="text" name="name" class="form-control" value="shiori_db" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Database User</label>
                    <input type="text" name="user" class="form-control" value="root" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Database Password</label>
                    <input type="password" name="pass" class="form-control" placeholder="Leave empty if none">
                </div>

                <hr class="my-4">
                <h5 class="mb-3">Admin Account</h5>

                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="admin_user" class="form-control" value="admin" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="admin_pass" class="form-control" required>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Install Shiori</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
