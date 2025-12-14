<?php

/**
 * CSVTemplateService
 * 
 * Generates CSV templates dynamically based on active fields in the database.
 * Supports organization-specific field configurations.
 * No hardcoded field assumptions - fully dynamic.
 */
class CSVTemplateService
{
    /**
     * Get all importable fields (active, non-system fields)
     */
    public static function getImportableFields(): array
    {
        require_once BASE_PATH . '/app/Models/Field.php';
        Field::seedDefaults(); // Ensure defaults exist
        
        $allFields = Field::getAll(true); // Only active fields
        
        $importable = [];
        $excludedFields = ['photo_path', 'id', 'created_at', 'updated_at', 'thumbnail_blob'];
        
        foreach ($allFields as $field) {
            if (!in_array($field['name'], $excludedFields, true)) {
                $importable[] = $field;
            }
        }
        
        return $importable;
    }
    
    /**
     * Generate CSV headers dynamically
     */
    public static function generateHeaders(): array
    {
        $fields = self::getImportableFields();
        $headers = [];
        
        foreach ($fields as $field) {
            $headers[] = $field['label'];
        }
        
        return $headers;
    }
    
    /**
     * Generate example row with sample data
     */
    public static function generateExampleRow(): array
    {
        $fields = self::getImportableFields();
        $row = [];
        
        foreach ($fields as $field) {
            $row[] = self::getSampleValue($field);
        }
        
        return $row;
    }
    
    /**
     * Get field mapping: label => field metadata
     * Used during import to map CSV columns to database fields
     */
    public static function getFieldMapping(): array
    {
        $fields = self::getImportableFields();
        $mapping = [];
        
        foreach ($fields as $field) {
            $mapping[$field['label']] = [
                'name' => $field['name'],
                'type' => $field['type'],
                'label' => $field['label'],
                'required' => self::isRequired($field['name']),
                'is_custom' => (bool)$field['is_custom']
            ];
        }
        
        return $mapping;
    }
    
    /**
     * Get validation rules for each field
     */
    public static function getValidationRules(): array
    {
        $fields = self::getImportableFields();
        $rules = [];
        
        foreach ($fields as $field) {
            $rules[$field['name']] = [
                'required' => self::isRequired($field['name']),
                'type' => $field['type'],
                'maxLength' => self::getMaxLength($field),
                'pattern' => self::getPattern($field['name'])
            ];
        }
        
        return $rules;
    }
    
    /**
     * Check if a field is required
     */
    private static function isRequired(string $fieldName): bool
    {
        // LENIENT: Only truly required fields for student record
        $requiredFields = [
            'roll_no', 'enrollment_no', 'class_id', 'section_id',
            'student_name', 'father_name'
        ];
        
        return in_array($fieldName, $requiredFields, true);
    }
    
    /**
     * Get maximum length for field
     */
    private static function getMaxLength(array $field): ?int
    {
        if ($field['type'] === 'textarea') {
            return 1000;
        }
        
        if ($field['type'] === 'text') {
            return 255;
        }
        
        if (in_array($field['name'], ['cnic', 'b_form'], true)) {
            return 13;
        }
        
        return null;
    }
    
    /**
     * Get validation pattern for field
     */
    private static function getPattern(string $fieldName): ?string
    {
        if ($fieldName === 'cnic' || $fieldName === 'b_form') {
            return '/^\d{13}$/';
        }
        
        if ($fieldName === 'email') {
            return '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
        }
        
        if ($fieldName === 'mobile') {
            return '/^\d{7,15}$/';
        }
        
        if ($fieldName === 'session') {
            return '/^\d{4}-\d{4}$/';
        }
        
        if ($fieldName === 'dob') {
            return '/^\d{4}-\d{2}-\d{2}$/';
        }
        
        return null;
    }
    
    /**
     * Generate sample value based on field type
     */
    private static function getSampleValue(array $field): string
    {
        $name = $field['name'];
        
        // Specific field examples
        $samples = [
            'roll_no' => '101',
            'enrollment_no' => 'ENR-2025-001',
            'student_name' => 'John Doe',
            'father_name' => 'Richard Doe',
            'class_id' => '1',
            'section_id' => 'A',  // Use name, not ID
            'session' => '2025-2026',
            'dob' => '2010-01-15',
            'b_form' => '1234567890123',
            'father_occupation' => 'Engineer',
            'cnic' => '1234567890123',
            'mobile' => '03001234567',
            'email' => 'john.doe@example.com',
            'category_id' => 'Civilian',  // Use name, not ID
            'fcategory_id' => 'Nuclear Family',  // Use name, not ID
            'bps' => '17',
            'religion' => 'Islam',
            'caste' => 'Rajput',
            'domicile' => 'Karachi',
            'address' => '123 Main Street, Karachi',
        ];
        
        if (isset($samples[$name])) {
            return $samples[$name];
        }
        
        // Generic samples based on type
        switch ($field['type']) {
            case 'text':
                return 'Sample Text';
            case 'number':
                return '100';
            case 'date':
                return '2025-01-01';
            case 'select':
                return 'Option 1';
            case 'textarea':
                return 'Sample longer text content';
            default:
                return '';
        }
    }
}
