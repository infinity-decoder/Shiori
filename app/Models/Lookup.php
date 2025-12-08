<?php
/**
 * Lookup Model
 * 
 * Manages all lookup/reference data: Classes, Sections, Sessions, Categories
 * Provides CRUD operations with safe deletion (prevents removing in-use items)
 */
class Lookup
{
    // ==================== CLASSES ====================
    
    public static function getClasses(): array
    {
        $stmt = DB::get()->query("SELECT id, name FROM classes ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function createClass(string $name): int
    {
        $pdo = DB::get();
        $name = trim($name);
        
        if (empty($name)) {
            throw new InvalidArgumentException('Class name cannot be empty');
        }
        
        $stmt = $pdo->prepare("INSERT INTO classes (name) VALUES (?)");
        $stmt->execute([$name]);
        return (int)$pdo->lastInsertId();
    }
    
    public static function deleteClass(int $id): bool
    {
        $pdo = DB::get();
        
        // Check if any students use this class
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM students WHERE class_id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
            return false; // In use, cannot delete
        }
        
        $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
        $stmt->execute([$id]);
        return true;
    }
    
    // ==================== SECTIONS ====================
    
    public static function getSections(): array
    {
        $stmt = DB::get()->query("SELECT id, name FROM sections ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function createSection(string $name): int
    {
        $pdo = DB::get();
        $name = trim($name);
        
        if (empty($name)) {
            throw new InvalidArgumentException('Section name cannot be empty');
        }
        
        $stmt = $pdo->prepare("INSERT INTO sections (name) VALUES (?)");
        $stmt->execute([$name]);
        return (int)$pdo->lastInsertId();
    }
    
    public static function deleteSection(int $id): bool
    {
        $pdo = DB::get();
        
        // Check if any students use this section
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM students WHERE section_id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
            return false; // In use, cannot delete
        }
        
        $stmt = $pdo->prepare("DELETE FROM sections WHERE id = ?");
        $stmt->execute([$id]);
        return true;
    }
    
    // ==================== SESSIONS ====================
    
    public static function getSessions(): array
    {
        require_once BASE_PATH . '/app/Models/Session.php';
        return Session::getAll(true); // Only active sessions
    }
    
    // ==================== CATEGORIES ====================
    
    public static function getCategories(): array
    {
        $stmt = DB::get()->query("SELECT id, name FROM categories ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function createCategory(string $name): int
    {
        $pdo = DB::get();
        $name = trim($name);
        
        if (empty($name)) {
            throw new InvalidArgumentException('Category name cannot be empty');
        }
        
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$name]);
        return (int)$pdo->lastInsertId();
    }
    
    public static function deleteCategory(int $id): bool
    {
        $pdo = DB::get();
        
        // Check if any students use this category
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM students WHERE category_id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
            return false; // In use, cannot delete
        }
        
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        return true;
    }
    
    // ==================== FAMILY CATEGORIES ====================
    
    public static function getFamilyCategories(): array
    {
        $stmt = DB::get()->query("SELECT id, name FROM family_categories ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function createFamilyCategory(string $name): int
    {
        $pdo = DB::get();
        $name = trim($name);
        
        if (empty($name)) {
            throw new InvalidArgumentException('Family category name cannot be empty');
        }
        
        $stmt = $pdo->prepare("INSERT INTO family_categories (name) VALUES (?)");
        $stmt->execute([$name]);
        return (int)$pdo->lastInsertId();
    }
    
    public static function deleteFamilyCategory(int $id): bool
    {
        $pdo = DB::get();
        
        // Check if any students use this family category
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM students WHERE fcategory_id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
            return false; // In use, cannot delete
        }
        
        $stmt = $pdo->prepare("DELETE FROM family_categories WHERE id = ?");
        $stmt->execute([$id]);
        return true;
    }
    
    // ==================== UTILITY ====================
    
    /**
     * Get usage count for any lookup type
     * @param string $type Type: class, section, category, fcategory
     * @param int $id Lookup ID
     * @return int Number of students using this lookup
     */
    public static function getUsageCount(string $type, int $id): int
    {
        $pdo = DB::get();
        
        $columnMap = [
            'class' => 'class_id',
            'section' => 'section_id',
            'category' => 'category_id',
            'fcategory' => 'fcategory_id',
        ];
        
        if (!isset($columnMap[$type])) {
            return 0;
        }
        
        $column = $columnMap[$type];
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM students WHERE {$column} = ?");
        $stmt->execute([$id]);
        
        return (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }
}
