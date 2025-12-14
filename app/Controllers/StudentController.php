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
            'classes'        => Lookup::getClasses(),
            'sections'       => Lookup::getSections(),
            'sessions'       => Lookup::getSessions(), // From database
            'categories'     => Lookup::getCategories(),
            'familyCategories' => Lookup::getFamilyCategories(),
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

            if (!empty($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $saved = ImageService::saveStudentPhoto($_FILES['photo'], $id);
                if ($saved['ok']) {
                    $updateData = ['photo_path' => $saved['filename']];
                    if (!empty($saved['thumbnail_blob'])) {
                        $updateData['thumbnail_blob'] = $saved['thumbnail_blob'];
                    }
                    Student::update($id, $updateData);
                } else {
                    Auth::flash('error', 'Saved student but photo upload failed: ' . $saved['error']);
                    $this->redirect('/students');
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
            'classes'        => Lookup::getClasses(),
            'sections'       => Lookup::getSections(),
            'sessions'       => Lookup::getSessions(), // From database
            'categories'     => Lookup::getCategories(),
            'familyCategories' => Lookup::getFamilyCategories(),
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

            if (!empty($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
                if (!empty($student['photo_path'])) {
                    ImageService::deleteStudentPhoto($student['photo_path']);
                }
                $saved = ImageService::saveStudentPhoto($_FILES['photo'], $id);
                if ($saved['ok']) {
                    Student::update($id, ['photo_path' => $saved['filename']]);
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

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            Auth::flash('error', 'Could not open file.');
            $this->redirect('/students/import');
        }

        // Skip header row
        fgetcsv($handle);

        $count = 0;
        $errors = [];
        $rowNum = 1;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            
            // Expected columns (20 total): Roll No, Enrollment No, Student Name, Class ID, Section ID,
            // Session, DOB, B-Form, Father Name, Father Occupation, CNIC, Mobile, Email,
            // Category ID, Family Category ID, BPS, Religion, Caste, Domicile, Address
            if (count($row) < 20) {
                $errors[] = "Row $rowNum: Expected 20 columns, got " . count($row);
                $skipped++;
                continue;
            }

            // Map CSV columns to database fields
            $data = [
                'roll_no' => trim($row[0]),
                'enrollment_no' => trim($row[1]),
                'student_name' => trim($row[2]),
                'class_id' => (int)$row[3],
                'section_id' => (int)$row[4],
                'session' => trim($row[5]) ?: (date('Y') . '-' . (date('Y')+1)),
                'dob' => trim($row[6]) ?: null,
                'b_form' => trim($row[7]) ?: null,
                'father_name' => trim($row[8]),
                'father_occupation' => trim($row[9]) ?: null,
                'cnic' => trim($row[10]) ?: null,
                'mobile' => trim($row[11]) ?: null,
                'email' => trim($row[12]) ?: null,
                'category_id' => (int)$row[13] ?: 1,
                'fcategory_id' => (int)$row[14] ?: 1,
                'bps' => trim($row[15]) ? (int)$row[15] : null,
                'religion' => trim($row[16]) ?: null,
                'caste' => trim($row[17]) ?: null,
                'domicile' => trim($row[18]) ?: null,
                'address' => trim($row[19]) ?: null,
            ];

            // Basic validation
            if (empty($data['student_name']) || empty($data['father_name'])) {
                $errors[] = "Row $rowNum: Student Name and Father Name are required";
                $skipped++;
                continue;
            }

            try {
                Student::create($data);
                $count++;
            } catch (Exception $e) {
                $errors[] = "Row $rowNum: " . $e->getMessage();
                $skipped++;
            }
        }
        fclose($handle);

        // Build success/error message
        $messages = [];
        if ($count > 0) {
            $messages[] = "Successfully imported $count student(s)";
        }
        if ($skipped > 0) {
            $messages[] = "Skipped $skipped row(s) due to errors";
        }
        
        if (!empty($messages)) {
            Auth::flash('success', implode('. ', $messages));
        }
        
        if (!empty($errors)) {
            // Limit errors to avoid huge session cookie
            $errStr = implode(' | ', array_slice($errors, 0, 5));
            if (count($errors) > 5) $errStr .= '... (and ' . (count($errors) - 5) . ' more)';
            Auth::flash('error', "Import errors: " . $errStr);
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
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="student_import_template.csv"');
        
        $out = fopen('php://output', 'w');
        
        // Complete headers matching all database columns in proper order
        fputcsv($out, [
            'Roll No', 'Enrollment No', 'Student Name', 'Class ID', 'Section ID',
            'Session', 'DOB', 'B-Form', 'Father Name', 'Father Occupation',
            'CNIC', 'Mobile', 'Email', 'Category ID', 'Family Category ID',
            'BPS', 'Religion', 'Caste', 'Domicile', 'Address'
        ]);
        
        // Example row with sample data
        fputcsv($out, [
            '101',                    // Roll No
            'ENR-2025-001',          // Enrollment No
            'John Doe',              // Student Name
            '1',                     // Class ID (must exist in classes table)
            '1',                     // Section ID (must exist in sections table)
            '2025-2026',             // Session
            '2010-01-15',            // DOB (YYYY-MM-DD)
            '1234567890123',         // B-Form (13 digits)
            'Richard Doe',           // Father Name
            'Engineer',              // Father Occupation
            '1234567890123',         // CNIC (13 digits)
            '03001234567',           // Mobile
            'john.doe@example.com',  // Email
            '1',                     // Category ID (must exist in categories table)
            '1',                     // Family Category ID (must exist in family_categories table)
            '17',                    // BPS
            'Islam',                 // Religion
            '',                      // Caste (optional)
            'Karachi',               // Domicile
            '123 Main Street'        // Address
        ]);
        
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

        // CSV header - added BPS, Religion, Caste, Domicile
        fputcsv($out, [
            'ID','Roll No','Enrollment No','Class','Section','Name','DOB','B.form',
            'BPS','Religion','Caste','Domicile',
            'Father Name','CNIC','Mobile','Email','Category','Family Category','Address','Photo Path','Created At','Updated At'
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
                    $row['id'],
                    $row['roll_no'],
                    $row['enrollment_no'],
                    $row['class_name'] ?? $row['class_id'],
                    $row['section_name'] ?? $row['section_id'],
                    $row['student_name'] ?? '',
                    $row['dob'] ?? '',
                    $row['b_form'] ?? '',

                    // NEW fields
                    $row['bps'] ?? '',
                    $row['religion'] ?? '',
                    $row['caste'] ?? '',
                    $row['domicile'] ?? '',

                    $row['father_name'] ?? '',
                    $row['cnic'] ?? '',
                    $row['mobile'] ?? '',
                    $row['email'] ?? '',
                    $row['category_name'] ?? '',
                    $row['fcategory_name'] ?? '',
                    $row['address'] ?? '',
                    $row['photo_path'] ?? '',
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
                    $row['id'],
                    $row['roll_no'],
                    $row['enrollment_no'],
                    $row['class_name'] ?? $row['class_id'],
                    $row['section_name'] ?? $row['section_id'],
                    $row['student_name'] ?? '',
                    $row['dob'] ?? '',
                    $row['b_form'] ?? '',

                    // NEW fields
                    $row['bps'] ?? '',
                    $row['religion'] ?? '',
                    $row['caste'] ?? '',
                    $row['domicile'] ?? '',

                    $row['father_name'] ?? '',
                    $row['cnic'] ?? '',
                    $row['mobile'] ?? '',
                    $row['email'] ?? '',
                    $row['category_name'] ?? '',
                    $row['fcategory_name'] ?? '',
                    $row['address'] ?? '',
                    $row['photo_path'] ?? '',
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
}
