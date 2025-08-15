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
        return $row ?: null;
    }

    public static function findAll(int $limit = 50, int $offset = 0): array
    {
        $pdo = DB::get();
        $stmt = $pdo->prepare("
            SELECT s.id, s.roll_no, s.enrollment_no, s.student_name, s.class_id, s.section_id,
                   c.name AS class_name, sec.name AS section_name, s.photo_path
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            LEFT JOIN sections sec ON s.section_id = sec.id
            ORDER BY s.id DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create(array $data): int
    {
        $pdo = DB::get();
        $stmt = $pdo->prepare("
            INSERT INTO students
                (roll_no, enrollment_no, class_id, section_id, student_name, dob, b_form,
                 father_name, cnic, mobile, address, father_occupation, category_id, fcategory_id, email, photo_path)
            VALUES
                (:roll_no, :enrollment_no, :class_id, :section_id, :student_name, :dob, :b_form,
                 :father_name, :cnic, :mobile, :address, :father_occupation, :category_id, :fcategory_id, :email, :photo_path)
        ");
        $stmt->execute([
            ':roll_no' => $data['roll_no'] ?? '',
            ':enrollment_no' => $data['enrollment_no'] ?? '',
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
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $pdo = DB::get();

        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $k => $v) {
            // allow only known fields
            if (in_array($k, ['roll_no','enrollment_no','class_id','section_id','student_name','dob','b_form','father_name','cnic','mobile','address','father_occupation','category_id','fcategory_id','email','photo_path'], true)) {
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
