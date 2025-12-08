<?php View::partial('layouts/main.php', ['title' => $title]); ?>

<div class="container">
    <div class="row mb-3">
        <div class="col">
            <h1>Import Students</h1>
            <p class="text-muted">Upload a CSV file or import from a URL to bulk add students.</p>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_URL ?>/students" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>

    <div class="card card-soft">
        <div class="card-body">
            <?php if ($msg = Auth::getFlash('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($msg = Auth::getFlash('success')): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Import Method Selection -->
            <div class="mb-4">
                <label class="form-label fw-bold">Import Method</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="import_method" id="method_file" value="file" autocomplete="off" checked>
                    <label class="btn btn-outline-primary" for="method_file">
                        <i class="bi bi-cloud-upload me-2"></i>Upload File
                    </label>
                    
                    <input type="radio" class="btn-check" name="import_method" id="method_url" value="url" autocomplete="off">
                    <label class="btn btn-outline-primary" for="method_url">
                        <i class="bi bi-link-45deg me-2"></i>Import from URL
                    </label>
                </div>
            </div>

            <!-- File Upload Form -->
            <div id="file_import_form">
                <form action="<?= BASE_URL ?>/students/import-process" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">CSV File</label>
                        <div class="input-group">
                            <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                            <?php if (!empty($templateUrl)): ?>
                                <a href="<?= $templateUrl ?>" class="btn btn-outline-secondary" title="Download Template">
                                    <i class="bi bi-download"></i> Template
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="form-text">
                            <strong>Expected format:</strong> Roll No, Enrollment No, Student Name, Father Name, Class ID, Section ID<br>
                            <a href="<?= $templateUrl ?>">Download template</a> for correct format.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-2"></i>Import from File
                    </button>
                </form>
            </div>

            <!-- URL Import Form -->
            <div id="url_import_form" style="display:none;">
                <form action="<?= BASE_URL ?>/students/import-url" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">CSV URL</label>
                        <input type="url" name="csv_url" class="form-control" placeholder="https://example.com/students.csv" required>
                        <div class="form-text">
                            Enter the direct URL to a CSV file. The file must be publicly accessible.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-cloud-download me-2"></i>Import from URL
                    </button>
                </form>
            </div>

            <div class="mt-4 p-3 bg-light border rounded">
                <h6 class="mb-2"><i class="bi bi-info-circle me-2"></i>CSV Format Requirements</h6>
                <ul class="mb-0 small">
                    <li>First row must be headers (will be skipped)</li>
                    <li>Minimum 6 columns required: Roll No, Enrollment, Name, Father Name, Class ID, Section ID</li>
                    <li>Class ID and Section ID must be valid IDs from your database</li>
                    <li>Session will be set to current year automatically</li>
                    <li>Maximum file size: 5MB (for URL imports)</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle between file and URL import forms
document.querySelectorAll('input[name="import_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const fileForm = document.getElementById('file_import_form');
        const urlForm = document.getElementById('url_import_form');
        
        if (this.value === 'file') {
            fileForm.style.display = 'block';
            urlForm.style.display = 'none';
        } else {
            fileForm.style.display = 'none';
            urlForm.style.display = 'block';
        }
    });
});
</script>

<?php View::partial('layouts/footer.php'); ?>
