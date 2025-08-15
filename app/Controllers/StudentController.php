<?php
class StudentController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = (int)($_GET['per_page'] ?? 10);
        if (!in_array($perPage, [10,25,50], true)) $perPage = 10;

        $total = Student::countAll();
        $students = Student::paginate($page, $perPage);

        $this->view('students/list.php', [
            'title' => 'Students | Shiori',
            'students' => $students,
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
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

            // handle photo replacement
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
            Auth::flash('success', 'Student deleted.');
            $this->redirect('/students');
        } catch (PDOException $e) {
            Auth::flash('error', 'Could not delete student: ' . $e->getMessage());
            $this->redirect('/students');
        }
    }
}
