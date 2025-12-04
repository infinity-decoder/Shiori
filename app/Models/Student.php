<?php
class Student
{
    public static function find(int $id): ?array
    {
        $pdo = DB::get();
        $stmt = $pdo->prepare("
            SELECT s.*,
                   c.name AS class_name,
                   sec.name AS section_name,
                   cat.name AS category_name,
                   fc.name  AS fcategory_name
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            LEFT JOIN sections sec ON s.section_id = sec.id
            LEFT JOIN categories cat ON s.category_id = cat.id
            LEFT JOIN family_categories fc ON s.fcategory_id = fc.id
            WHERE s.id = ? LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            // Attach meta
            $meta = self::getMeta($id);
            $row = array_merge($row, $meta);
        }
        
        return $row ?: null;
    }

    public static function getMeta(int $studentId): array
    {
        $pdo = DB::get();
        $stmt = $pdo->prepare("
            SELECT f.name, sm.value 
            FROM student_meta sm
            JOIN fields f ON sm.field_id = f.id
            WHERE sm.student_id = ?
        ");
        $stmt->execute([$studentId]);
        $meta = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $meta[$row['name']] = $row['value'];
        }
        return $meta;
    }

    public static function saveMeta(int $studentId, array $data): void
    {
        $pdo = DB::get();
        // Get all custom fields
        $stmt = $pdo->query("SELECT id, name FROM fields WHERE is_custom = 1");
        $fields = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // id => name

        foreach ($fields as $fieldId => $fieldName) {
            if (isset($data[$fieldName])) {
                $value = $data[$fieldName];
                $stmt = $pdo->prepare("
                    INSERT INTO student_meta (student_id, field_id, value)
                    VALUES (:sid, :fid, :val)
                    ON DUPLICATE KEY UPDATE value = :val
                ");
                $stmt->execute([
                    ':sid' => $studentId,
                    ':fid' => $fieldId,
                    ':val' => $value
                ]);
            }
        }
    }

        public static function paginate(int $page = 1, int $perPage = 10, array $filters = [], string $sort = 'id_desc'): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        $pdo = DB::get();

        $where = [];
        $params = [];

        if (!empty($filters['class_id'])) {
            $where[] = 's.class_id = :class_id';
            $params[':class_id'] = (int)$filters['class_id'];
        }
        if (!empty($filters['section_id'])) {
            $where[] = 's.section_id = :section_id';
            $params[':section_id'] = (int)$filters['section_id'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(s.student_name LIKE :q OR s.father_name LIKE :q OR s.enrollment_no LIKE :q OR s.roll_no LIKE :q)';
            $params[':q'] = '%' . $filters['q'] . '%';
        }

        $whereSql = '';
        if (!empty($where)) {
            $whereSql = 'WHERE ' . implode(' AND ', $where);
        }

        // Determine ORDER BY clause
        switch ($sort) {
            case 'name_asc':
                $order = 's.student_name ASC';
                break;
            case 'name_desc':
                $order = 's.student_name DESC';
                break;
            case 'roll_asc':
                $order = 's.roll_no ASC';
                break;
            case 'roll_desc':
                $order = 's.roll_no DESC';
                break;
            case 'id_asc':
                $order = 's.id ASC';
                break;
            case 'id_desc':
            default:
                $order = 's.id DESC';
                break;
        }

        $sql = "
            SELECT s.id, s.roll_no, s.enrollment_no, s.session, s.student_name, s.class_id, s.section_id,
                   s.father_name, s.mobile,
                   c.name AS class_name, sec.name AS section_name, s.photo_path
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            LEFT JOIN sections sec ON s.section_id = sec.id
            {$whereSql}
            ORDER BY {$order}
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $pdo->prepare($sql);

        // bind filter params
        foreach ($params as $k => $v) {
            if (is_int($v) || ctype_digit((string)$v)) {
                $stmt->bindValue($k, (int)$v, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($k, $v, PDO::PARAM_STR);
            }
        }

        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public static function countAll(): int
    {
        $pdo = DB::get();
        $stmt = $pdo->query("SELECT COUNT(*) FROM students");
        return (int)$stmt->fetchColumn();
    }

        public static function countFiltered(array $filters = []): int
    {
        $pdo = DB::get();
        $where = [];
        $params = [];

        if (!empty($filters['class_id'])) {
            $where[] = 's.class_id = :class_id';
            $params[':class_id'] = (int)$filters['class_id'];
        }
        if (!empty($filters['section_id'])) {
            $where[] = 's.section_id = :section_id';
            $params[':section_id'] = (int)$filters['section_id'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(s.student_name LIKE :q OR s.father_name LIKE :q OR s.enrollment_no LIKE :q OR s.roll_no LIKE :q)';
            $params[':q'] = '%' . $filters['q'] . '%';
        }

        $whereSql = '';
        if (!empty($where)) {
            $whereSql = 'WHERE ' . implode(' AND ', $where);
        }

        $sql = "SELECT COUNT(*) FROM students s {$whereSql}";
        $stmt = $pdo->prepare($sql);

        foreach ($params as $k => $v) {
            if (is_int($v) || ctype_digit((string)$v)) {
                $stmt->bindValue($k, (int)$v, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($k, $v, PDO::PARAM_STR);
            }
        }

        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public static function create(array $data): int
{
    $pdo = DB::get();
    $stmt = $pdo->prepare("
        INSERT INTO students
            (roll_no, enrollment_no, session, class_id, section_id, student_name, dob, b_form,
             father_name, cnic, mobile, address, father_occupation, category_id, fcategory_id, email, photo_path,
             bps, religion, caste, domicile)
        VALUES
            (:roll_no, :enrollment_no, :session, :class_id, :section_id, :student_name, :dob, :b_form,
             :father_name, :cnic, :mobile, :address, :father_occupation, :category_id, :fcategory_id, :email, :photo_path,
             :bps, :religion, :caste, :domicile)
    ");
    $stmt->execute([
        ':roll_no' => $data['roll_no'] ?? '',
        ':enrollment_no' => $data['enrollment_no'] ?? '',
        ':session' => $data['session'] ?? null,
        ':class_id' => $data['class_id'],
        ':section_id' => $data['section_id'],
        ':student_name' => $data['student_name'],
        ':dob' => $data['dob'] ?: null,
        ':b_form' => $data['b_form'] ?? null,
        ':father_name' => $data['father_name'],
        ':cnic' => $data['cnic'] ?? null,
        ':mobile' => $data['mobile'] ?? null,
        ':address' => $data['address'] ?? null,
        ':father_occupation' => $data['father_occupation'] ?? null,
        ':category_id' => $data['category_id'],
        ':fcategory_id' => $data['fcategory_id'],
        ':email' => $data['email'] ?? null,
        ':photo_path' => $data['photo_path'] ?? null,
        ':bps' => $data['bps'] ?? null,
        ':religion' => $data['religion'] ?? null,
        ':caste' => $data['caste'] ?? null,
        ':domicile' => $data['domicile'] ?? null,
    ]);
    $id = (int)$pdo->lastInsertId();
    self::saveMeta($id, $data);
    return $id;
}


    public static function update(int $id, array $data): void
    {
        $pdo = DB::get();

        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $k => $v) {
        if (in_array($k, [
            'roll_no','enrollment_no','session','class_id','section_id','student_name','dob',
            'b_form','father_name','cnic','mobile','address','father_occupation',
            'category_id','fcategory_id','email','photo_path',
            'bps','religion','caste','domicile'
        ], true)) {
            $fields[] = "`$k` = :$k";
            $params[":$k"] = ($v === '') ? null : $v;
        }
    }


        if (empty($fields)) {
            return;
        }

        $sql = "UPDATE students SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        self::saveMeta($id, $data);
    }

    public static function delete(int $id): void
    {
        $pdo = DB::get();
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$id]);
    }

    public static function findByEnrollment(string $enrollment): ?array
    {
        $pdo = DB::get();
        $stmt = $pdo->prepare("SELECT * FROM students WHERE enrollment_no = ? LIMIT 1");
        $stmt->execute([$enrollment]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function findByRoll(string $roll): ?array
    {
        $pdo = DB::get();
        $stmt = $pdo->prepare("SELECT * FROM students WHERE roll_no = ? LIMIT 1");
        $stmt->execute([$roll]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
