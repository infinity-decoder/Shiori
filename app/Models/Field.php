<?php

class Field
{
    public static function getAll(bool $onlyActive = false): array
    {
        $pdo = DB::get();
        $sql = "SELECT * FROM fields";
        if ($onlyActive) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY order_index ASC";
        return $pdo->query($sql)->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $pdo = DB::get();
        $stmt = $pdo->prepare("SELECT * FROM fields WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = DB::get();
        $stmt = $pdo->prepare("
            INSERT INTO fields (name, label, type, options, is_active, is_custom, section, order_index)
            VALUES (:name, :label, :type, :options, :is_active, :is_custom, :section, :order_index)
        ");
        $stmt->execute([
            ':name' => $data['name'],
            ':label' => $data['label'],
            ':type' => $data['type'],
            ':options' => $data['options'] ?? null,
            ':is_active' => $data['is_active'] ?? 1,
            ':is_custom' => $data['is_custom'] ?? 1,
            ':section' => $data['section'] ?? 'main',
            ':order_index' => $data['order_index'] ?? 99,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $pdo = DB::get();
        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $k => $v) {
            if (in_array($k, ['label', 'type', 'options', 'is_active', 'order_index'], true)) {
                $fields[] = "`$k` = :$k";
                $params[":$k"] = $v;
            }
        }

        if (empty($fields)) return;

        $sql = "UPDATE fields SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    public static function toggle(int $id): void
    {
        $pdo = DB::get();
        $stmt = $pdo->prepare("UPDATE fields SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);
    }
    
    public static function delete(int $id): void
    {
        $pdo = DB::get();
        // Only custom fields can be deleted
        $stmt = $pdo->prepare("DELETE FROM fields WHERE id = ? AND is_custom = 1");
        $stmt->execute([$id]);
    }

    public static function seedDefaults(): void
    {
        $pdo = DB::get();
        // Check if any fields exist
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM fields");
        if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
            return;
        }

        // Default fields with logical order and sections
        // Order follows: Roll/Enrollment → Student Info → Father Info → Family/Home → Photo
        $defaults = [
            // Student Identification (1-8)
            ['roll_no', 'Roll No', 'text', 'main', 1],
            ['enrollment_no', 'Enrollment No', 'text', 'main', 2],
            ['student_name', 'Student Name', 'text', 'main', 3],
            ['class_id', 'Class', 'select', 'main', 4],
            ['section_id', 'Section', 'select', 'main', 5],
            ['session', 'Session', 'select', 'main', 6],
            ['dob', 'Date of Birth', 'date', 'main', 7],
            ['b_form', 'B-Form', 'text', 'main', 8],
            
            // Father Information (9-13)
            ['father_name', 'Father Name', 'text', 'main', 9],
            ['father_occupation', 'Father Occupation', 'text', 'main', 10],
            ['cnic', 'CNIC', 'text', 'main', 11],
            ['mobile', 'Mobile', 'text', 'main', 12],
            ['email', 'Email', 'text', 'main', 13],
            
            // Category & Classification (14-19)
            ['category_id', 'Category', 'select', 'main', 14],
            ['fcategory_id', 'Family Category', 'select', 'main', 15],
            ['bps', 'BPS', 'number', 'main', 16],
            ['religion', 'Religion', 'text', 'main', 17],
            ['caste', 'Caste', 'text', 'main', 18],
            ['domicile', 'Domicile', 'text', 'main', 19],
            
            // Address (20)
            ['address', 'Address', 'textarea', 'main', 20],
            
            // Photo (sidebar, order 99)
            ['photo_path', 'Photo', 'file', 'sidebar', 99],
        ];

        $stmt = $pdo->prepare("
            INSERT INTO fields (name, label, type, section, is_active, is_custom, order_index)
            VALUES (?, ?, ?, ?, 1, 0, ?)
        ");

        foreach ($defaults as $d) {
            $stmt->execute([$d[0], $d[1], $d[2], $d[3], $d[4]]);
        }
    }
}
