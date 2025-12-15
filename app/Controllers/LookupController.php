<?php
/**
 * LookupController
 * 
 * Manages all lookup/reference data through admin interface
 * Classes, Sections, Sessions, Categories, Family Categories
 * 
 * SECURITY: All methods require admin authentication
 */

class LookupController extends Controller
{
    /**
     * Display lookup management page
     */
    public function index(): void
    {
        $this->requireAuth();
        
        if (!Auth::isAdmin()) {
            Auth::flash('error', 'Access denied. Admin privileges required.');
            $this->redirect('/dashboard');
        }
        
        // Load Session model
        require_once BASE_PATH . '/app/Models/Session.php';
        
        // Get all lookups
        $data = [
            'title' => 'Manage Lookups | Shiori',
            'classes' => Lookup::getClasses(),
            'sections' => Lookup::getSections(),
            'sessions' => Session::getAll(false), // Include inactive
            'categories' => Lookup::getCategories(),
            'familyCategories' => Lookup::getFamilyCategories(),
        ];
        
        // Add usage counts
        foreach ($data['classes'] as &$class) {
            $class['student_count'] = Lookup::getUsageCount('class', (int)$class['id']);
        }
        foreach ($data['sections'] as &$section) {
            $section['student_count'] = Lookup::getUsageCount('section', (int)$section['id']);
        }
        foreach ($data['categories'] as &$category) {
            $category['student_count'] = Lookup::getUsageCount('category', (int)$category['id']);
        }
        foreach ($data['familyCategories'] as &$fc) {
            $fc['student_count'] = Lookup::getUsageCount('fcategory', (int)$fc['id']);
        }
        
        $this->view('lookups/index.php', $data);
    }
    
    // ==================== CLASSES ====================
    
    public function storeClass(): void
    {
        $this->requireAuth();
        if (!Auth::isAdmin()) {
            $this->redirect('/dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/lookups');
        }
        
        if (!CSRF::verify($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid security token.');
            $this->redirect('/lookups');
        }
        
        $name = trim($_POST['name'] ?? '');
        
        if (empty($name)) {
            Auth::flash('error', 'Class name is required.');
            $this->redirect('/lookups');
        }
        
        try {
            Lookup::createClass($name);
            Auth::flash('success', 'Class added successfully.');
        } catch (PDOException $e) {
            Auth::flash('error', 'Failed to add class. It may already exist.');
        }
        
        $this->redirect('/lookups');
    }
    
    public function deleteClass(): void
    {
        $this->requireAuth();
        if (!Auth::isAdmin()) {
            $this->redirect('/dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/lookups');
        }
        
        if (!CSRF::verify($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid security token.');
            $this->redirect('/lookups');
        }
        
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            Auth::flash('error', 'Invalid class ID.');
            $this->redirect('/lookups');
        }
        
        if (Lookup::deleteClass($id)) {
            Auth::flash('success', 'Class deleted successfully.');
        } else {
            Auth::flash('error', 'Cannot delete class. It is being used by students.');
        }
        
        $this->redirect('/lookups');
    }
    
    // ==================== SECTIONS ====================
    
    public function storeSection(): void
    {
        $this->requireAuth();
        if (!Auth::isAdmin()) {
            $this->redirect('/dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/lookups');
        }
        
        if (!CSRF::verify($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid security token.');
            $this->redirect('/lookups');
        }
        
        $name = trim($_POST['name'] ?? '');
        
        if (empty($name)) {
            Auth::flash('error', 'Section name is required.');
            $this->redirect('/lookups');
        }
        
        try {
            Lookup::createSection($name);
            Auth::flash('success', 'Section added successfully.');
        } catch (PDOException $e) {
            Auth::flash('error', 'Failed to add section. It may already exist.');
        }
        
        $this->redirect('/lookups');
    }
    
    public function deleteSection(): void
    {
        $this->requireAuth();
        if (!Auth::isAdmin()) {
            $this->redirect('/dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/lookups');
        }
        
        if (!CSRF::verify($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid security token.');
            $this->redirect('/lookups');
        }
        
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            Auth::flash('error', 'Invalid section ID.');
            $this->redirect('/lookups');
        }
        
        if (Lookup::deleteSection($id)) {
            Auth::flash('success', 'Section deleted successfully.');
        } else {
            Auth::flash('error', 'Cannot delete section. It is being used by students.');
        }
        
        $this->redirect('/lookups');
    }
    
    // ==================== SESSIONS ====================
    
    public function storeSession(): void
    {
        $this->requireAuth();
        if (!Auth::isAdmin()) {
            $this->redirect('/dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/lookups');
        }
        
        if (!CSRF::verify($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid security token.');
            $this->redirect('/lookups');
        }
        
        require_once BASE_PATH . '/app/Models/Session.php';
        
        $sessionYear = trim($_POST['session_year'] ?? '');
        
        if (empty($sessionYear)) {
            Auth::flash('error', 'Session year is required.');
            $this->redirect('/lookups');
        }
        
        // Validate format
        if (!preg_match('/^\d{4}-\d{4}$/', $sessionYear)) {
            Auth::flash('error', 'Invalid format. Use YYYY-YYYY (e.g., 2024-2025)');
            $this->redirect('/lookups');
        }
        
        try {
            if (Session::exists($sessionYear)) {
                Auth::flash('error', 'Session already exists.');
            } else {
                Session::create($sessionYear);
                Auth::flash('success', 'Session added successfully.');
            }
        } catch (Exception $e) {
            Auth::flash('error', 'Failed to add session: ' . $e->getMessage());
        }
        
        $this->redirect('/lookups');
    }
    
    public function toggleSession(): void
    {
        $this->requireAuth();
        if (!Auth::isAdmin()) {
            $this->redirect('/dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/lookups');
        }
        
        if (!CSRF::verify($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid security token.');
            $this->redirect('/lookups');
        }
        
        require_once BASE_PATH . '/app/Models/Session.php';
        
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            Auth::flash('error', 'Invalid session ID.');
            $this->redirect('/lookups');
        }
        
        Session::toggle($id);
        Auth::flash('success', 'Session status updated.');
        
        $this->redirect('/lookups');
    }
    
    // ==================== CATEGORIES ====================
    
    public function storeCategory(): void
    {
        $this->requireAuth();
        if (!Auth::isAdmin()) {
            $this->redirect('/dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/lookups');
        }
        
        if (!CSRF::verify($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid security token.');
            $this->redirect('/lookups');
        }
        
        $name = trim($_POST['name'] ?? '');
        
        if (empty($name)) {
            Auth::flash('error', 'Category name is required.');
            $this->redirect('/lookups');
        }
        
        try {
            Lookup::createCategory($name);
            Auth::flash('success', 'Category added successfully.');
        } catch (PDOException $e) {
            Auth::flash('error', 'Failed to add category. It may already exist.');
        }
        
        $this->redirect('/lookups');
    }
    
    public function deleteCategory(): void
    {
        $this->requireAuth();
        if (!Auth::isAdmin()) {
            $this->redirect('/dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/lookups');
        }
        
        if (!CSRF::verify($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid security token.');
            $this->redirect('/lookups');
        }
        
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            Auth::flash('error', 'Invalid category ID.');
            $this->redirect('/lookups');
        }
        
        if (Lookup::deleteCategory($id)) {
            Auth::flash('success', 'Category deleted successfully.');
        } else {
            Auth::flash('error', 'Cannot delete category. It is being used by students.');
        }
        
        $this->redirect('/lookups');
    }
    
    // ==================== FAMILY CATEGORIES ====================
    
    public function storeFamilyCategory(): void
    {
        $this->requireAuth();
        if (!Auth::isAdmin()) {
            $this->redirect('/dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/lookups');
        }
        
        if (!CSRF::verify($_POST['csrf_token'] ?? null)){
            Auth::flash('error', 'Invalid security token.');
            $this->redirect('/lookups');
        }
        
        $name = trim($_POST['name'] ?? '');
        
        if (empty($name)) {
            Auth::flash('error', 'Family category name is required.');
            $this->redirect('/lookups');
        }
        
        try {
            Lookup::createFamilyCategory($name);
            Auth::flash('success', 'Family category added successfully.');
        } catch (PDOException $e) {
            Auth::flash('error', 'Failed to add family category. It may already exist.');
        }
        
        $this->redirect('/lookups');
    }
    
    public function deleteFamilyCategory(): void
    {
        $this->requireAuth();
        if (!Auth::isAdmin()) {
            $this->redirect('/dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/lookups');
        }
        
        if (!CSRF::verify($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid security token.');
            $this->redirect('/lookups');
        }
        
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            Auth::flash('error', 'Invalid family category ID.');
            $this->redirect('/lookups');
        }
        
        if (Lookup::deleteFamilyCategory($id)) {
            Auth::flash('success', 'Family category deleted successfully.');
        } else {
            Auth::flash('error', 'Cannot delete family category. It is being used by students.');
        }
        
        $this->redirect('/lookups#fcategories');
    }

    // ==================== GENERIC TOGGLE ====================

    /**
     * Unified toggle method for all lookup types
     * Preserves active tab using URL fragment
     */
    public function toggle(): void
    {
        $this->requireAuth();
        if (!Auth::isAdmin()) {
            $this->redirect('/dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/lookups');
        }
        
        if (!CSRF::verify($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid security token.');
            $this->redirect('/lookups');
        }
        
        $type = $_POST['type'] ?? '';
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id <= 0 || empty($type)) {
            Auth::flash('error', 'Invalid request parameters.');
            $this->redirect('/lookups');
        }
        
        try {
            Lookup::toggle($type, $id);
            Auth::flash('success', ucfirst($type) . ' status updated.');
        } catch (Exception $e) {
            Auth::flash('error', 'Failed to update status.');
        }
        
        // Redirect back to the specific tab
        // Map type to tab ID (classes, sections, sessions, categories, fcategories)
        $tabMap = [
            'class' => 'classes',
            'section' => 'sections',
            'session' => 'sessions',
            'category' => 'categories',
            'fcategory' => 'fcategories'
        ];
        
        $hash = $tabMap[$type] ?? 'classes';
        $this->redirect('/lookups#' . $hash);
    }
}
