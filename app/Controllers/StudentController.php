<?php
class StudentController extends Controller
{
    // Standard CSV field order for import/export/template consistency
    const CSV_FIELD_ORDER = [
        'roll_no', 'enrollment_no', 'student_name', 'class_id', 'section_id',
        'session', 'dob', 'b_form', 'father_name', 'father_occupation',
        'cnic', 'mobile', 'email', 'category_id', 'fcategory_id',
        'bps', 'religion', 'caste', 'domicile', 'address'
    ];
        public function index(): void
    {
        $this->requireAuth();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = (int)($_GET['per_page'] ?? 10);
        if (!in_array($perPage, [10,25,50], true)) $perPage = 10;

        // Filters
        $filters = [];
        if (!empty($_GET['class_id'])) {
            $filters['class_id'] = (int)$_GET['class_id'];
        }
        if (!empty($_GET['section_id'])) {
            $filters['section_id'] = (int)$_GET['section_id'];
        }
        $q = trim((string)($_GET['q'] ?? ''));
        if ($q !== '') {
            $filters['q'] = $q;
        }

        $allowedSorts = ['id_desc','id_asc','name_asc','name_desc','roll_asc','roll_desc'];
        $sort = $_GET['sort'] ?? 'id_desc';
        if (!in_array($sort, $allowedSorts, true)) $sort = 'id_desc';

        $total = Student::countFiltered($filters);
        $students = Student::paginate($page, $perPage, $filters, $sort);

        $this->view('students/list.php', [
            'title' => 'Students | Shiori',
            'students' => $students,
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'classes' => Lookup::getClasses(),
            'sections' => Lookup::getSections(),
            'filters' => $filters,
            'sort' => $sort,
        ]);
    }


    public function create(): void
    {
        $this->requireAuth();
        if (Auth::isViewer()) {
            Auth::flash('error', 'Viewers cannot create students.');
            $this->redirect('/students');
        }
        // Load Session model for database-driven sessions
        require_once BASE_PATH . '/app/Models/Session.php';
        
        $lookups = [
            'classes'        => Lookup::getClasses(true),
            'sections'       => Lookup::getSections(true),
            'sessions'       => Lookup::getSessions(), // Already active-only
            'categories'     => Lookup::getCategories(true),
            'familyCategories' => Lookup::getFamilyCategories(true),
        ];
        // Get active fields
        require_once BASE_PATH . '/app/Models/Field.php';
        Field::seedDefaults(); // Ensure defaults exist
        $fields = Field::getAll(true);
        
        if (empty($fields)) {
            // Should not happen after seed, but just in case
            Auth::flash('error', 'No active fields found.');
        }
        
        $this->view('students/form.php', [
            'title'   => 'Add Student | Shiori',
            'student' => null,
            'lookups' => $lookups,
            'fields'  => $fields,
            'mode'    => 'create',
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        if (Auth::isViewer()) {
            Auth::flash('error', 'Viewers cannot create students.');
            $this->redirect('/students');
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/students/create');
        }
        if (!CSRF::verify($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid session token.');
            $this->redirect('/students/create');
        }

        $validation = Validator::validateStudent($_POST, $_FILES);
        if (!empty($validation['errors'])) {
            Auth::setOldInput($_POST); // Preserve form data
            Auth::flash('error', implode(' | ', $validation['errors']));
            $this->redirect('/students/create');
        }

        // Clear old input on successful validation
        Auth::flushOldInput();

        $data = $validation['data'];
        try {
            // Handle Custom Fields (Validator strips them, so we pick from $_POST)
            // We'll fetch all custom fields to know what to look for
            require_once BASE_PATH . '/app/Models/Field.php';
            $allFields = Field::getAll(false);
            $customData = [];
            foreach ($allFields as $f) {
                if ($f['is_custom'] && isset($_POST[$f['name']])) {
                    $customData[$f['name']] = trim($_POST[$f['name']]);
                }
            }
            // Merge into data if Student::create supports it, OR call saveMeta separately
            // Student::create calls saveMeta internally with the passed array, so we merge:
            $fullData = array_merge($data, $customData);
            
            $id = Student::create($fullData);

            // Handle Photo (Priority: Cropped > File Upload)
            if (!empty($_POST['cropped_image']) && strpos($_POST['cropped_image'], 'data:image') === 0) {
                 $saved = ImageService::saveFromBase64($_POST['cropped_image'], $id);
                 if ($saved['ok']) {
                    Student::update($id, [
                        'photo_path' => $saved['filename'],
                        'photo_blob' => $saved['photo_blob'] ?? null,
                        'photo_mime' => $saved['photo_mime'] ?? null,
                        'photo_hash' => $saved['photo_hash'] ?? null,
                        'thumbnail_blob' => $saved['thumbnail_blob'] ?? null
                    ]);
                 } else {
                    Auth::flash('error', 'Saved student but photo save failed: ' . $saved['error']);
                 }
            } elseif (!empty($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $saved = ImageService::saveStudentPhoto($_FILES['photo'], $id);
                if ($saved['ok']) {
                    $updateData = [
                        'photo_path' => $saved['filename'],
                        'photo_blob' => $saved['photo_blob'] ?? null,
                        'photo_mime' => $saved['photo_mime'] ?? null,
                        'photo_hash' => $saved['photo_hash'] ?? null,
                        'thumbnail_blob' => $saved['thumbnail_blob'] ?? null
                    ];
                    Student::update($id, $updateData);
                } else {
                    Auth::flash('error', 'Saved student but photo upload failed: ' . $saved['error']);
                    // We don't redirect here, just flash error and continue to list
                }
            }

            // Activity log
            require_once BASE_PATH . '/app/Services/ActivityLogger.php';
            ActivityLogger::log('create', 'student', $id, [
                'student_name' => $data['student_name'] ?? '',
                'roll_no' => $data['roll_no'] ?? '',
                'enrollment_no' => $data['enrollment_no'] ?? '',
            ]);

            Auth::flushOldInput(); // Clear old input on success
            Auth::flash('success', 'Student added successfully.');
            $this->redirect('/students');
        } catch (PDOException $e) {
            Auth::flash('error', 'Database error: ' . $e->getMessage());
            $this->redirect('/students/create');
        }
    }

    public function edit(): void
    {
        $this->requireAuth();
        if (Auth::isViewer()) {
            Auth::flash('error', 'Viewers cannot edit students.');
            $this->redirect('/students');
        }
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            Auth::flash('error', 'Invalid student id.');
            $this->redirect('/students');
        }
        $student = Student::find($id);
        if (!$student) {
            Auth::flash('error', 'Student not found.');
            $this->redirect('/students');
        }
        // Load Session model for database-driven sessions
        require_once BASE_PATH . '/app/Models/Session.php';
        
        $lookups = [
            'classes'        => Lookup::getClasses(true),
            'sections'       => Lookup::getSections(true),
            'sessions'       => Lookup::getSessions(), // Already active-only
            'categories'     => Lookup::getCategories(true),
            'familyCategories' => Lookup::getFamilyCategories(true),
        ];
        // Get active fields
        require_once BASE_PATH . '/app/Models/Field.php';
        Field::seedDefaults();
        $fields = Field::getAll(true);

        if (empty($fields)) {
            Auth::flash('error', 'No active fields found.');
        }

        $this->view('students/form.php', [
            'title'   => 'Edit Student | Shiori',
            'student' => $student,
            'lookups' => $lookups,
            'fields'  => $fields,
            'mode'    => 'edit',
        ]);
    }

    public function update(): void
    {
        $this->requireAuth();
        if (Auth::isViewer()) {
            Auth::flash('error', 'Viewers cannot edit students.');
            $this->redirect('/students');
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/students');
        }
        if (!CSRF::verify($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid session token.');
            $this->redirect('/students');
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            Auth::flash('error', 'Invalid student id.');
            $this->redirect('/students');
        }

        $student = Student::find($id);
        if (!$student) {
            Auth::flash('error', 'Student not found.');
            $this->redirect('/students');
        }

        $validation = Validator::validateStudent($_POST, $_FILES, true);
        if (!empty($validation['errors'])) {
            Auth::setOldInput($_POST); // Preserve form data
            Auth::flash('error', implode(' | ', $validation['errors']));
            $this->redirect('/students/edit?id=' . $id);
        }

        // Clear old input on successful validation
        Auth::flushOldInput();

        $data = $validation['data'];
        
        // Handle Custom Fields for Update
        require_once BASE_PATH . '/app/Models/Field.php';
        $allFields = Field::getAll(false);
        $customData = [];
        foreach ($allFields as $f) {
            if ($f['is_custom'] && isset($_POST[$f['name']])) {
                $customData[$f['name']] = trim($_POST[$f['name']]);
            }
        }
        $fullData = array_merge($data, $customData);

        try {
            Student::update($id, $fullData);

            // Handle Photo Update (Remove OR Update)
            if (isset($_POST['cropped_image']) && $_POST['cropped_image'] === 'remove') {
                // Remove photo
                if (!empty($student['photo_path'])) {
                    ImageService::deleteStudentPhoto($student['photo_path']);
                }
                Student::update($id, [
                    'photo_path' => null,
                    'photo_blob' => null,
                    'photo_mime' => null,
                    'photo_hash' => null,
                    'thumbnail_blob' => null
                ]);
            } elseif (!empty($_POST['cropped_image']) && strpos($_POST['cropped_image'], 'data:image') === 0) {
                 // Update with new cropped image
                 if (!empty($student['photo_path'])) {
                    ImageService::deleteStudentPhoto($student['photo_path']);
                 }
                 $saved = ImageService::saveFromBase64($_POST['cropped_image'], $id);
                 if ($saved['ok']) {
                    Student::update($id, [
                        'photo_path' => $saved['filename'],
                        'photo_blob' => $saved['photo_blob'] ?? null,
                        'photo_mime' => $saved['photo_mime'] ?? null,
                        'photo_hash' => $saved['photo_hash'] ?? null,
                        'thumbnail_blob' => $saved['thumbnail_blob'] ?? null
                    ]);
                 } else {
                    Auth::flash('error', 'Student updated but photo save failed: ' . $saved['error']);
                 }
            } elseif (!empty($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
                // Fallback to standard upload
                if (!empty($student['photo_path'])) {
                    ImageService::deleteStudentPhoto($student['photo_path']);
                }
                $saved = ImageService::saveStudentPhoto($_FILES['photo'], $id);
                if ($saved['ok']) {
                    Student::update($id, [
                        'photo_path' => $saved['filename'],
                        'photo_blob' => $saved['photo_blob'] ?? null,
                        'photo_mime' => $saved['photo_mime'] ?? null,
                        'photo_hash' => $saved['photo_hash'] ?? null,
                        'thumbnail_blob' => $saved['thumbnail_blob'] ?? null
                    ]);
                } else {
                    Auth::flash('error', 'Student updated but photo upload failed: ' . $saved['error']);
                    $this->redirect('/students');
                }
            }

            // Activity log
            require_once BASE_PATH . '/app/Services/ActivityLogger.php';
            ActivityLogger::log('update', 'student', $id, [
                'student_name' => $data['student_name'] ?? '',
                'roll_no' => $data['roll_no'] ?? '',
            ]);

            Auth::flushOldInput(); // Clear old input on success
            Auth::flash('success', 'Student updated successfully.');
            $this->redirect('/students');
        } catch (PDOException $e) {
            Auth::flash('error', 'Database error: ' . $e->getMessage());
            $this->redirect('/students/edit?id=' . $id);
        }
    }

    public function show(): void
    {
        $this->requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            Auth::flash('error', 'Invalid student id.');
            $this->redirect('/students');
        }
        $student = Student::find($id);
        if (!$student) {
            Auth::flash('error', 'Student not found.');
            $this->redirect('/students');
        }
        $this->view('students/view.php', [
            'title'   => 'View Student | Shiori',
            'student' => $student,
        ]);
    }

    public function destroy(): void
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/students');
        }
        if (!CSRF::verify($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid session token.');
            $this->redirect('/students');
        }
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            Auth::flash('error', 'Invalid student id.');
            $this->redirect('/students');
        }
        $student = Student::find($id);
        if (!$student) {
            Auth::flash('error', 'Student not found.');
            $this->redirect('/students');
        }

        // Role-based delete: only admin allowed
        if (!Auth::isAdmin()) {
            Auth::flash('error', 'You do not have permission to delete records.');
            $this->redirect('/students');
        }

        try {
            Student::delete($id);
            if (!empty($student['photo_path'])) {
                ImageService::deleteStudentPhoto($student['photo_path']);
            }

            // Activity log
            require_once BASE_PATH . '/app/Services/ActivityLogger.php';
            ActivityLogger::log('delete', 'student', $id, [
                'student_name' => $student['student_name'] ?? '',
                'roll_no' => $student['roll_no'] ?? '',
            ]);

            Auth::flash('success', 'Student deleted.');
            $this->redirect('/students');
        } catch (PDOException $e) {
            Auth::flash('error', 'Could not delete student: ' . $e->getMessage());
            $this->redirect('/students');
        }
    }

    public function import(): void
    {
        $this->requireAuth();
        if (Auth::isViewer()) {
            Auth::flash('error', 'Viewers cannot import data.');
            $this->redirect('/students');
        }
        $this->view('students/import.php', [
            'title' => 'Import Students | Shiori',
            'templateUrl' => BASE_URL . '/students/import-template'
        ]);
    }

    public function importProcess(): void
    {
        $this->requireAuth();
        if (Auth::isViewer()) {
            $this->redirect('/students');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['csv_file'])) {
            $this->redirect('/students/import');
        }

        $file = $_FILES['csv_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            Auth::flash('error', 'File upload failed.');
            $this->redirect('/students/import');
        }

        // Use enterprise import service
        require_once BASE_PATH . '/app/Services/CSVImportService.php';
        
        try {
            $result = CSVImportService::importFile($file['tmp_name']);
            
            // Flash success message
            if ($result->isFullSuccess()) {
                Auth::flash('success', $result->toFlashMessage());
            } elseif ($result->isPartialSuccess()) {
                Auth::flash('success', $result->toFlashMessage());
                if (!empty($result->errors)) {
                    Auth::flash('error', 'Import errors: ' . $result->getErrorSummary(5));
                }
            } elseif ($result->isCompleteFailure()) {
                Auth::flash('error', $result->toFlashMessage() . ' ' . $result->getErrorSummary(5));
            } else {
                Auth::flash('error', 'No valid data found in CSV file');
            }
        } catch (Exception $e) {
            Auth::flash('error', 'Import failed: ' . $e->getMessage());
        }
        
        $this->redirect('/students');
    }

    /**
     * Import students from CSV URL
     */
    public function importFromUrl(): void
    {
        $this->requireAuth();
        if (Auth::isViewer()) {
            Auth::flash('error', 'Viewers cannot import data.');
            $this->redirect('/students');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/students/import');
        }
        
        if (!CSRF::verify($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid session token.');
            $this->redirect('/students/import');
        }
        
        $url = trim($_POST['csv_url'] ?? '');
        
        if (empty($url)) {
            Auth::flash('error', 'CSV URL is required.');
            $this->redirect('/students/import');
        }
        
        // Validate URL format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            Auth::flash('error', 'Invalid URL format.');
            $this->redirect('/students/import');
        }
        
        // Download CSV content with timeout and size limit
        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'user_agent' => 'Shiori SIS/1.0'
            ]
        ]);
        
        $csvContent = @file_get_contents($url, false, $context);
        
        if ($csvContent === false) {
            Auth::flash('error', 'Could not download CSV from URL. Please check the URL and try again.');
            $this->redirect('/students/import');
        }
        
        // Check file size (max 5MB)
        if (strlen($csvContent) > 5 * 1024 * 1024) {
            Auth::flash('error', 'CSV file too large (max 5MB).');
            $this->redirect('/students/import');
        }
        
        // Save to temporary file
        $tmpFile = tempnam(sys_get_temp_dir(), 'csv_import_');
        file_put_contents($tmpFile, $csvContent);
        
        // Process the CSV file (reuse existing logic)
        $handle = fopen($tmpFile, 'r');
        if (!$handle) {
            @unlink($tmpFile);
            Auth::flash('error', 'Could not process CSV file.');
            $this->redirect('/students/import');
        }
        
        // Skip header
        fgetcsv($handle);
        
        $count = 0;
        $errors = [];
        $rowNum = 1;
        
        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count($row) < 6) {
                $errors[] = "Row $rowNum: Not enough columns.";
                continue;
            }
            
            $data = [
                'roll_no' => $row[0],
                'enrollment_no' => $row[1],
                'student_name' => $row[2],
                'father_name' => $row[3],
                'class_id' => (int)$row[4],
                'section_id' => (int)$row[5],
                'session' => date('Y') . '-' . (date('Y')+1),
                'category_id' => 1,
                'fcategory_id' => 1,
            ];
            
            try {
                Student::create($data);
                $count++;
            } catch (Exception $e) {
                $errors[] = "Row $rowNum: " . $e->getMessage();
            }
        }
        fclose($handle);
        @unlink($tmpFile);
        
        if ($count > 0) {
            Auth::flash('success', "Imported $count students from URL.");
        }
        if (!empty($errors)) {
            $errStr = implode(' | ', array_slice($errors, 0, 5));
            if (count($errors) > 5) $errStr .= '... (and more)';
            Auth::flash('error', "Errors: " . $errStr);
        }
        
        $this->redirect('/students');
    }

    public function downloadTemplate(): void
    {
        $this->requireAuth();
        
        require_once BASE_PATH . '/app/Services/CSVTemplateService.php';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="student_import_template.csv"');
        
        $out = fopen('php://output', 'w');
        
        // Get dynamic headers based on active fields
        $headers = CSVTemplateService::generateHeaders();
        fputcsv($out, $headers);
        
        // Get example row
        $exampleRow = CSVTemplateService::generateExampleRow();
        fputcsv($out, $exampleRow);
        
        fclose($out);
        exit;
    }

    /**
 * Export CSV of students.
 * Use ?all=1 to export entire table; otherwise paging parameters are used.
 */
    public function export(): void
    {
        $this->requireAuth();
        $all = (int)($_GET['all'] ?? 0);
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = (int)($_GET['per_page'] ?? 10);
        if (!in_array($perPage, [10,25,50], true)) $perPage = 10;

        $filename = 'students_export_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');

        // CSV header - User requested sequence
        fputcsv($out, [
            'Roll No','Enrollment No','Student Name','Date of Birth','B.form','Father Name',
            'CNIC','Mobile','Class Name','Section','Session','Father Occupation','BPS',
            'Category','Family Category','Email','Religion','Caste','Address','Created At','Updated At'
        ]);

        $pdo = DB::get();

        if ($all === 1) {
            $stmt = $pdo->query("
                SELECT s.*, c.name AS class_name, sec.name AS section_name, cat.name AS category_name, fc.name AS fcategory_name
                FROM students s
                LEFT JOIN classes c ON s.class_id = c.id
                LEFT JOIN sections sec ON s.section_id = sec.id
                LEFT JOIN categories cat ON s.category_id = cat.id
                LEFT JOIN family_categories fc ON s.fcategory_id = fc.id
                ORDER BY s.id DESC
            ");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                fputcsv($out, [
                    $row['roll_no'],
                    $row['enrollment_no'],
                    $row['student_name'] ?? '',
                    $row['dob'] ?? '',
                    $row['b_form'] ?? '',
                    $row['father_name'] ?? '',
                    $row['cnic'] ?? '',
                    $row['mobile'] ?? '',
                    $row['class_name'] ?? ($row['class_id'] ?? ''),
                    $row['section_name'] ?? ($row['section_id'] ?? ''),
                    $row['session'] ?? '',
                    $row['father_occupation'] ?? '',
                    $row['bps'] ?? '',
                    $row['category_name'] ?? '',
                    $row['fcategory_name'] ?? '',
                    $row['email'] ?? '',
                    $row['religion'] ?? '',
                    $row['caste'] ?? '',
                    $row['address'] ?? '',
                    $row['created_at'] ?? '',
                    $row['updated_at'] ?? ''
                ]);
            }
        } else {
            $offset = ($page - 1) * $perPage;
            $stmt = $pdo->prepare("
                SELECT s.*, c.name AS class_name, sec.name AS section_name, cat.name AS category_name, fc.name AS fcategory_name
                FROM students s
                LEFT JOIN classes c ON s.class_id = c.id
                LEFT JOIN sections sec ON s.section_id = sec.id
                LEFT JOIN categories cat ON s.category_id = cat.id
                LEFT JOIN family_categories fc ON s.fcategory_id = fc.id
                ORDER BY s.id DESC
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                fputcsv($out, [
                    $row['roll_no'],
                    $row['enrollment_no'],
                    $row['student_name'] ?? '',
                    $row['dob'] ?? '',
                    $row['b_form'] ?? '',
                    $row['father_name'] ?? '',
                    $row['cnic'] ?? '',
                    $row['mobile'] ?? '',
                    $row['class_name'] ?? ($row['class_id'] ?? ''),
                    $row['section_name'] ?? ($row['section_id'] ?? ''),
                    $row['session'] ?? '',
                    $row['father_occupation'] ?? '',
                    $row['bps'] ?? '',
                    $row['category_name'] ?? '',
                    $row['fcategory_name'] ?? '',
                    $row['email'] ?? '',
                    $row['religion'] ?? '',
                    $row['caste'] ?? '',
                    $row['address'] ?? '',
                    $row['created_at'] ?? '',
                    $row['updated_at'] ?? ''
                ]);
            }
        }

        fclose($out);
        exit;
    }



    /**
     * Print-friendly student profile view (standalone)
     * GET /students/print?id=#
     */
    public function print(): void
    {
        $this->requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            Auth::flash('error', 'Invalid student id.');
            $this->redirect('/students');
        }
        $student = Student::find($id);
        if (!$student) {
            Auth::flash('error', 'Student not found.');
            $this->redirect('/students');
        }

        // Render a standalone print-friendly HTML (not using the main layout)
        $appCfg = require BASE_PATH . '/config/app.php';
        $baseUrl = rtrim($appCfg['base_url'], '/');
        // Make $student & $baseUrl available to the view file
        include BASE_PATH . '/app/Views/students/print.php';
        exit;
    }
    
    /**
     * Serve student photo with filesystem-first, database-fallback strategy
     */
    public function servePhoto(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $size = $_GET['size'] ?? 'thumb';
        
        if ($id <= 0) {
            $this->serveDefaultAvatar();
            return;
        }
        
        $student = Student::find($id);
        if (!$student) {
            $this->serveDefaultAvatar();
            return;
        }
        
        $uploadsDir = BASE_PATH . '/public/uploads/students';
        
        // Try filesystem cache first (fastest)
        if (!empty($student['photo_path'])) {
            if ($size === 'thumb') {
                $base = pathinfo($student['photo_path'], PATHINFO_FILENAME);
                $thumbPath = $uploadsDir . DIRECTORY_SEPARATOR . 'thumb_' . $base . '.jpg';
                if (file_exists($thumbPath)) {
                    ImageService::serveImage(file_get_contents($thumbPath), 'image/jpeg');
                }
            }
            
            $fullPath = $uploadsDir . DIRECTORY_SEPARATOR . $student['photo_path'];
            if (file_exists($fullPath)) {
                $mime = $student['photo_mime'] ?? 'image/jpeg';
                ImageService::serveImage(file_get_contents($fullPath), $mime);
            }
        }
        
        // Fallback to database BLOB (portability)
        if (!empty($student['photo_blob'])) {
            // Regenerate filesystem cache for future requests
            ImageService::regenerateFromBlob($id, $student['photo_blob'], $student['photo_mime'] ?? 'image/jpeg');
            
            // Serve appropriate version
            if ($size === 'thumb' && !empty($student['thumbnail_blob'])) {
                ImageService::serveImage($student['thumbnail_blob'], 'image/jpeg');
            } else {
                ImageService::serveImage($student['photo_blob'], $student['photo_mime'] ?? 'image/jpeg');
            }
        }
        
        // Final fallback: default avatar
        $this->serveDefaultAvatar();
    }
    
    /**
     * Serve default avatar SVG
     */
    private function serveDefaultAvatar(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200">
            <rect fill="#e2e8f0" width="200" height="200"/>
            <circle cx="100" cy="80" r="35" fill="#94a3b8"/>
            <path d="M100 120 Q70 140 50 180 H150 Q130 140 100 120 Z" fill="#94a3b8"/>
        </svg>';
        
        header('Content-Type: image/svg+xml');
        header('Cache-Control: public, max-age=86400');
        echo $svg;
        exit;
    }
}
