<?php View::partial('layouts/main.php', ['title' => $title]); ?>

<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-2">Import Students from CSV</h1>
            <p class="text-muted mb-0">Upload a CSV file to bulk import student records. Download the template below to ensure the correct format.</p>
        </div>
        <div class="col-auto">
            <a href="<?= $baseUrl ?>/students" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Students
            </a>
        </div>
    </div>

    <?php if ($msg = Auth::getFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($msg = Auth::getFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= htmlspecialchars($msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- CSV Template Download - Prominent -->
    <div class="card card-soft mb-4" style="border-left: 4px solid #0ea5e9;">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-1"><i class="bi bi-file-earmark-spreadsheet me-2 text-primary"></i>CSV Template</h5>
                    <p class="text-muted mb-0 small">Download the template file with the correct column structure. populate your data and upload it below.</p>
                </div>
                <div class="col-auto">
                    <a href="<?= $templateUrl ?>" class="btn btn-primary btn-lg" download>
                        <i class="bi bi-download me-2"></i> Download Template
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Upload Card -->
    <div class="card card-soft shadow-sm">
        <div class="card-body p-4">
            <!-- Import Method Selection -->
            <div class="mb-4">
                <label class="form-label fw-bold text-secondary small text-uppercase">Import Method</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="import_method" id="method_file" value="file" autocomplete="off" checked>
                    <label class="btn btn-outline-primary btn-lg" for="method_file">
                        <i class="bi bi-cloud-upload me-2"></i>Upload File
                    </label>
                    
                    <input type="radio" class="btn-check" name="import_method" id="method_url" value="url" autocomplete="off">
                    <label class="btn btn-outline-primary btn-lg" for="method_url">
                        <i class="bi bi-link-45deg me-2"></i>Import from URL
                    </label>
                </div>
            </div>

            <!-- File Upload Form -->
            <div id="file_import_form">
                <form action="<?= $baseUrl ?>/students/import-process" method="POST" enctype="multipart/form-data" id="uploadForm">
                    <?= CSRF::field(); ?>
                    
                    <!-- Modern Drag & Drop Upload Zone -->
                    <div class="upload-zone border-3 border-dashed rounded-3 p-5 text-center mb-3" 
                         id="dropZone"
                         style="border-color: #cbd5e1; background: #f8fafc; transition: all 0.3s ease;">
                        <input type="file" name="csv_file" id="csvFileInput" class="d-none" accept=".csv" required>
                        
                        <div id="uploadPrompt">
                            <i class="bi bi-cloud-arrow-up display-1 text-muted mb-3"></i>
                            <h4 class="mb-2">Drag & Drop your CSV file here</h4>
                            <p class="text-muted mb-3">or</p>
                            <button type="button" class="btn btn-outline-primary btn-lg" onclick="document.getElementById('csvFileInput').click()">
                                <i class="bi bi-folder2-open me-2"></i> Browse Files
                            </button>
                            <div class="small text-muted mt-3">
                                <strong>Accepted:</strong> .csv files only | <strong>Max size:</strong> 5MB
                            </div>
                        </div>
                        
                        <div id="fileSelected" style="display:none;">
                            <i class="bi bi-file-earmark-check display-1 text-success mb-3"></i>
                            <h5 class="text-success mb-2">File Ready for Import</h5>
                            <p class="mb-3"><strong id="fileName"></strong></p>
                            <button type="button" class="btn btn-outline-secondary me-2" onclick="clearFile()">
                                <i class="bi bi-x-circle me-1"></i> Remove
                            </button>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-upload me-2"></i> Import Now
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- URL Import Form -->
            <div id="url_import_form" style="display:none;">
                <form action="<?= $baseUrl ?>/students/import-url" method="POST">
                    <?= CSRF::field(); ?>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">CSV File URL</label>
                        <input type="url" name="csv_url" class="form-control form-control-lg" placeholder="https://example.com/students.csv" required>
                        <div class="form-text">
                            <i class="bi bi-info-circle me-1"></i>
                            Enter the direct URL to a publicly accessible CSV file.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-cloud-download me-2"></i>Import from URL
                    </button>
                </form>
            </div>

            <!-- Format Requirements Info -->
            <div class="mt-4 p-4 rounded-3" style="background: #f0f9ff; border: 1px solid #bae6fd;">
                <h6 class="mb-3"><i class="bi bi-info-circle-fill me-2 text-info"></i>CSV Format Requirements</h6>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="mb-0 small">
                            <li class="mb-1">First row must contain headers (will be skipped during import)</li>
                            <li class="mb-1">All columns from the template must be present in order</li>
                            <li class="mb-1">Class ID and Section ID must be valid IDs from your database</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="mb-0 small">
                            <li class="mb-1">CNIC and B-Form must be exactly 13 digits</li>
                            <li class="mb-1">Date format should be YYYY-MM-DD</li>
                            <li class="mb-1">Session format should be YYYY-YYYY (e.g., 2025-2026)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.upload-zone:hover {
    border-color: #0ea5e9 !important;
    background: #f0f9ff !important;
}
.upload-zone.drag-over {
    border-color: #0ea5e9 !important;
    background: #e0f2fe !important;
    transform: scale(1.02);
}
</style>

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

// Drag and drop functionality
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('csvFileInput');
const uploadPrompt = document.getElementById('uploadPrompt');
const fileSelected = document.getElementById('fileSelected');
const fileNameDisplay = document.getElementById('fileName');

// Prevent default drag behaviors
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, preventDefaults, false);
    document.body.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

// Highlight drop zone when item is dragged over it
['dragenter', 'dragover'].forEach(eventName => {
    dropZone.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, unhighlight, false);
});

function highlight(e) {
    dropZone.classList.add('drag-over');
}

function unhighlight(e) {
    dropZone.classList.remove('drag-over');
}

// Handle dropped files
dropZone.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    
    if (files.length > 0) {
        fileInput.files = files;
        displayFile(files[0]);
    }
}

// Handle file selection via browse button
fileInput.addEventListener('change', function() {
    if (this.files.length > 0) {
        displayFile(this.files[0]);
    }
});

function displayFile(file) {
    // Validate file type
    if (!file.name.endsWith('.csv')) {
        alert('Please select a CSV file');
        return;
    }
    
    // Validate file size (5MB)
    if (file.size > 5 * 1024 * 1024) {
        alert('File size must be less than 5MB');
        return;
    }
    
    fileNameDisplay.textContent = file.name;
    uploadPrompt.style.display = 'none';
    fileSelected.style.display = 'block';
}

function clearFile() {
    fileInput.value = '';
    uploadPrompt.style.display = 'block';
    fileSelected.style.display = 'none';
}
</script>

<?php View::partial('layouts/footer.php'); ?>

