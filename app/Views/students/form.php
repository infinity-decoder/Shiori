<?php
// $student (null or array), $lookups (arrays), $mode ('create'|'edit'), $fields (array of active fields)
$mode = $mode ?? 'create';
$student = $student ?? [];
$fields = $fields ?? [];

$action = ($mode === 'create') ? ($baseUrl . '/students') : ($baseUrl . '/students/update?id=' . ((int)$student['id']));

// Generate session options dynamically (3 years back, 3 years forward)
// REMOVED: Now using database lookups passed via $lookups['sessions']

// Helper to get value - check old input first, then student data
$getValue = function($name) use ($student) {
    // Priority: old_input > student data > empty string
    if (Auth::hasOldInput($name)) {
        return htmlspecialchars(Auth::getOldInput($name));
    }
    return htmlspecialchars($student[$name] ?? '');
};

// Define required fields for asterisk display
$requiredFields = ['student_name', 'dob', 'father_name', 'father_occupation', 'cnic', 'mobile', 'address'];
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
                  <label class="form-label">
                    <?= htmlspecialchars($field['label']) ?>
                    <?php if (in_array($name, $requiredFields)): ?>
                      <span class="text-danger" title="Required field">*</span>
                    <?php endif; ?>
                  </label>
                  
                  <?php if ($name === 'session'): ?>
                    <select name="session" class="form-select form-select-lg">
                      <option value="">(select)</option>
                      <?php 
                      // Use sessions from database lookup
                      $availableSessions = $lookups['sessions'] ?? [];
                      
                      // If editing a student with an inactive/old session, ensure it appears
                      if (!empty($student['session'])) {
                          $found = false;
                          foreach ($availableSessions as $s) {
                              if ($s['session_year'] === $student['session']) {
                                  $found = true;
                                  break;
                              }
                          }
                          // Add current student's session if not in the active list
                          if (!$found) {
                              array_unshift($availableSessions, ['session_year' => $student['session'], 'id' => 0]);
                          }
                      }
                      ?>
                      
                      <?php foreach ($availableSessions as $s): ?>
                        <option value="<?= htmlspecialchars($s['session_year']); ?>" 
                          <?= (isset($student['session']) && $student['session'] === $s['session_year']) ? 'selected' : ''; ?>>
                          <?= htmlspecialchars($s['session_year']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>

                  <?php elseif ($name === 'class_id'): ?>
                    <?php
                    // Preserve inactive class if selected
                    $availableClasses = $lookups['classes'];
                    if (!empty($student['class_id'])) {
                        $found = false;
                        foreach ($availableClasses as $c) {
                            if ($c['id'] == $student['class_id']) {
                                $found = true;
                                break;
                            }
                        }
                        if (!$found && !empty($student['class_name'])) {
                            // Add the inactive class to the list
                            array_unshift($availableClasses, [
                                'id' => $student['class_id'],
                                'name' => $student['class_name'] . ' (Inactive)'
                            ]);
                        }
                    }
                    ?>
                    <select name="class_id" class="form-select form-select-lg" required>
                      <option value="">Select class</option>
                      <?php foreach ($availableClasses as $c): ?>
                        <option value="<?= $c['id']; ?>" <?= (isset($student['class_id']) && $student['class_id'] == $c['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($c['name']); ?></option>
                      <?php endforeach; ?>
                    </select>

                  <?php elseif ($name === 'section_id'): ?>
                    <?php
                    // Preserve inactive section if selected
                    $availableSections = $lookups['sections'];
                    if (!empty($student['section_id'])) {
                        $found = false;
                        foreach ($availableSections as $sct) {
                            if ($sct['id'] == $student['section_id']) {
                                $found = true;
                                break;
                            }
                        }
                        if (!$found && !empty($student['section_name'])) {
                            array_unshift($availableSections, [
                                'id' => $student['section_id'],
                                'name' => $student['section_name'] . ' (Inactive)'
                            ]);
                        }
                    }
                    ?>
                    <select name="section_id" class="form-select form-select-lg" required>
                      <option value="">Select section</option>
                      <?php foreach ($availableSections as $sct): ?>
                        <option value="<?= $sct['id']; ?>" <?= (isset($student['section_id']) && $student['section_id'] == $sct['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($sct['name']); ?></option>
                      <?php endforeach; ?>
                    </select>

                  <?php elseif ($name === 'category_id'): ?>
                    <?php
                    // Preserve inactive category if selected
                    $availableCategories = $lookups['categories'];
                    if (!empty($student['category_id'])) {
                        $found = false;
                        foreach ($availableCategories as $cat) {
                            if ($cat['id'] == $student['category_id']) {
                                $found = true;
                                break;
                            }
                        }
                        if (!$found && !empty($student['category_name'])) {
                            array_unshift($availableCategories, [
                                'id' => $student['category_id'],
                                'name' => $student['category_name'] . ' (Inactive)'
                            ]);
                        }
                    }
                    ?>
                    <select name="category_id" class="form-select form-select-lg" required>
                      <option value="">Select category</option>
                      <?php foreach ($availableCategories as $cat): ?>
                        <option value="<?= $cat['id']; ?>" <?= (isset($student['category_id']) && $student['category_id'] == $cat['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($cat['name']); ?></option>
                      <?php endforeach; ?>
                    </select>

                  <?php elseif ($name === 'fcategory_id'): ?>
                    <?php
                    // Preserve inactive family category if selected
                    $availableFCategories = $lookups['familyCategories'];
                    if (!empty($student['fcategory_id'])) {
                        $found = false;
                        foreach ($availableFCategories as $fc) {
                            if ($fc['id'] == $student['fcategory_id']) {
                                $found = true;
                                break;
                            }
                        }
                        if (!$found && !empty($student['fcategory_name'])) {
                            array_unshift($availableFCategories, [
                                'id' => $student['fcategory_id'],
                                'name' => $student['fcategory_name'] . ' (Inactive)'
                            ]);
                        }
                    }
                    ?>
                    <select name="fcategory_id" class="form-select form-select-lg" required>
                      <option value="">Select family category</option>
                      <?php foreach ($availableFCategories as $fc): ?>
                        <option value="<?= $fc['id']; ?>" <?= (isset($student['fcategory_id']) && $student['fcategory_id'] == $fc['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($fc['name']); ?></option>
                      <?php endforeach; ?>
                    </select>

                  <?php elseif ($field['type'] === 'textarea' || $name === 'address'): ?>
                    <textarea 
                      name="<?= $name ?>" 
                      rows="3" 
                      class="form-control"
                      <?= in_array($name, $requiredFields) ? 'required' : '' ?>
                    ><?= $getValue($name) ?></textarea>

                  <?php elseif ($field['type'] === 'date' || $name === 'dob'): ?>
                    <input 
                      id="<?= $name === 'dob' ? 'dob' : '' ?>" 
                      name="<?= $name ?>" 
                      type="date" 
                      class="form-control form-control-lg" 
                      value="<?= $getValue($name) ?>"
                      <?= in_array($name, $requiredFields) ? 'required' : '' ?>
                    >

                  <?php elseif ($field['type'] === 'select'): ?>
                    <?php 
                        $opts = array_map('trim', explode(',', $field['options'] ?? ''));
                        $val = $getValue($name);
                    ?>
                    <select name="<?= $name ?>" class="form-select form-select-lg">
                        <option value="">(select)</option>
                        <?php foreach ($opts as $opt): ?>
                            <option value="<?= htmlspecialchars($opt) ?>" <?= $val === $opt ? 'selected' : '' ?>><?= htmlspecialchars($opt) ?></option>
                        <?php endforeach; ?>
                    </select>

                  <?php elseif ($field['type'] === 'radio'): ?>
                    <?php 
                        $opts = array_map('trim', explode(',', $field['options'] ?? ''));
                        $val = $getValue($name);
                    ?>
                    <div class="mt-2">
                        <?php foreach ($opts as $opt): ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="<?= $name ?>" value="<?= htmlspecialchars($opt) ?>" id="<?= $name . '_' . md5($opt) ?>" <?= $val === $opt ? 'checked' : '' ?>>
                                <label class="form-check-label" for="<?= $name . '_' . md5($opt) ?>"><?= htmlspecialchars($opt) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                  <?php else: ?>
                    <?php if ($name === 'b_form' || $name === 'cnic'): ?>
                      <!-- Special handling for B-Form and CNIC with 13-digit validation -->
                      <input 
                        id="<?= $name ?>" 
                        name="<?= $name ?>" 
                        type="text" 
                        class="form-control form-control-lg" 
                        value="<?= $getValue($name) ?>" 
                        maxlength="13" 
                        pattern="[0-9]{13}" 
                        data-validate="13-digits"
                        <?= in_array($name, $requiredFields) ? 'required' : '' ?>
                        placeholder="13-digit <?= $name === 'b_form' ? 'B-Form' : 'CNIC' ?> number"
                      >
                      <div class="invalid-feedback-custom text-danger small mt-1" id="<?= $name ?>-error" style="display:none;"></div>
                    <?php else: ?>
                      <!-- Standard text/number inputs -->
                      <input 
                        name="<?= $name ?>" 
                        type="<?= $field['type'] === 'number' ? 'number' : 'text' ?>" 
                        class="form-control form-control-lg" 
                        value="<?= $getValue($name) ?>"
                        <?= in_array($name, $requiredFields) ? 'required' : '' ?>
                      >
                    <?php endif; ?>
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
                    <img id="currentPhoto" src="<?= getStudentImageUrl($student, $baseUrl, 'full') ?>" alt="photo" style="max-width:100%; border-radius:8px;">
                  <?php else: ?>
                    <div id="placeholderPhoto" class="bg-light d-flex align-items-center justify-content-center" style="height:220px; border-radius:8px;">
                      <i class="bi bi-person fs-1 text-muted"></i>
                    </div>
                  <?php endif; ?>
                  
                  <!-- Preview for newly selected photo -->
                  <div id="photoPreviewContainer" style="display:none; margin-top:15px;">
                    <img id="photoPreview" style="max-width:100%; border-radius:8px;">
                    <button type="button" onclick="clearPhoto()" class="btn btn-sm btn-outline-danger mt-2">
                      <i class="bi bi-x-circle"></i> Remove Photo
                    </button>
                  </div>
                </div>

                <label class="form-label w-100 text-start">Photo (jpg, png, webp)</label>
                <input id="photoFile" name="photo" type="file" accept="image/jpeg,image/jpg,image/png,image/webp" class="form-control">
                <div id="photoError" class="invalid-feedback d-block"></div>

                <div class="small text-muted mt-2">
                  Recommended: Square image, max 3 MB
                </div>
              </div>
            </div>
            
            <script>
            document.addEventListener('DOMContentLoaded', function() {
              const photoInput = document.getElementById('photoFile');
              const preview = document.getElementById('photoPreview');
              const previewContainer = document.getElementById('photoPreviewContainer');
              const errorDiv = document.getElementById('photoError');
              const currentPhoto = document.getElementById('currentPhoto');
              const placeholderPhoto = document.getElementById('placeholderPhoto');
              
              if (!photoInput) return;

              photoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                
                // Reset errors
                errorDiv.textContent = '';
                errorDiv.style.display = 'none';
                
                if (!file) return;
                
                console.log('Photo selected:', file.name, file.type, file.size);
                
                // Validate file type
                // Note: file.type might be empty on some systems, but usually present for images
                const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
                if (file.type && !validTypes.includes(file.type)) {
                  errorDiv.textContent = '❌ Invalid file type. Only JPG, PNG, and WebP allowed.';
                  errorDiv.style.display = 'block';
                  photoInput.value = '';
                  return;
                }
                
                // Validate size (3MB)
                if (file.size > 3 * 1024 * 1024) {
                   const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                   errorDiv.textContent = `❌ File too large (${sizeMB}MB). Max 3MB.`;
                   errorDiv.style.display = 'block';
                   photoInput.value = '';
                   return;
                }
                
                // Hide current display
                if (currentPhoto) currentPhoto.style.display = 'none';
                if (placeholderPhoto) placeholderPhoto.style.display = 'none';
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(event) {
                  preview.src = event.target.result;
                  previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
              });
              
              // Expose clear function globally for the button
              window.clearPhoto = function() {
                photoInput.value = '';
                previewContainer.style.display = 'none';
                errorDiv.style.display = 'none';
                
                // Restore original state (remove inline display:none to let CSS/Classes take over)
                if (currentPhoto) currentPhoto.style.display = '';
                if (placeholderPhoto) placeholderPhoto.style.display = '';
              };
            });
            </script>
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

  // Real-time validation for B-Form and CNIC fields (13 digits, numeric only)
  (function () {
    const fieldsToValidate = ['b_form', 'cnic'];
    
    fieldsToValidate.forEach(fieldName => {
      const field = document.getElementById(fieldName);
      if (!field) return;
      
      const errorDiv = document.getElementById(fieldName + '-error');
      
      function validateField() {
        const value = field.value;
        const digitsOnly = value.replace(/\D/g, '');
        const length = digitsOnly.length;
        
        // Remove non-numeric characters
        if (value !== digitsOnly) {
          field.value = digitsOnly;
        }
        
        // Show error if not empty and not 13 digits
        if (digitsOnly.length > 0 && digitsOnly.length < 13) {
          const fieldLabel = fieldName === 'b_form' ? 'B-Form' : 'CNIC';
          errorDiv.textContent = `${fieldLabel} must be exactly 13 digits (currently ${length}/13)`;
          errorDiv.style.display = 'block';
          field.classList.add('is-invalid');
          field.classList.remove('is-valid');
        } else if (digitsOnly.length === 13) {
          errorDiv.style.display = 'none';
          field.classList.remove('is-invalid');
          field.classList.add('is-valid');
        } else {
          errorDiv.style.display = 'none';
          field.classList.remove('is-invalid');
          field.classList.remove('is-valid');
        }
      }
      
      // Validate on input (real-time)
      field.addEventListener('input', validateField);
      
      // Validate on blur
      field.addEventListener('blur', validateField);
      
      // Prevent paste of non-numeric content
      field.addEventListener('paste', function(e) {
        setTimeout(validateField, 10);
      });
      
      // Initial validation if field has value
      if (field.value) {
        validateField();
      }
    });
  })();
</script>
