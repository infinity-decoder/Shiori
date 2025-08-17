<?php
// $students, $page, $per_page, $total are provided
$totalPages = (int)ceil(($total ?: 0) / max(1, $per_page));
?>
<div class="container">
  <div class="row mb-3">
    <div class="col">
      <h1 class="h4 mb-0">Students</h1>
      <p class="text-muted">Manage student records (add, view, edit, delete). <small class="text-muted ms-2">Tip: press <kbd>/</kbd> to open search.</small></p>
    </div>
    <div class="col-auto d-flex align-items-center gap-2">
      <a href="<?= $baseUrl; ?>/dashboard" class="btn btn-outline-secondary">Dashboard</a>
      <a href="<?= $baseUrl; ?>/students/create" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Student</a>
      <a href="#" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#searchModal"><i class="bi bi-search"></i> Search</a>
      <a href="<?= $baseUrl; ?>/students/export?all=1" class="btn btn-outline-success"><i class="bi bi-file-earmark-arrow-down"></i> Export CSV</a>
    </div>
  </div>

  <div class="card card-soft">
    <div class="card-body p-2">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
          <form method="GET" action="<?= $baseUrl; ?>/students" class="d-flex align-items-center gap-2 mb-0">
            <label class="small text-muted mb-0">Show</label>
            <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()" style="width:90px;">
              <option value="10" <?= $per_page == 10 ? 'selected' : ''; ?>>10</option>
              <option value="25" <?= $per_page == 25 ? 'selected' : ''; ?>>25</option>
              <option value="50" <?= $per_page == 50 ? 'selected' : ''; ?>>50</option>
            </select>
            <input type="hidden" name="page" value="1">
          </form>
        </div>

        <div class="small text-muted">
          Showing page <?= $page; ?> of <?= max(1, $totalPages); ?> — total <?= $total; ?> records
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-striped mb-0">
          <thead class="table-light">
            <tr>
              <th>Photo</th>
              <th>Roll</th>
              <th>Enrollment</th>
              <th>Name</th>
              <th>Father Name</th>
              <th>Mobile Number</th>
              <th>Class</th>
              <th>Section</th>
              <th style="width:180px">Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($students)): ?>
            <tr><td colspan="9" class="text-center py-4">No students yet.</td></tr>
          <?php else: ?>
            <?php foreach ($students as $s): ?>
            <tr>
              <td style="width:80px">
                <?php if (!empty($s['photo_path'])): ?>
                  <img src="<?= $baseUrl; ?>/uploads/students/<?= rawurlencode($s['photo_path']); ?>" alt="photo" style="height:56px; width:auto; border-radius:4px;">
                <?php else: ?>
                  <div class="bg-light text-muted d-inline-flex align-items-center justify-content-center" style="height:56px; width:56px; border-radius:4px;">
                    <i class="bi bi-person"></i>
                  </div>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($s['roll_no']); ?></td>
              <td><?= htmlspecialchars($s['enrollment_no']); ?></td>
              <td><?= htmlspecialchars($s['student_name']); ?></td>
              <td><?= htmlspecialchars($s['father_name'] ?? ''); ?></td>
              <td><?= htmlspecialchars($s['mobile'] ?? ''); ?></td>
              <td><?= htmlspecialchars($s['class_name'] ?? $s['class_id']); ?></td>
              <td><?= htmlspecialchars($s['section_name'] ?? $s['section_id']); ?></td>
              <td>
                <a href="<?= $baseUrl; ?>/students/show?id=<?= $s['id']; ?>" class="btn btn-sm btn-outline-primary me-1">
                  <i class="bi bi-eye"></i>
                </a>
                <a href="<?= $baseUrl; ?>/students/edit?id=<?= $s['id']; ?>" class="btn btn-sm btn-outline-secondary me-1">
                  <i class="bi bi-pencil"></i>
                </a>

                <form method="POST" action="<?= $baseUrl; ?>/students/delete?id=<?= $s['id']; ?>" class="d-inline-block delete-form" style="margin:0;">
                  <?= CSRF::field(); ?>
                  <button type="button" class="btn btn-sm btn-outline-danger btn-delete"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination (unchanged from previous) -->
      <nav class="mt-3" aria-label="Student pagination">
        <ul class="pagination mb-0">
          <?php
            $start = max(1, $page - 3);
            $end = min(max(1, $totalPages), $page + 3);
            if ($page > 1):
          ?>
            <li class="page-item"><a class="page-link" href="<?= $baseUrl; ?>/students?page=<?= $page-1; ?>&per_page=<?= $per_page; ?>">« Prev</a></li>
          <?php endif; ?>

          <?php for ($p = $start; $p <= $end; $p++): ?>
            <li class="page-item <?= $p === $page ? 'active' : ''; ?>">
              <a class="page-link" href="<?= $baseUrl; ?>/students?page=<?= $p; ?>&per_page=<?= $per_page; ?>"><?= $p; ?></a>
            </li>
          <?php endfor; ?>

          <?php if ($page < $totalPages): ?>
            <li class="page-item"><a class="page-link" href="<?= $baseUrl; ?>/students?page=<?= $page+1; ?>&per_page=<?= $per_page; ?>">Next »</a></li>
          <?php endif; ?>
        </ul>
      </nav>

    </div>
  </div>
</div>

<!-- include search modal markup -->
<?php require BASE_PATH . '/app/Views/students/search_modal.php'; ?>

<script>
// delete handler (uses SweetAlert)
document.querySelectorAll('.btn-delete').forEach(btn => {
  btn.addEventListener('click', function (e) {
    e.preventDefault();
    const form = this.closest('form');
    Swal.fire({
      title: 'Delete record?',
      text: 'This action cannot be undone.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete',
      cancelButtonText: 'Cancel',
    }).then(result => {
      if (result.isConfirmed) {
        form.submit();
      }
    });
  });
});
</script>
