<?php
// $student (null or array), $lookups (arrays), $mode ('create'|'edit')
$mode = $mode ?? 'create';
$student = $student ?? [];

$action = ($mode === 'create') ? ($baseUrl . '/students') : ($baseUrl . '/students/update?id=' . ((int)$student['id']));

// Generate session options dynamically (3 years back, 3 years forward)
$nowY = (int)date('Y');
$sessions = [];
for ($y = $nowY - 3; $y <= $nowY + 3; $y++) {
    $sessions[] = sprintf('%04d-%04d', $y, $y + 1);
}
?>
<div class="container">
  <div class="row mb-3 align-items-center">
    <div class="col">
      <h1 class="h4 mb-0"><?= $mode === 'create' ? 'Add Student' : 'Edit Student'; ?></h1>
      <p class="text-muted">Use the form to <?= $mode === 'create' ? 'add' : 'update'; ?> student details. Fields marked <span class="text-danger">*</span> are required.</p>
    </div>
    <div class="col-auto d-flex gap-2">
      <a href="<?= $baseUrl; ?>/dashboard" class="btn btn-outline-secondary">Dashboard</a>
      <a href="<?= $baseUrl; ?>/students" class="btn btn-outline-secondary">Back to list</a>
    </div>
  </div>

  <div class="card card-soft shadow-sm">
    <div class="card-body">
      <form id="studentForm" method="POST" action="<?= $action; ?>" enctype="multipart/form-data" novalidate>
        <?= CSRF::field(); ?>

        <div class="row">
          <div class="col-lg-8">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Roll No. <span class="text-danger">*</span></label>
                <input name="roll_no" class="form-control form-control-lg" value="<?= htmlspecialchars($student['roll_no'] ?? '') ?>" required>
              </div>

              <div class="col-md-4">
                <label class="form-label">Enrollment No. <span class="text-danger">*</span></label>
                <input name="enrollment_no" class="form-control form-control-lg" value="<?= htmlspecialchars($student['enrollment_no'] ?? '') ?>" required>
              </div>

              <div class="col-md-4">
                <label class="form-label">Session</label>
                <select name="session" class="form-select form-select-lg">
                  <option value="">(select)</option>
                  <?php foreach ($sessions as $s): ?>
                    <option value="<?= $s; ?>" <?= (isset($student['session']) && $student['session'] === $s) ? 'selected' : ''; ?>><?= $s; ?></option>
                  <?php endforeach; ?>
                </select>
                <div class="small text-muted mt-1">Academic session, e.g. 2025-2026</div>
              </div>

              <div class="col-md-4">
                <label class="form-label">Class <span class="text-danger">*</span></label>
                <select name="class_id" class="form-select form-select-lg" required>
                  <option value="">Select class</option>
                  <?php foreach ($lookups['classes'] as $c): ?>
                    <option value="<?= $c['id']; ?>" <?= (isset($student['class_id']) && $student['class_id'] == $c['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($c['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label">Section <span class="text-danger">*</span></label>
                <select name="section_id" class="form-select form-select-lg" required>
                  <option value="">Select section</option>
                  <?php foreach ($lookups['sections'] as $sct): ?>
                    <option value="<?= $sct['id']; ?>" <?= (isset($student['section_id']) && $student['section_id'] == $sct['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($sct['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label">Student Name <span class="text-danger">*</span></label>
                <input name="student_name" class="form-control form-control-lg" value="<?= htmlspecialchars($student['student_name'] ?? '') ?>" required>
              </div>

              <div class="col-md-4">
                <label class="form-label">Date of Birth</label>
                <input id="dob" name="dob" class="form-control form-control-lg" value="<?= htmlspecialchars($student['dob'] ?? '') ?>" placeholder="YYYY-MM-DD">
              </div>

              <div class="col-md-4">
                <label class="form-label">B.form</label>
                <input name="b_form" class="form-control form-control-lg" value="<?= htmlspecialchars($student['b_form'] ?? '') ?>">
              </div>

              <div class="col-md-6">
                <label class="form-label">Father Name <span class="text-danger">*</span></label>
                <input name="father_name" class="form-control form-control-lg" value="<?= htmlspecialchars($student['father_name'] ?? '') ?>" required>
              </div>

              <div class="col-md-3">
                <label class="form-label">CNIC</label>
                <input name="cnic" class="form-control form-control-lg" value="<?= htmlspecialchars($student['cnic'] ?? '') ?>" placeholder="xxxxx-xxxxxxx-x">
              </div>

              <div class="col-md-3">
                <label class="form-label">Mobile</label>
                <input name="mobile" class="form-control form-control-lg" value="<?= htmlspecialchars($student['mobile'] ?? '') ?>">
              </div>

              <div class="col-md-6">
                <label class="form-label">Father Occupation</label>
                <input name="father_occupation" class="form-control form-control-lg" value="<?= htmlspecialchars($student['father_occupation'] ?? '') ?>">
              </div>
                            <!-- BPS (after father_occupation) -->
              <div class="col-md-3">
                <label class="form-label">BPS</label>
                <input name="bps" class="form-control form-control-lg" value="<?= htmlspecialchars($student['bps'] ?? '') ?>" placeholder="e.g. 17">
              </div>

              <!-- Religion -->
              <div class="col-md-3">
                <label class="form-label">Religion</label>
                <input name="religion" class="form-control form-control-lg" value="<?= htmlspecialchars($student['religion'] ?? '') ?>" placeholder="e.g. Islam">
              </div>

              <!-- Caste -->
              <div class="col-md-3">
                <label class="form-label">Caste</label>
                <input name="caste" class="form-control form-control-lg" value="<?= htmlspecialchars($student['caste'] ?? '') ?>" placeholder="e.g. Sheikh">
              </div>

              <!-- Domicile -->
              <div class="col-md-3">
                <label class="form-label">Domicile</label>
                <input name="domicile" class="form-control form-control-lg" value="<?= htmlspecialchars($student['domicile'] ?? '') ?>" placeholder="e.g. Punjab">
              </div>


              <div class="col-md-6">
                <label class="form-label">Category <span class="text-danger">*</span></label>
                <select name="category_id" class="form-select form-select-lg" required>
                  <option value="">Select category</option>
                  <?php foreach ($lookups['categories'] as $cat): ?>
                    <option value="<?= $cat['id']; ?>" <?= (isset($student['category_id']) && $student['category_id'] == $cat['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($cat['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Family Category <span class="text-danger">*</span></label>
                <select name="fcategory_id" class="form-select form-select-lg" required>
                  <option value="">Select family category</option>
                  <?php foreach ($lookups['familyCategories'] as $fc): ?>
                    <option value="<?= $fc['id']; ?>" <?= (isset($student['fcategory_id']) && $student['fcategory_id'] == $fc['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($fc['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Email</label>
                <input name="email" type="email" class="form-control form-control-lg" value="<?= htmlspecialchars($student['email'] ?? '') ?>">
              </div>

              <div class="col-12">
                <label class="form-label">Address</label>
                <textarea name="address" rows="3" class="form-control"><?= htmlspecialchars($student['address'] ?? '') ?></textarea>
              </div>

              <div class="col-12 text-end mt-2">
                <button class="btn btn-lg btn-primary"><?= $mode === 'create' ? 'Save Student' : 'Update Student'; ?></button>
                <a href="<?= $baseUrl; ?>/students" class="btn btn-lg btn-outline-secondary ms-2">Cancel</a>
              </div>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
              <div class="card-body text-center">
                <div class="mb-3">
                  <?php if (!empty($student['photo_path'])): ?>
                    <img src="<?= $baseUrl; ?>/uploads/students/<?= rawurlencode($student['photo_path']); ?>" alt="photo" style="max-width:100%; border-radius:8px;">
                  <?php else: ?>
                    <div class="bg-light d-flex align-items-center justify-content-center" style="height:220px; border-radius:8px;">
                      <i class="bi bi-person fs-1 text-muted"></i>
                    </div>
                  <?php endif; ?>
                </div>

                <label class="form-label w-100 text-start">Photo (jpg, png, webp)</label>
                <input id="photoFile" name="photo" type="file" accept="image/*" class="form-control">

                <div class="small text-muted mt-2">
                  Recommended size: square; max 3 MB.
                </div>
              </div>
            </div>

            <div class="mt-3 text-center">
              <small class="text-muted">Tip: use the calendar for Date of Birth. Session can be selected from the dropdown.</small>
            </div>
          </div>
        </div>

      </form>
    </div>
  </div>
</div>

<script>
  // initialize Flatpickr with friendly alt input and calendar
  if (typeof flatpickr !== 'undefined') {
    flatpickr("#dob", {
      dateFormat: "Y-m-d",
      altInput: true,
      altFormat: "F j, Y",
      allowInput: true,
      maxDate: "today",
      // optionally provide a year range (helps with the UI)
      yearRange: [1900, (new Date()).getFullYear()],
    });
  }

  // FilePond initialization
  (function () {
    if (typeof FilePond === 'undefined') return;
    const inputElement = document.getElementById('photoFile');
    const pond = FilePond.create(inputElement, {
      allowMultiple: false,
      maxFiles: 1,
      maxFileSize: '3MB',
      acceptedFileTypes: ['image/jpeg', 'image/png', 'image/webp'],
      labelIdle: 'Drag & Drop your photo or <span class="filepond--label-action">Browse</span>',
    });
  })();
</script>
