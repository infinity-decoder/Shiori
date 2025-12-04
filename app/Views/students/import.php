<?php View::partial('layouts/main.php', ['title' => $title]); ?>

<div class="container">
    <div class="row mb-3">
        <div class="col">
            <h1>Import Students</h1>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_URL ?>/students" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if ($msg = Auth::getFlash('error')): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($msg) ?></div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/students/import" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                
                <div class="mb-3">
                    <label class="form-label">CSV File</label>
                    <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                    <div class="form-text">
                        Format: Roll No, Enrollment No, Student Name, Father Name, Class ID, Section ID
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Import</button>
            </form>
        </div>
    </div>
</div>

<?php View::partial('layouts/footer.php'); ?>
