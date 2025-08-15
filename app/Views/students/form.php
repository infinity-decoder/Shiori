<?php
// $student (null or array), $lookups (arrays), $mode ('create'|'edit')
$mode = $mode ?? 'create';
$student = $student ?? [];
$action = ($mode === 'create') ? ($baseUrl . '/students') : ($baseUrl . '/students/update?id=' . ((int)$student['id']));
?>
<div class="container">
  <div class="row mb-3">
    <div class="col">
      <h1 class="h4"><?= $mode === 'create' ? 'Add Student' : 'Edit Student'; ?></h1>
      <p class="text-muted">Fill the form and save student details.</p>
    </div>
    <div class="col-auto">
      <a href="<?= $baseUrl; ?>/students" class="btn btn-outline-secondary">Back to list</a>
    </div>
  </div>

  <div class="card card-soft">
    <div class="card-body">
      <form method="POST" action="<?= $action; ?>" enctype="multipart/form-data">
        <?= CSRF::field(); ?>

        <?php if ($mode === 'edit'): ?>
        <div class="mb-3">
          <label class="form-label">ID</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($student['id']); ?>" disabled>
        </div>
        <?php endif; ?>

        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Roll No.</label>
            <input name="roll_no" class="form-control" value="<?= htmlspecialchars($student['roll_no'] ?? '') ?>" required>
          </div>

          <div class="col-md-4">
            <label class="form-label">Enrollment No.</label>
            <input name="enrollment_no" class="form-control" value="<?= htmlspecialchars($student['enrollment_no'] ?? '') ?>" required>
          </div>

          <div class="col-md-2">
            <label class="form-label">Class</label>
            <select name="class_id" class="form-select" required>
              <option value="">Select</option>
              <?php foreach ($lookups['classes'] as $c): ?>
                <option value="<?= $c['id']; ?>" <?= (isset($student['class_id']) && $student['class_id'] == $c['id']) ? 'selected' : ''; ?>>
                  <?= htmlspecialchars($c['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label">Section</label>
            <select name="section_id" class="form-select" required>
              <option value="">Select</option>
              <?php foreach ($lookups['sections'] as $sct): ?>
                <option value="<?= $sct['id']; ?>" <?= (isset($student['section_id']) && $student['section_id'] == $sct['id']) ? 'selected' : ''; ?>>
                  <?= htmlspecialchars($sct['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Student Name</label>
            <input name="student_name" class="form-control" value="<?= htmlspecialchars($student['student_name'] ?? '') ?>" required>
          </div>

          <div class="col-md-3">
            <label class="form-label">Date of Birth</label>
            <input id="dob" name="dob" class="form-control" value="<?= htmlspecialchars($student['dob'] ?? '') ?>">
          </div>

          <div class="col-md-3">
            <label class="form-label">B.form</label>
            <input name="b_form" class="form-control" value="<?= htmlspecialchars($student['b_form'] ?? '') ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label">Father Name</label>
            <input name="father_name" class="form-control" value="<?= htmlspecialchars($student['father_name'] ?? '') ?>" required>
          </div>

          <div class="col-md-3">
            <label class="form-label">CNIC</label>
            <input name="cnic" class="form-control" value="<?= htmlspecialchars($student['cnic'] ?? '') ?>">
          </div>

          <div class="col-md-3">
            <label class="form-label">Mobile</label>
            <input name="mobile" class="form-control" value="<?= htmlspecialchars($student['mobile'] ?? '') ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label">Father Occupation</label>
            <input name="father_occupation" class="form-control" value="<?= htmlspecialchars($student['father_occupation'] ?? '') ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select" required>
              <option value="">Select</option>
              <?php foreach ($lookups['categories'] as $cat): ?>
                <option value="<?= $cat['id']; ?>" <?= (isset($student['category_id']) && $student['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                  <?= htmlspecialchars($cat['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Family Category</label>
            <select name="fcategory_id" class="form-select" required>
              <option value="">Select</option>
              <?php foreach ($lookups['familyCategories'] as $fc): ?>
                <option value="<?= $fc['id']; ?>" <?= (isset($student['fcategory_id']) && $student['fcategory_id'] == $fc['id']) ? 'selected' : ''; ?>>
                  <?= htmlspecialchars($fc['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input name="email" type="email" class="form-control" value="<?= htmlspecialchars($student['email'] ?? '') ?>">
          </div>

          <div class="col-12">
            <label class="form-label">Address</label>
            <textarea name="address" rows="2" class="form-control"><?= htmlspecialchars($student['address'] ?? '') ?></textarea>
          </div>

          <div class="col-md-6">
            <label class="form-label">Photo</label>
            <?php if (!empty($student['photo_path'])): ?>
              <div class="mb-2">
                <img src="<?= $baseUrl; ?>/uploads/students/<?= rawurlencode($student['photo_path']); ?>" alt="photo" style="height:120px; border-radius:6px;">
              </div>
            <?php endif; ?>
            <input name="photo" type="file" accept="image/*" class="form-control">
            <small class="text-muted">Allowed: jpg, png, webp. Max 3 MB.</small>
          </div>
        </div>

        <div class="mt-4">
          <button class="btn btn-primary" type="submit"><?= $mode === 'create' ? 'Save' : 'Update'; ?></button>
          <a href="<?= $baseUrl; ?>/students" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  // initialize date picker (flatpickr included in layout)
  if (typeof flatpickr !== 'undefined') {
    flatpickr("#dob", { dateFormat: "Y-m-d", allowInput: true });
  }
</script>
