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
            <!-- Cropper CSS -->
            <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
            
            <div class="card border-0 shadow-sm">
              <div class="card-body text-center p-3">
                
                <!-- Fixed Dimension Container for Placeholder/Image -->
                <div class="mb-3 mx-auto position-relative" style="width: 200px; height: 200px; overflow: hidden; border-radius: 8px; background-color: #f8f9fa; border: 1px solid #dee2e6;">
                  
                  <?php if (!empty($student['photo_path'])): ?>
                    <img id="currentPhoto" src="<?= getStudentImageUrl($student, $baseUrl, 'full') ?>" alt="photo" 
                         style="width: 100%; height: 100%; object-fit: cover;">
                  <?php endif; ?>

                  <!-- Placeholder is shown if no current photo exists OR if we are in 'remove' state -->
                  <div id="placeholderPhoto" class="d-flex align-items-center justify-content-center w-100 h-100" 
                       style="<?= !empty($student['photo_path']) ? 'display:none;' : '' ?>">
                    <i class="bi bi-person fs-1 text-muted" style="font-size: 4rem !important;"></i>
                  </div>
                  
                  <!-- This will show the cropped result immediately -->
                  <img id="finalPreview" style="display:none; width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; z-index: 10;">
                </div>

                <div class="d-grid gap-2 mb-2">
                  <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('photoInput').click()">
                    <i class="bi bi-camera"></i> Select Photo
                  </button>
                  <button type="button" id="removePhotoBtn" class="btn btn-outline-danger" onclick="removePhoto()" style="display: none;">
                    <i class="bi bi-trash"></i> Remove
                  </button>
                </div>

                <!-- Hidden Input for File Selection -->
                <input type="file" id="photoInput" accept="image/jpeg,image/png,image/webp" style="display: none;">
                <!-- Actual Input for Form Submission -->
                <input type="hidden" name="cropped_image" id="croppedImageBase64">
                
                <div id="photoError" class="invalid-feedback d-block text-center mt-2"></div>

                <div class="small text-muted mt-2">
                  <i class="bi bi-info-circle"></i> Allowed: JPG, PNG, WebP. Max 3MB.
                </div>
              </div>
            </div>

            <!-- Cropping Modal (Large) -->
            <div class="modal fade" id="cropModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
              <div class="modal-dialog modal-xl modal-dialog-centered"> <!-- changed to modal-xl -->
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Crop Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body p-0" style="height: 70vh; background: #333; position: relative;">
                    <!-- Image container must be full size -->
                    <div style="width: 100%; height: 100%;">
                        <img id="imageToCrop" style="max-width: 100%; display: block;">
                    </div>
                  </div>
                  <div class="modal-footer justify-content-between">
                    <div class="text-muted small">
                         Mouse wheel to zoom. Drag to move.
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary px-4" id="cropBtn"><i class="bi bi-check-lg"></i> Crop & Update Preview</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
              const photoInput = document.getElementById('photoInput');
              const imageToCrop = document.getElementById('imageToCrop');
              const cropBtn = document.getElementById('cropBtn');
              const finalPreview = document.getElementById('finalPreview');
              const currentPhoto = document.getElementById('currentPhoto');
              const placeholderPhoto = document.getElementById('placeholderPhoto');
              const croppedImageInput = document.getElementById('croppedImageBase64');
              const removeBtn = document.getElementById('removePhotoBtn');
              const errorDiv = document.getElementById('photoError');
              
              let cropper;
              let cropModal;

              // Initialize bootstrap modal
              if (typeof bootstrap !== 'undefined') {
                  cropModal = new bootstrap.Modal(document.getElementById('cropModal'));
              }

              // Show remove button and hide placeholder if image exists on load
              if (currentPhoto && currentPhoto.getAttribute('src')) {
                  removeBtn.style.display = 'block';
                  if(placeholderPhoto) placeholderPhoto.style.display = 'none';
              }

              photoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;

                // Validate
                const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
                if (!validTypes.includes(file.type)) {
                   showError('❌ Invalid file type. Use JPG, PNG, or WebP.');
                   return;
                }
                if (file.size > 3 * 1024 * 1024) {
                   showError('❌ File too large. Max 3MB.');
                   return;
                }
                clearError();

                const reader = new FileReader();
                reader.onload = function(event) {
                  // Reset image source
                  imageToCrop.src = event.target.result;
                  
                  if (cropModal) {
                      cropModal.show();
                      // We must wait for modal to be visible before initializing cropper for correct math
                      document.getElementById('cropModal').addEventListener('shown.bs.modal', initCropperOnce, { once: true });
                  }
                };
                reader.readAsDataURL(file);
                
                this.value = '';
              });

              function initCropperOnce() {
                  if (cropper) cropper.destroy();
                  cropper = new Cropper(imageToCrop, {
                    aspectRatio: 1, // Square
                    viewMode: 1,    // Restrict crop box to canvas
                    dragMode: 'move',
                    autoCropArea: 0.8,
                    responsive: true,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    toggleDragModeOnDblclick: false,
                  });
              }

              cropBtn.addEventListener('click', function() {
                if (!cropper) return;

                // Get cropped canvas
                const canvas = cropper.getCroppedCanvas({
                  width: 400, // Reasonable size for profile
                  height: 400,
                  fillColor: '#fff',
                  imageSmoothingEnabled: true,
                  imageSmoothingQuality: 'high',
                });

                if (!canvas) {
                    console.error('Could not get cropped canvas');
                    return;
                }

                // Convert to base64
                const base64 = canvas.toDataURL('image/jpeg', 0.9);
                
                // Update interface
                finalPreview.src = base64;
                finalPreview.style.display = 'block';
                
                // Hide others
                if (currentPhoto) currentPhoto.style.display = 'none';
                if (placeholderPhoto) placeholderPhoto.style.display = 'none';
                
                // FORCE visibility via z-index just in case
                finalPreview.style.zIndex = '100';

                // Set hidden input
                croppedImageInput.value = base64;
                removeBtn.style.display = 'block';

                if (cropModal) cropModal.hide();
              });

              window.removePhoto = function() {
                 finalPreview.src = '';
                 finalPreview.style.display = 'none';
                 croppedImageInput.value = 'remove'; 
                 
                 if (currentPhoto) currentPhoto.style.display = 'none'; 
                 // Show placeholder
                 if (placeholderPhoto) {
                     placeholderPhoto.style.display = 'flex'; // Use flex to center icon
                     placeholderPhoto.style.removeProperty('display'); // Or revert to stylesheet
                     placeholderPhoto.className = 'd-flex align-items-center justify-content-center w-100 h-100'; // re-apply boostrap classes just in case
                 }
                 
                 removeBtn.style.display = 'none';
              };

              function showError(msg) {
                errorDiv.textContent = msg;
                errorDiv.style.display = 'block';
              }
              function clearError() {
                 errorDiv.style.display = 'none';
              }
              
              // Clean up cropper when modal closes
              document.getElementById('cropModal').addEventListener('hidden.bs.modal', function () {
                 if (cropper) {
                     cropper.destroy();
                     cropper = null;
                 }
                 // Reset file input logic just in case
                 photoInput.value = ''; 
              });
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
