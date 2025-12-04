<?php
// app/Views/students/view.php
// $student is provided by controller; $baseUrl is available from layout
?>
<div class="container">
  <div class="row mb-3 align-items-center">
    <div class="col">
      <h1 class="h4 mb-0">View Student</h1>
      <p class="text-muted">Profile for student #<?= htmlspecialchars($student['id'] ?? ''); ?></p>
    </div>
    <div class="col-auto d-flex gap-2">
      <a href="<?= $baseUrl; ?>/students" class="btn btn-outline-secondary">Back to list</a>
      <a href="<?= $baseUrl; ?>/students/print?id=<?= (int)($student['id'] ?? 0); ?>" class="btn btn-outline-secondary">Print</a>
      <a href="<?= $baseUrl; ?>/students/edit?id=<?= (int)($student['id'] ?? 0); ?>" class="btn btn-primary">Edit</a>
    </div>
  </div>

  <div class="card card-soft">
    <div class="card-body">
      <div class="row">
        <div class="col-md-3">
          <div class="mb-3">
                  <?php
                  $thumbUrl = $baseUrl . '/students/thumbnail?id=' . (int)$student['id'];
                  // Check if we have a photo path to decide whether to show placeholder or image
                  // Since thumbnail route handles fallback, we can just try to load it. 
                  // But to be cleaner, let's check if photo_path is set.
                  if (!empty($student['photo_path'])): ?>
                      <img src="<?= $thumbUrl; ?>" alt="photo" class="img-fluid rounded" style="width:100%; height:auto;"
                           onerror="this.onerror=null;this.src='data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22200%22%20height%3D%22200%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%20200%20200%22%20preserveAspectRatio%3D%22none%22%3E%3Crect%20width%3D%22200%22%20height%3D%22200%22%20fill%3D%22%23eeeeee%22%3E%3C%2Frect%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2250%25%22%20dominant-baseline%3D%22middle%22%20text-anchor%3D%22middle%22%20fill%3D%22%23aaaaaa%22%3ENo%20Image%3C%2Ftext%3E%3C%2Fsvg%3E';">
                  <?php else: ?>
                      <div class="bg-light d-flex align-items-center justify-content-center" style="height:220px; border-radius:6px;">
                          <i class="bi bi-person fs-1 text-muted"></i>
                      </div>
                  <?php endif; ?>
            </div>
        </div>

        <div class="col-md-9">
          <table class="table table-borderless mb-0">
            <tr><th style="width:200px">ID</th><td><?= htmlspecialchars($student['id'] ?? ''); ?></td></tr>
            <tr><th>Roll No.</th><td><?= htmlspecialchars($student['roll_no'] ?? ''); ?></td></tr>
            <tr><th>Enrollment No.</th><td><?= htmlspecialchars($student['enrollment_no'] ?? ''); ?></td></tr>
            <tr><th>Session</th><td><?= htmlspecialchars($student['session'] ?? ''); ?></td></tr>
            <tr><th>Name</th><td><?= htmlspecialchars($student['student_name'] ?? ''); ?></td></tr>
            <tr><th>Class / Section</th><td><?= htmlspecialchars($student['class_name'] ?? $student['class_id'] ?? ''); ?> / <?= htmlspecialchars($student['section_name'] ?? $student['section_id'] ?? ''); ?></td></tr>
            <tr><th>Date of Birth</th><td><?= htmlspecialchars($student['dob'] ?? ''); ?></td></tr>
            <tr><th>B.form</th><td><?= htmlspecialchars($student['b_form'] ?? ''); ?></td></tr>

            <tr><th>Father Name</th><td><?= htmlspecialchars($student['father_name'] ?? ''); ?></td></tr>
            <tr><th>CNIC</th><td><?= htmlspecialchars($student['cnic'] ?? ''); ?></td></tr>
            <tr><th>Mobile</th><td><?= htmlspecialchars($student['mobile'] ?? ''); ?></td></tr>
            <tr><th>Father Occupation</th><td><?= htmlspecialchars($student['father_occupation'] ?? ''); ?></td></tr>
            <tr><th>BPS</th><td><?= htmlspecialchars($student['bps'] ?? ''); ?></td></tr>
            <tr><th>Category</th><td><?= htmlspecialchars($student['category_name'] ?? ''); ?></td></tr>
            <tr><th>Family Category</th><td><?= htmlspecialchars($student['fcategory_name'] ?? ''); ?></td></tr>
            <tr><th>Email</th><td><?= htmlspecialchars($student['email'] ?? ''); ?></td></tr>
              <!-- NEW: fields that were previously missing -->
            
            <tr><th>Religion</th><td><?= htmlspecialchars($student['religion'] ?? ''); ?></td></tr>
            <tr><th>Caste</th><td><?= htmlspecialchars($student['caste'] ?? ''); ?></td></tr>
            <tr><th>Domicile</th><td><?= htmlspecialchars($student['domicile'] ?? ''); ?></td></tr>
            <tr><th>Address</th><td><?= nl2br(htmlspecialchars($student['address'] ?? '')); ?></td></tr>
            <tr><th>Created</th><td><?= htmlspecialchars($student['created_at'] ?? ''); ?></td></tr>
            <tr><th>Updated</th><td><?= htmlspecialchars($student['updated_at'] ?? ''); ?></td></tr>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- include search modal (keeps behavior consistent with other pages) -->
<?php require BASE_PATH . '/app/Views/students/search_modal.php'; ?>
