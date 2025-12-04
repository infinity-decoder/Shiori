<?php
// public/fix_db.php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/app/Core/DB.php';

try {
    $pdo = DB::get();
    echo "Connected to database.<br>";

    // 1. Create Activity Logs Table
    $sql1 = "CREATE TABLE IF NOT EXISTS `activity_logs` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `action` varchar(50) NOT NULL,
      `entity_type` varchar(50) NOT NULL,
      `entity_id` int(11) NOT NULL,
      `details` json DEFAULT NULL,
      `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql1);
    echo "Checked/Created activity_logs table.<br>";

    // 2. Create Fields Table
    $sql2 = "CREATE TABLE IF NOT EXISTS `fields` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(100) NOT NULL,
      `label` varchar(100) NOT NULL,
      `type` varchar(50) NOT NULL DEFAULT 'text',
      `options` text,
      `is_active` tinyint(1) DEFAULT 1,
      `is_custom` tinyint(1) DEFAULT 0,
      `section` varchar(50) DEFAULT 'main',
      `order_index` int(11) DEFAULT 0,
      PRIMARY KEY (`id`),
      UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql2);
    echo "Checked/Created fields table.<br>";

    // 3. Create Student Meta Table
    $sql3 = "CREATE TABLE IF NOT EXISTS `student_meta` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `student_id` int(11) NOT NULL,
      `field_id` int(11) NOT NULL,
      `value` text,
      PRIMARY KEY (`id`),
      UNIQUE KEY `student_field` (`student_id`, `field_id`),
      KEY `student_id` (`student_id`),
      KEY `field_id` (`field_id`),
      CONSTRAINT `fk_student_meta_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
      CONSTRAINT `fk_student_meta_field` FOREIGN KEY (`field_id`) REFERENCES `fields` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql3);
    echo "Checked/Created student_meta table.<br>";

    // 4. Seed Fields
    $count = $pdo->query("SELECT COUNT(*) FROM fields")->fetchColumn();
    if ($count == 0) {
        $sqlSeed = "INSERT INTO `fields` (`name`, `label`, `type`, `is_custom`, `section`, `order_index`) VALUES
        ('roll_no', 'Roll No', 'text', 0, 'main', 1),
        ('enrollment_no', 'Enrollment No', 'text', 0, 'main', 2),
        ('session', 'Session', 'text', 0, 'main', 3),
        ('class_id', 'Class', 'select', 0, 'main', 4),
        ('section_id', 'Section', 'select', 0, 'main', 5),
        ('student_name', 'Student Name', 'text', 0, 'main', 6),
        ('dob', 'Date of Birth', 'date', 0, 'main', 7),
        ('b_form', 'B-Form', 'text', 0, 'main', 8),
        ('father_name', 'Father Name', 'text', 0, 'main', 9),
        ('cnic', 'CNIC', 'text', 0, 'main', 10),
        ('mobile', 'Mobile', 'text', 0, 'main', 11),
        ('address', 'Address', 'textarea', 0, 'main', 12),
        ('father_occupation', 'Father Occupation', 'text', 0, 'main', 13),
        ('category_id', 'Category', 'select', 0, 'main', 14),
        ('fcategory_id', 'Family Category', 'select', 0, 'main', 15),
        ('email', 'Email', 'email', 0, 'main', 16),
        ('photo_path', 'Photo', 'file', 0, 'main', 17),
        ('bps', 'BPS', 'number', 0, 'main', 18),
        ('religion', 'Religion', 'text', 0, 'main', 19),
        ('caste', 'Caste', 'text', 0, 'main', 20),
        ('domicile', 'Domicile', 'text', 0, 'main', 21);";
        $pdo->exec($sqlSeed);
        echo "Seeded fields table.<br>";
    }

    // 5. Add thumbnail_blob column
    try {
        $pdo->exec("ALTER TABLE `students` ADD COLUMN `thumbnail_blob` MEDIUMBLOB DEFAULT NULL");
        echo "Added thumbnail_blob column.<br>";
    } catch (PDOException $e) {
        // Ignore if exists
        echo "thumbnail_blob column might already exist: " . $e->getMessage() . "<br>";
    }

    echo "Database fix completed successfully.";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
