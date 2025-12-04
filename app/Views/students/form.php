<?php
// $student (null or array), $lookups (arrays), $mode ('create'|'edit'), $fields (array of active fields)
$mode = $mode ?? 'create';
$student = $student ?? [];
$fields = $fields ?? [];

$action = ($mode === 'create') ? ($baseUrl . '/students') : ($baseUrl . '/students/update?id=' . ((int)$student['id']));

// Generate session options dynamically (3 years back, 3 years forward)
$nowY = (int)date('Y');
$sessions = [];
for ($y = $nowY - 3; $y <= $nowY + 3; $y++) {
    $sessions[] = sprintf('%04d-%04d', $y, $y + 1);
}

// Helper to get value
$getValue = function($name) use ($student) {
    return htmlspecialchars($student[$name] ?? '');
};
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
              <?php foreach ($fields as $field): ?>
                <?php 
                  $name = $field['name'];
                  if ($name === 'photo_path') continue; // Handled in side column
                  
                  // Determine column width
                  $colClass = 'col-md-6';
                  if (in_array($name, ['roll_no', 'enrollment_no', 'session', 'dob', 'cnic', 'mobile', 'bps', 'religion', 'caste', 'domicile'])) {
                      $colClass = 'col-md-4';
                  }
                  if ($name === 'address') {
                      $colClass = 'col-12';
                  }
                ?>
                
                <div class="<?= $colClass ?>">
                  <label class="form-label"><?= htmlspecialchars($field['label']) ?></label>
                  
                  <?php if ($name === 'session'): ?>
                    <select name="session" class="form-select form-select-lg">
                      <option value="">(select)</option>
                      <?php foreach ($sessions as $s): ?>
                        <option value="<?= $s; ?>" <?= (isset($student['session']) && $student['session'] === $s) ? 'selected' : ''; ?>><?= $s; ?></option>
                      <?php endforeach; ?>
                    </select>

                  <?php elseif ($name === 'class_id'): ?>
                    <select name="class_id" class="form-select form-select-lg" required>
                      <option value="">Select class</option>
                      <?php foreach ($lookups['classes'] as $c): ?>
                        <option value="<?= $c['id']; ?>" <?= (isset($student['class_id']) && $student['class_id'] == $c['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($c['name']); ?></option>
                      <?php endforeach; ?>
                    </select>

                  <?php elseif ($name === 'section_id'): ?>
                    <select name="section_id" class="form-select form-select-lg" required>
                      <option value="">Select section</option>
                      <?php foreach ($lookups['sections'] as $sct): ?>
                        <option value="<?= $sct['id']; ?>" <?= (isset($student['section_id']) && $student['section_id'] == $sct['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($sct['name']); ?></option>
                      <?php endforeach; ?>
                    </select>

                  <?php elseif ($name === 'category_id'): ?>
                    <select name="category_id" class="form-select form-select-lg" required>
                      <option value="">Select category</option>
                      <?php foreach ($lookups['categories'] as $cat): ?>
                        <option value="<?= $cat['id']; ?>" <?= (isset($student['category_id']) && $student['category_id'] == $cat['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($cat['name']); ?></option>
                      <?php endforeach; ?>
                    </select>

                  <?php elseif ($name === 'fcategory_id'): ?>
                    <select name="fcategory_id" class="form-select form-select-lg" required>
                      <option value="">Select family category</option>
                      <?php foreach ($lookups['familyCategories'] as $fc): ?>
                        <option value="<?= $fc['id']; ?>" <?= (isset($student['fcategory_id']) && $student['fcategory_id'] == $fc['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($fc['name']); ?></option>
                      <?php endforeach; ?>
                    </select>

                  <?php elseif ($field['type'] === 'textarea' || $name === 'address'): ?>
                    <textarea name="<?= $name ?>" rows="3" class="form-control"><?= $getValue($name) ?></textarea>

                  <?php elseif ($field['type'] === 'date' || $name === 'dob'): ?>
                    <input id="<?= $name === 'dob' ? 'dob' : '' ?>" name="<?= $name ?>" type="date" class="form-control form-control-lg" value="<?= $getValue($name) ?>">

                  <?php else: ?>
                    <input name="<?= $name ?>" type="<?= $field['type'] === 'number' ? 'number' : 'text' ?>" class="form-control form-control-lg" value="<?= $getValue($name) ?>">
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
              
              <div class="col-12 text-end mt-2">
                <button class="btn btn-lg btn-primary"><?= $mode === 'create' ? 'Save Student' : 'Update Student'; ?></button>
                <a href="<?= $baseUrl; ?>/students" class="btn btn-lg btn-outline-secondary ms-2">Cancel</a>
              </div>
            </div>
          </div>

          <div class="col-lg-4">
            <?php 
              // Check if photo_path is active
              $photoField = array_filter($fields, fn($f) => $f['name'] === 'photo_path');
              if (!empty($photoField)): 
            ?>
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
            <?php endif; ?>

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
      yearRange: [1900, (new Date()).getFullYear()],
    });
  }

  // FilePond initialization
  (function () {
    if (typeof FilePond === 'undefined') return;
    const inputElement = document.getElementById('photoFile');
    if (inputElement) {
        const pond = FilePond.create(inputElement, {
          allowMultiple: false,
          maxFiles: 1,
          maxFileSize: '3MB',
          acceptedFileTypes: ['image/jpeg', 'image/png', 'image/webp'],
          labelIdle: 'Drag & Drop your photo or <span class="filepond--label-action">Browse</span>',
        });
    }
  })();
</script>
