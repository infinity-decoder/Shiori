
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1>Settings</h1>
            <p class="text-muted">Manage application settings and recovery options.</p>
        </div>
    </div>

    <ul class="nav nav-tabs mb-4" id="settingsTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button" role="tab">Recovery Setup</button>
        </li>
        <!-- Add more tabs for User Management or Logs here if meant to be inline, 
             but currently Users and Activity have their own pages/routes managed via menu. 
             If the user wants them here, we'd include them. 
             For now, sticking to what is working: Recovery. -->
    </ul>

    <div class="tab-content" id="settingsTabContent">
        
        <!-- Email Tab -->
        <div class="tab-pane fade show active" id="email" role="tabpanel">
            <div class="row">
                <div class="col-md-8 col-lg-6">
                    <div class="card">
                        <div class="card-header">SMTP Configuration</div>
                        <div class="card-body">
                            <form method="POST" action="<?= BASE_URL ?>/settings/email">
                                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                                
                                <div class="row mb-3">
                                    <div class="col-md-8">
                                        <label class="form-label">SMTP Host</label>
                                        <input type="text" name="smtp_host" class="form-control" value="<?= htmlspecialchars($email['smtp_host'] ?? '') ?>" placeholder="e.g. smtp.gmail.com">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Port</label>
                                        <input type="number" name="smtp_port" class="form-control" value="<?= htmlspecialchars($email['smtp_port'] ?? '587') ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">SMTP Username / Email</label>
                                    <input type="text" name="smtp_user" class="form-control" value="<?= htmlspecialchars($email['smtp_user'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">SMTP Password</label>
                                    <input type="password" name="smtp_pass" class="form-control" value="<?= htmlspecialchars($email['smtp_pass'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">From Name</label>
                                    <input type="text" name="from_name" class="form-control" value="<?= htmlspecialchars($email['mail_from_name'] ?? 'Shiori Admin') ?>">
                                </div>
                                
                                <button class="btn btn-primary">Save Settings</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
