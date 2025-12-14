<?php

/**
 * LookupService
 * 
 * Provides intelligent name-to-ID mapping for categories and family categories.
 * Handles:
 * - Case-insensitive matching
 * - Synonym resolution (e.g., "Civ" → "Civilian")
 * - Default value seeding
 * - Graceful fallback (returns null for invalid values)
 */
class LookupService
{
    // Default Family Category values
    const DEFAULT_FAMILY_CATEGORIES = [
        'Nuclear Family',
        'Joint Family',
        'Single-Parent',
        'Orphan',
        'Separated',
        'Divorced',
        'Widowed',
        'Guardian-Led Family',
        'Adoptive Family',
        'Other'
    ];
    
    // Default Category values with synonyms
    const DEFAULT_CATEGORIES = [
        'Civilian' => ['Civilian', 'Civ'],
        'FGEI' => ['FGEI'],
        'Army Serving' => ['Army Serving', 'Army-Serving'],
        'Army Retired' => ['Army Retired', 'Army-Retired'],
        'Defence Paid' => ['Defence Paid', 'Defense Paid', 'Defence-Paid', 'Defense-Paid']
    ];
    
    // Default Section values
    const DEFAULT_SECTIONS = ['A', 'B', 'C', 'D', 'E', 'F'];
    
    /**
     * Seed default categories, family categories, and sections if they don't exist
     */
    public static function seedDefaults(): void
    {
        require_once BASE_PATH . '/app/Models/Lookup.php';
        
        self::seedFamilyCategories();
        self::seedCategories();
        self::seedSections();
    }
    
    /**
     * Seed default family categories
     */
    private static function seedFamilyCategories(): void
    {
        $pdo = DB::get();
        
        // Check if any exist
        $stmt = $pdo->query("SELECT COUNT(*) FROM family_categories");
        $count = (int)$stmt->fetchColumn();
        
        if ($count > 0) {
            return; // Already seeded
        }
        
        // Insert defaults
        foreach (self::DEFAULT_FAMILY_CATEGORIES as $name) {
            try {
                Lookup::createFamilyCategory($name);
            } catch (Exception $e) {
                // Skip if already exists
            }
        }
    }
    
    /**
     * Seed default categories
     */
    private static function seedCategories(): void
    {
        $pdo = DB::get();
        
        // Check if any exist
        $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
        $count = (int)$stmt->fetchColumn();
        
        if ($count > 0) {
            return; // Already seeded
        }
        
        // Insert defaults (use primary name only)
        foreach (self::DEFAULT_CATEGORIES as $primaryName => $synonyms) {
            try {
                Lookup::createCategory($primaryName);
            } catch (Exception $e) {
                // Skip if already exists
            }
        }
    }
    
    /**
     * Seed default sections
     */
    private static function seedSections(): void
    {
        $pdo = DB::get();
        
        // Check if any exist
        $stmt = $pdo->query("SELECT COUNT(*) FROM sections");
        $count = (int)$stmt->fetchColumn();
        
        if ($count > 0) {
            return; // Already seeded
        }
        
        // Insert defaults (A through F)
        foreach (self::DEFAULT_SECTIONS as $name) {
            try {
                Lookup::createSection($name);
            } catch (Exception $e) {
                // Skip if already exists
            }
        }
    }
    
    /**
     * Map family category name to ID
     * 
     * @param string $name Human-readable name (case-insensitive)
     * @return int|null Category ID or null if not found
     */
    public static function getFamilyCategoryIdByName(string $name): ?int
    {
        if (empty($name)) {
            return null;
        }
        
        $pdo = DB::get();
        $name = trim($name);
        
        // Try exact case-insensitive match
        $stmt = $pdo->prepare("SELECT id FROM family_categories WHERE LOWER(name) = LOWER(?)");
        $stmt->execute([$name]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            return (int)$result['id'];
        }
        
        return null;
    }
    
    /**
     * Map category name to ID with synonym support
     * 
     * @param string $name Human-readable name or synonym (case-insensitive)
     * @return int|null Category ID or null if not found
     */
    public static function getCategoryIdByName(string $name): ?int
    {
        if (empty($name)) {
            return null;
        }
        
        $pdo = DB::get();
        $name = trim($name);
        
        // First, try direct database match
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE LOWER(name) = LOWER(?)");
        $stmt->execute([$name]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            return (int)$result['id'];
        }
        
        // If no match, try synonym matching
        foreach (self::DEFAULT_CATEGORIES as $primaryName => $synonyms) {
            foreach ($synonyms as $synonym) {
                if (strcasecmp($name, $synonym) === 0) {
                    // Found synonym, now find primary name in DB
                    $stmt = $pdo->prepare("SELECT id FROM categories WHERE LOWER(name) = LOWER(?)");
                    $stmt->execute([$primaryName]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($result) {
                        return (int)$result['id'];
                    }
                }
            }
        }
        
        return null;
    }
    
    /**
     * Get all family categories as name => id map
     */
    public static function getFamilyCategoryMap(): array
    {
        $pdo = DB::get();
        $stmt = $pdo->query("SELECT id, name FROM family_categories");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $map = [];
        foreach ($categories as $cat) {
            $map[strtolower($cat['name'])] = (int)$cat['id'];
        }
        
        return $map;
    }
    
    /**
     * Get all categories as name => id map (including synonyms)
     */
    public static function getCategoryMap(): array
    {
        $pdo = DB::get();
        $stmt = $pdo->query("SELECT id, name FROM categories");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $map = [];
        foreach ($categories as $cat) {
            $name = strtolower($cat['name']);
            $id = (int)$cat['id'];
            $map[$name] = $id;
            
            // Add synonyms
            foreach (self::DEFAULT_CATEGORIES as $primaryName => $synonyms) {
                if (strcasecmp($cat['name'], $primaryName) === 0) {
                    foreach ($synonyms as $synonym) {
                        $map[strtolower($synonym)] = $id;
                    }
                }
            }
        }
        
        return $map;
    }
    
    /**
     * Get list of valid family category names for CSV template
     */
    public static function getFamilyCategoryNames(): array
    {
        $pdo = DB::get();
        $stmt = $pdo->query("SELECT name FROM family_categories ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Get list of valid category names for CSV template
     */
    public static function getCategoryNames(): array
    {
        $pdo = DB::get();
        $stmt = $pdo->query("SELECT name FROM categories ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Map section name to ID with case-insensitive matching
     * 
     * @param string $name Section name (e.g., 'A', 'a', 'B')
     * @return int|null Section ID or null if not found
     */
    public static function getSectionIdByName(string $name): ?int
    {
        if (empty($name)) {
            return null;
        }
        
        $pdo = DB::get();
        $name = trim($name);
        
        // Case-insensitive match
        $stmt = $pdo->prepare("SELECT id FROM sections WHERE LOWER(name) = LOWER(?)");
        $stmt->execute([$name]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            return (int)$result['id'];
        }
        
        return null;
    }
    
    /**
     * Map class name to ID with case-insensitive matching
     * 
     * @param string $name Class name (e.g., '1', '2', '8')
     * @return int|null Class ID or null if not found
     */
    public static function getClassIdByName(string $name): ?int
    {
        if (empty($name)) {
            return null;
        }
        
        $pdo = DB::get();
        $name = trim($name);
        
        // Try exact match first, then fuzzy
        $stmt = $pdo->prepare("SELECT id FROM classes WHERE name = ?");
        $stmt->execute([$name]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            return (int)$result['id'];
        }
        
        // Try as integer if numeric
        if (is_numeric($name)) {
            $stmt = $pdo->prepare("SELECT id FROM classes WHERE id = ?");
            $stmt->execute([(int)$name]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return (int)$result['id'];
            }
        }
        
        return null;
    }
}
