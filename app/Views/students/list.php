<?php
// app/Views/students/list.php
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
      <?php if (Auth::isAdmin()): ?>
      <a href="<?= $baseUrl; ?>/students/export?all=1" class="btn btn-outline-success"><i class="bi bi-file-earmark-arrow-down"></i> Export CSV</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="card card-soft">
    <div class="card-body p-2">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
                    <form method="GET" action="<?= $baseUrl; ?>/students" class="row gx-2 gy-2 align-items-center mb-0">
            <div class="col-auto">
              <select name="class_id" class="form-select form-select-sm">
                <option value="">All Classes</option>
                <?php foreach (($classes ?? []) as $c): ?>
                  <option value="<?= $c['id']; ?>" <?= (isset($filters['class_id']) && $filters['class_id'] == $c['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($c['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-auto">
              <select name="section_id" class="form-select form-select-sm">
                <option value="">All Sections</option>
                <?php foreach (($sections ?? []) as $sct): ?>
                  <option value="<?= $sct['id']; ?>" <?= (isset($filters['section_id']) && $filters['section_id'] == $sct['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($sct['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-auto">
              <input type="text" name="q" value="<?= htmlspecialchars($filters['q'] ?? ''); ?>" class="form-control form-control-sm" placeholder="Search name, father, roll">
            </div>

            <div class="col-auto">
              <select name="sort" class="form-select form-select-sm">
                <option value="id_desc" <?= ($sort ?? '') === 'id_desc' ? 'selected' : ''; ?>>Newest</option>
                <option value="id_asc" <?= ($sort ?? '') === 'id_asc' ? 'selected' : ''; ?>>Oldest</option>
                <option value="name_asc" <?= ($sort ?? '') === 'name_asc' ? 'selected' : ''; ?>>Name A→Z</option>
                <option value="name_desc" <?= ($sort ?? '') === 'name_desc' ? 'selected' : ''; ?>>Name Z→A</option>
                <option value="roll_asc" <?= ($sort ?? '') === 'roll_asc' ? 'selected' : ''; ?>>Roll Asc</option>
                <option value="roll_desc" <?= ($sort ?? '') === 'roll_desc' ? 'selected' : ''; ?>>Roll Desc</option>
              </select>
            </div>

            <div class="col-auto">
              <select name="per_page" class="form-select form-select-sm">
                <option value="10" <?= $per_page == 10 ? 'selected' : ''; ?>>10</option>
                <option value="25" <?= $per_page == 25 ? 'selected' : ''; ?>>25</option>
                <option value="50" <?= $per_page == 50 ? 'selected' : ''; ?>>50</option>
              </select>
            </div>

            <div class="col-auto">
              <button type="submit" class="btn btn-sm btn-primary">Filter</button>
              <a href="<?= $baseUrl; ?>/students" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
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
              <th>B.Form</th>
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
                <img src="<?= getStudentImageUrl($s, $baseUrl) ?>" 
                     alt="<?= htmlspecialchars($s['student_name']) ?>" 
                     style="height:56px; width:56px; object-fit:cover; border-radius:4px;"
                     loading="lazy">
              </td>

              <td><?= htmlspecialchars($s['roll_no']); ?></td>
              <td><?= htmlspecialchars($s['enrollment_no']); ?></td>
              <td><?= htmlspecialchars($s['student_name']); ?></td>
              <td><?= htmlspecialchars($s['father_name'] ?? ''); ?></td>
              <td><?= htmlspecialchars($s['b_form'] ?? ''); ?></td>
              <td><?= htmlspecialchars($s['mobile'] ?? ''); ?></td>
              <td><?= htmlspecialchars($s['class_name'] ?? $s['class_id']); ?></td>
              <td><?= htmlspecialchars($s['section_name'] ?? $s['section_id']); ?></td>

              <td>
                <a href="<?= $baseUrl; ?>/students/show?id=<?= $s['id']; ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-eye"></i></a>
                <a href="<?= $baseUrl; ?>/students/edit?id=<?= $s['id']; ?>" class="btn btn-sm btn-outline-secondary me-1"><i class="bi bi-pencil"></i></a>

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

      <!-- Pagination (unchanged) -->
      <nav class="mt-3" aria-label="Student pagination">
        <ul class="pagination mb-0">
          <?php
            $start = max(1, $page - 3);
            $end = min(max(1, $totalPages), $page + 3);

            // base query preserves filters and sort for pagination links
            $baseQuery = [];
            if (!empty($filters['class_id'])) $baseQuery['class_id'] = $filters['class_id'];
            if (!empty($filters['section_id'])) $baseQuery['section_id'] = $filters['section_id'];
            if (!empty($filters['q'])) $baseQuery['q'] = $filters['q'];
            if (!empty($sort)) $baseQuery['sort'] = $sort;
            $baseQuery['per_page'] = $per_page;
          ?>

          <?php if ($page > 1): ?>
            <li class="page-item"><a class="page-link" href="<?= $baseUrl; ?>/students?<?= http_build_query(array_merge($baseQuery, ['page' => $page-1])); ?>">« Prev</a></li>
          <?php endif; ?>

          <?php for ($p = $start; $p <= $end; $p++): ?>
            <li class="page-item <?= $p === $page ? 'active' : ''; ?>">
              <a class="page-link" href="<?= $baseUrl; ?>/students?<?= http_build_query(array_merge($baseQuery, ['page' => $p])); ?>"><?= $p; ?></a>
            </li>
          <?php endfor; ?>

          <?php if ($page < $totalPages): ?>
            <li class="page-item"><a class="page-link" href="<?= $baseUrl; ?>/students?<?= http_build_query(array_merge($baseQuery, ['page' => $page+1])); ?>">Next »</a></li>
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
