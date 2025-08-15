<?php
// $students passed from controller
?>
<div class="container">
  <div class="row mb-3">
    <div class="col">
      <h1 class="h4 mb-0">Students</h1>
      <p class="text-muted">Manage student records (add, view, edit, delete).</p>
    </div>
    <div class="col-auto">
      <a href="<?= $baseUrl; ?>/students/create" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add Student
      </a>
    </div>
  </div>

  <div class="card card-soft">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped mb-0">
          <thead class="table-light">
            <tr>
              <th style="width:60px">ID</th>
              <th>Photo</th>
              <th>Roll</th>
              <th>Enrollment</th>
              <th>Name</th>
              <th>Class</th>
              <th>Section</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($students)): ?>
            <tr><td colspan="8" class="text-center py-4">No students yet.</td></tr>
          <?php else: ?>
            <?php foreach ($students as $s): ?>
            <tr>
              <td><?= htmlspecialchars($s['id']); ?></td>
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
              <td><?= htmlspecialchars($s['class_name'] ?? $s['class_id']); ?></td>
              <td><?= htmlspecialchars($s['section_name'] ?? $s['section_id']); ?></td>
              <td style="width:180px">
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
    </div>
  </div>
</div>

<script>
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
