<?php
// This file is included directly by StudentController::print()
// $student and $baseUrl are available
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Print Student #<?= htmlspecialchars($student['id']); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: Arial, Helvetica, sans-serif; color:#111; margin:20px; }
    .profile { display:flex; gap:20px; }
    .photo { width:220px; }
    .photo img { width:100%; height:auto; border-radius:6px; }
    .info { flex:1; }
    table { width:100%; border-collapse:collapse; margin-top:10px; }
    th { text-align:left; width:200px; padding:6px 0; color:#444; vertical-align:top; }
    td { padding:6px 0; }
    @media print {
      .no-print { display:none; }
      body { margin: 0; }
    }
  </style>
</head>
<body>
  <div class="no-print" style="margin-bottom:10px;">
    <button onclick="window.print()">Print</button>
  </div>

  <h2>Student Profile — #<?= htmlspecialchars($student['id']); ?></h2>

  <div class="profile">
    <div class="photo">
      <?php if (!empty($student['photo_path'])): ?>
        <img src="<?= $baseUrl; ?>/uploads/students/<?= rawurlencode($student['photo_path']); ?>" alt="photo">
      <?php else: ?>
        <div style="background:#f0f0f0;height:220px;display:flex;align-items:center;justify-content:center;border-radius:6px;color:#999;">No photo</div>
      <?php endif; ?>
    </div>
    <div class="info">
      <table>
        <tr><th>ID</th><td><?= htmlspecialchars($student['id']); ?></td></tr>
        <tr><th>Name</th><td><?= htmlspecialchars($student['student_name']); ?></td></tr>
        <tr><th>Roll No</th><td><?= htmlspecialchars($student['roll_no']); ?></td></tr>
        <tr><th>Enrollment No</th><td><?= htmlspecialchars($student['enrollment_no']); ?></td></tr>
        <tr><th>Session</th><td><?= htmlspecialchars($student['session'] ?? ''); ?></td></tr>
        <tr><th>Class / Section</th><td><?= htmlspecialchars($student['class_name'] ?? $student['class_id']); ?> / <?= htmlspecialchars($student['section_name'] ?? $student['section_id']); ?></td></tr>
        <tr><th>Date of Birth</th><td><?= htmlspecialchars($student['dob'] ?? ''); ?></td></tr>
        <tr><th>B.form</th><td><?= htmlspecialchars($student['b_form'] ?? ''); ?></td></tr>
        <tr><th>Father Name</th><td><?= htmlspecialchars($student['father_name']); ?></td></tr>
        <tr><th>CNIC</th><td><?= htmlspecialchars($student['cnic'] ?? ''); ?></td></tr>
        <tr><th>Mobile</th><td><?= htmlspecialchars($student['mobile'] ?? ''); ?></td></tr>
        <tr><th>Email</th><td><?= htmlspecialchars($student['email'] ?? ''); ?></td></tr>
        <tr><th>Category</th><td><?= htmlspecialchars($student['category_name'] ?? ''); ?></td></tr>
        <tr><th>Family Category</th><td><?= htmlspecialchars($student['fcategory_name'] ?? ''); ?></td></tr>
        <tr><th>Address</th><td><?= nl2br(htmlspecialchars($student['address'] ?? '')); ?></td></tr>
      </table>
    </div>
  </div>

  <p style="margin-top:30px; color:#666; font-size:12px;">Generated: <?= date('Y-m-d H:i:s'); ?></p>
</body>
</html>
