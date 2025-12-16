<?php
// This file is included directly by StudentController::print()
// $student and $baseUrl are available
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Print Student #<?= htmlspecialchars($student['roll_no']); ?></title>
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
  <button onclick="history.back()">Back</button>
</div>

  <h2>Student Profile —<?= htmlspecialchars($student['student_name']); ?></h2>

  <div class="profile">
    <div class="photo">
      <img src="<?= getStudentImageUrl($student, $baseUrl, 'full') ?>" 
           alt="<?= htmlspecialchars($student['student_name']) ?>" 
           style="width:100%; height:auto; max-height:220px; object-fit:contain;">
    </div>
    <div class="info">
      <table>
        
        
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

  <p style="margin-top:30px; color:#666; font-size:12px;">Generated: <?= date('Y-m-d H:i:s'); ?></p>
</body>
</html>
