<?php
/**
 * Database Migration Script for Shiori SIS
 * 
 * This script adds missing columns and tables to existing installations.
 * Run this ONCE after upgrading to the new version.
 * 
 * SECURITY: This script should be deleted or moved outside public directory after use.
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

// Load database config
if (!file_exists(BASE_PATH . '/config/database.php')) {
    die('Database configuration not found. Please run installer first.');
}

$dbConfig = require BASE_PATH . '/config/database.php';

// Connect to database
try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['name']};charset=utf8mb4",
        $dbConfig['user'],
        $dbConfig['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
}

$migrations = [];
$errors = [];

// Migration 1: Add section column to fields table
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM fields LIKE 'section'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE fields ADD COLUMN section VARCHAR(50) DEFAULT 'main' AFTER options");
        $migrations[] = "✓ Added 'section' column to fields table";
    } else {
        $migrations[] = "⊘ 'section' column already exists in fields table";
    }
} catch (PDOException $e) {
    $errors[] = "✗ Failed to add section column: " . $e->getMessage();
}

// Migration 2: Create sessions table
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'sessions'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("
            CREATE TABLE sessions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                session_year VARCHAR(20) NOT NULL UNIQUE,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $migrations[] = "✓ Created sessions table";
        
        // Seed sessions 2020-2030
        $stmt = $pdo->prepare("INSERT INTO sessions (session_year, is_active) VALUES (?, 1)");
        for ($year = 2020; $year <= 2030; $year++) {
            $sessionYear = sprintf('%d-%d', $year, $year + 1);
            $stmt->execute([$sessionYear]);
        }
        $migrations[] = "✓ Seeded sessions 2020-2021 through 2030-2031";
    } else {
        $migrations[] = "⊘ Sessions table already exists";
    }
} catch (PDOException $e) {
    $errors[] = "✗ Failed to create sessions table: " . $e->getMessage();
}

// Migration 3: Add index to activity_logs for performance
try {
    $stmt = $pdo->query("SHOW INDEX FROM activity_logs WHERE Key_name = 'idx_created'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE activity_logs ADD INDEX idx_created (created_at)");
        $migrations[] = "✓ Added performance index to activity_logs";
    } else {
        $migrations[] = "⊘ Activity logs index already exists";
    }
} catch (PDOException $e) {
    $errors[] = "✗ Failed to add activity_logs index: " . $e->getMessage();
}

// Migration 4: Ensure password column is correct name
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'password_hash'");
    $hasPasswordHash = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'password'");
    $hasPassword = $stmt->rowCount() > 0;
    
    if ($hasPasswordHash && !$hasPassword) {
        $pdo->exec("ALTER TABLE users CHANGE password_hash password VARCHAR(255) NOT NULL");
        $migrations[] = "✓ Renamed password_hash to password in users table";
    } elseif (!$hasPassword && !$hasPasswordHash) {
        $errors[] = "✗ Users table has no password column";
    } else {
        $migrations[] = "⊘ Users table password column is correct";
    }
} catch (PDOException $e) {
    $errors[] = "✗ Failed to update users table: " . $e->getMessage();
}

// Migration 5: Ensure all default fields are seeded
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM fields");
    $count = $stmt->fetch()['count'];
    
    if ($count == 0) {
        // Seed default fields with proper order
        $defaults = [
            ['roll_no', 'Roll No', 'text', 'main', 1],
            ['enrollment_no', 'Enrollment No', 'text', 'main', 2],
            ['student_name', 'Student Name', 'text', 'main', 3],
            ['class_id', 'Class', 'select', 'main', 4],
            ['section_id', 'Section', 'select', 'main', 5],
            ['session', 'Session', 'select', 'main', 6],
            ['dob', 'Date of Birth', 'date', 'main', 7],
            ['b_form', 'B-Form', 'text', 'main', 8],
            ['father_name', 'Father Name', 'text', 'main', 9],
            ['father_occupation', 'Father Occupation', 'text', 'main', 10],
            ['cnic', 'CNIC', 'text', 'main', 11],
            ['mobile', 'Mobile', 'text', 'main', 12],
            ['email', 'Email', 'text', 'main', 13],
            ['category_id', 'Category', 'select', 'main', 14],
            ['fcategory_id', 'Family Category', 'select', 'main', 15],
            ['bps', 'BPS', 'number', 'main', 16],
            ['religion', 'Religion', 'text', 'main', 17],
            ['caste', 'Caste', 'text', 'main', 18],
            ['domicile', 'Domicile', 'text', 'main', 19],
            ['address', 'Address', 'textarea', 'main', 20],
            ['photo_path', 'Photo', 'file', 'sidebar', 99],
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO fields (name, label, type, section, is_active, is_custom, order_index)
            VALUES (?, ?, ?, ?, 1, 0, ?)
        ");
        
        foreach ($defaults as $field) {
            $stmt->execute([$field[0], $field[1], $field[2], $field[3], $field[4]]);
        }
        
        $migrations[] = "✓ Seeded default fields with proper order";
    } else {
        $migrations[] = "⊘ Fields already exist (count: $count)";
    }
} catch (PDOException $e) {
    $errors[] = "✗ Failed to seed fields: " . $e->getMessage();
}

// Output results
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shiori Database Migration</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 700px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #1f2937;
            margin-bottom: 10px;
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .subtitle {
            color: #6b7280;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .result {
            background: #f9fafb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .result h2 {
            color: #059669;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .result.errors h2 {
            color: #dc2626;
        }
        ul {
            list-style: none;
        }
        li {
            padding: 8px 0;
            color: #374151;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
        li:before {
            margin-right: 8px;
        }
        .success { color: #059669; }
        .skip { color: #f59e0b; }
        .error { color: #dc2626; }
        .warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin-top: 20px;
            border-radius: 4px;
            color: #92400e;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 20px;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #5568d3;
        }
        .infinity {
            font-size: 36px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>
            <span class="infinity">∞</span>
            Database Migration Complete
        </h1>
        <p class="subtitle">Shiori Student Information System</p>
        
        <?php if (!empty($migrations)): ?>
        <div class="result">
            <h2>✓ Migrations Applied</h2>
            <ul>
                <?php foreach ($migrations as $msg): ?>
                    <li class="<?= strpos($msg, '✓') !== false ? 'success' : 'skip' ?>"><?= htmlspecialchars($msg) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
        <div class="result errors">
            <h2>✗ Errors Encountered</h2>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li class="error"><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <div class="warning">
            <strong>⚠️ Security Notice:</strong> Please delete this file (migrate_schema.php) after successful migration to prevent unauthorized access.
        </div>
        
        <a href="<?= dirname($_SERVER['PHP_SELF']) ?>" class="btn">← Back to Application</a>
    </div>
</body>
</html>
