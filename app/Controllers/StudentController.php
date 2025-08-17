<?php
class StudentController extends Controller
{
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
        $lookups = [
            'classes'        => Lookup::getClasses(),
            'sections'       => Lookup::getSections(),
            'categories'     => Lookup::getCategories(),
            'familyCategories' => Lookup::getFamilyCategories(),
        ];
        $this->view('students/form.php', [
            'title'   => 'Add Student | Shiori',
            'student' => null,
            'lookups' => $lookups,
            'mode'    => 'create',
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/students/create');
        }
        if (!CSRF::verify($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid session token.');
            $this->redirect('/students/create');
        }

        $validation = Validator::validateStudent($_POST, $_FILES);
        if (!empty($validation['errors'])) {
            Auth::flash('error', implode(' | ', $validation['errors']));
            $this->redirect('/students/create');
        }

        $data = $validation['data'];
        try {
            $id = Student::create($data);

            if (!empty($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $saved = ImageService::saveStudentPhoto($_FILES['photo'], $id);
                if ($saved['ok']) {
                    Student::update($id, ['photo_path' => $saved['filename']]);
                } else {
                    Auth::flash('error', 'Saved student but photo upload failed: ' . $saved['error']);
                    $this->redirect('/students');
                }
            }

            // Activity log
            $user = Auth::user();
            ActivityLog::log($user['id'] ?? null, 'create', 'student', $id, json_encode([
                'student_name' => $data['student_name'] ?? '',
                'roll_no' => $data['roll_no'] ?? '',
                'enrollment_no' => $data['enrollment_no'] ?? '',
            ]));

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
        $lookups = [
            'classes'        => Lookup::getClasses(),
            'sections'       => Lookup::getSections(),
            'categories'     => Lookup::getCategories(),
            'familyCategories' => Lookup::getFamilyCategories(),
        ];
        $this->view('students/form.php', [
            'title'   => 'Edit Student | Shiori',
            'student' => $student,
            'lookups' => $lookups,
            'mode'    => 'edit',
        ]);
    }

    public function update(): void
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

        $validation = Validator::validateStudent($_POST, $_FILES, true);
        if (!empty($validation['errors'])) {
            Auth::flash('error', implode(' | ', $validation['errors']));
            $this->redirect('/students/edit?id=' . $id);
        }

        $data = $validation['data'];
        try {
            Student::update($id, $data);

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
            $user = Auth::user();
            ActivityLog::log($user['id'] ?? null, 'update', 'student', $id, json_encode([
                'student_name' => $data['student_name'] ?? '',
                'roll_no' => $data['roll_no'] ?? '',
            ]));

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
        $user = Auth::user();
        if (empty($user['role']) || $user['role'] !== 'admin') {
            Auth::flash('error', 'You do not have permission to delete records.');
            $this->redirect('/students');
        }

        try {
            Student::delete($id);
            if (!empty($student['photo_path'])) {
                ImageService::deleteStudentPhoto($student['photo_path']);
            }

            // Activity log
            ActivityLog::log($user['id'] ?? null, 'delete', 'student', $id, json_encode([
                'student_name' => $student['student_name'] ?? '',
                'roll_no' => $student['roll_no'] ?? '',
            ]));

            Auth::flash('success', 'Student deleted.');
            $this->redirect('/students');
        } catch (PDOException $e) {
            Auth::flash('error', 'Could not delete student: ' . $e->getMessage());
            $this->redirect('/students');
        }
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
