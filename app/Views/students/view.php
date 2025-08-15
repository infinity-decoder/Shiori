<?php
// $student passed from controller
?>
<div class="container">
  <div class="row mb-3">
    <div class="col">
      <h1 class="h4 mb-0">Student Details</h1>
      <p class="text-muted">Read-only view.</p>
    </div>
    <div class="col-auto">
      <a href="<?= $baseUrl; ?>/students" class="btn btn-outline-secondary">Back to list</a>
      <a href="<?= $baseUrl; ?>/students/edit?id=<?= $student['id']; ?>" class="btn btn-secondary ms-2">Edit</a>
    </div>
  </div>

  <div class="card card-soft">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-3">
          <?php if (!empty($student['photo_path'])): ?>
            <img src="<?= $baseUrl; ?>/uploads/students/<?= rawurlencode($student['photo_path']); ?>" alt="photo" style="width:100%; border-radius:6px;">
          <?php else: ?>
            <div class="bg-light d-flex align-items-center justify-content-center" style="height:220px; border-radius:6px;">
              <i class="bi bi-person fs-1 text-muted"></i>
            </div>
          <?php endif; ?>
        </div>

        <div class="col-md-9">
          <table class="table table-borderless mb-0">
            <tr><th style="width:200px">ID</th><td><?= htmlspecialchars($student['id']); ?></td></tr>
            <tr><th>Roll No.</th><td><?= htmlspecialchars($student['roll_no']); ?></td></tr>
            <tr><th>Enrollment No.</th><td><?= htmlspecialchars($student['enrollment_no']); ?></td></tr>
            <tr><th>Name</th><td><?= htmlspecialchars($student['student_name']); ?></td></tr>
            <tr><th>Class / Section</th><td><?= htmlspecialchars($student['class_name'] ?? $student['class_id']); ?> / <?= htmlspecialchars($student['section_name'] ?? $student['section_id']); ?></td></tr>
            <tr><th>Date of Birth</th><td><?= htmlspecialchars($student['dob'] ?? ''); ?></td></tr>
            <tr><th>B.form</th><td><?= htmlspecialchars($student['b_form'] ?? ''); ?></td></tr>
            <tr><th>Father Name</th><td><?= htmlspecialchars($student['father_name']); ?></td></tr>
            <tr><th>CNIC</th><td><?= htmlspecialchars($student['cnic'] ?? ''); ?></td></tr>
            <tr><th>Mobile</th><td><?= htmlspecialchars($student['mobile'] ?? ''); ?></td></tr>
            <tr><th>Father Occupation</th><td><?= htmlspecialchars($student['father_occupation'] ?? ''); ?></td></tr>
            <tr><th>Category</th><td><?= htmlspecialchars($student['category_name'] ?? ''); ?></td></tr>
            <tr><th>Family Category</th><td><?= htmlspecialchars($student['fcategory_name'] ?? ''); ?></td></tr>
            <tr><th>Email</th><td><?= htmlspecialchars($student['email'] ?? ''); ?></td></tr>
            <tr><th>Address</th><td><?= nl2br(htmlspecialchars($student['address'] ?? '')); ?></td></tr>
            <tr><th>Created</th><td><?= htmlspecialchars($student['created_at'] ?? ''); ?></td></tr>
            <tr><th>Updated</th><td><?= htmlspecialchars($student['updated_at'] ?? ''); ?></td></tr>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
