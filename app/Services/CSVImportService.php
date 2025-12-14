<?php

require_once BASE_PATH . '/app/Services/ImportResult.php';
require_once BASE_PATH . '/app/Services/CSVTemplateService.php';
require_once BASE_PATH . '/app/Services/LookupService.php';

/**
 * CSVImportService
 * 
 * Enterprise-grade CSV import engine with:
 * - Partial success imports (valid rows saved, invalid rows skipped)
 * - Transaction safety
 * - Row-level validation
 * - Detailed error reporting
 * - Memory-efficient batch processing
 * - Organization-agnostic (no hardcoded assumptions)
 */
class CSVImportService
{
    private const BATCH_SIZE = 100; // Commit every 100 rows
    
    /**
     * Import CSV file
     */
    public static function importFile(string $filePath): ImportResult
    {
        $result = new ImportResult();
        
        // Validate file exists
        if (!file_exists($filePath)) {
            $result->addError(0, 'file', 'File not found');
            return $result;
        }
        
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            $result->addError(0, 'file', 'Could not open file');
            return $result;
        }
        
        try {
            // Read and validate headers
            $csvHeaders = fgetcsv($handle);
            if (!$csvHeaders) {
                $result->addError(0, 'file', 'Empty file or invalid format');
                fclose($handle);
                return $result;
            }
            
            // Get field mapping
            $fieldMapping = CSVTemplateService::getFieldMapping();
            $columnMapping = self::mapCSVColumnsToFields($csvHeaders, $fieldMapping);
            
            if (empty($columnMapping)) {
                $result->addError(0, 'headers', 'No valid columns found in CSV');
                fclose($handle);
                return $result;
            }
            
            // Process rows
            $pdo = DB::get();
            $rowNum = 1; // Header is row 1, data starts at row 2
            $batchCount = 0;
            
            $pdo->beginTransaction();
            
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                $result->totalRows++;
                
                try {
                    // Validate and map row data
                    $studentData = self::validateAndMapRow($row, $columnMapping, $rowNum);
                    
                    if ($studentData) {
                        // Create student record
                        Student::create($studentData);
                        $result->incrementSuccess();
                        $batchCount++;
                        
                        // Commit batch
                        if ($batchCount >= self::BATCH_SIZE) {
                            $pdo->commit();
                            $pdo->beginTransaction();
                            $batchCount = 0;
                        }
                    }
                } catch (PDOException $e) {
                    $result->addError($rowNum, 'database', $e->getMessage());
                } catch (Exception $e) {
                    $result->addError($rowNum, 'validation', $e->getMessage());
                }
            }
            
            // Commit any remaining rows
            if ($batchCount > 0) {
                $pdo->commit();
            }
            
            fclose($handle);
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $result->addError(0, 'system', $e->getMessage());
        }
        
        return $result;
    }
    
    /**
     * Map CSV columns to database fields
     */
    private static function mapCSVColumnsToFields(array $csvHeaders, array $fieldMapping): array
    {
        $mapping = [];
        
        foreach ($csvHeaders as $index => $header) {
            $header = trim($header);
            
            // Try to find matching field by label
            if (isset($fieldMapping[$header])) {
                $mapping[$index] = $fieldMapping[$header];
            } else {
                // Try case-insensitive match
                foreach ($fieldMapping as $label => $field) {
                    if (strcasecmp($header, $label) === 0) {
                        $mapping[$index] = $field;
                        break;
                    }
                }
            }
        }
        
        return $mapping;
    }
    
    /**
     * Validate and map a single row
     */
    private static function validateAndMapRow(array $row, array $columnMapping, int $rowNum): ?array
    {
        $data = [];
        $errors = [];
        
        // Map columns to fields
        foreach ($columnMapping as $colIndex => $fieldMeta) {
            $value = isset($row[$colIndex]) ? trim($row[$colIndex]) : '';
            $fieldName = $fieldMeta['name'];
            
            // Special handling for Category and Family Category (name-to-ID mapping)
            if ($fieldName === 'category_id') {
                if (!empty($value)) {
                    $categoryId = LookupService::getCategoryIdByName($value);
                    $data['category_id'] = $categoryId;
                }
                continue; // Skip normal validation
            }
            
            if ($fieldName === 'fcategory_id') {
                if (!empty($value)) {
                    $fcategoryId = LookupService::getFamilyCategoryIdByName($value);
                    $data['fcategory_id'] = $fcategoryId;
                }
                continue; // Skip normal validation
            }
            
            // Validate field only if it has a value OR is truly required
            if (!empty($value) || $fieldMeta['required']) {
                $error = self::validateField($fieldName, $value, $fieldMeta);
                if ($error) {
                    $errors[] = "{$fieldMeta['label']}: {$error}";
                } else {
                    // Convert value based on type
                    $data[$fieldName] = self::convertValue($value, $fieldMeta);
                }
            } else {
                // Empty optional field - set to null
                $data[$fieldName] = null;
            }
        }
        
        // LENIENT VALIDATION: Only check truly mandatory fields
        $strictlyRequired = ['roll_no', 'enrollment_no', 'student_name', 'father_name', 'class_id', 'section_id'];
        
        foreach ($strictlyRequired as $req) {
            if (!isset($data[$req]) || $data[$req] === '' || $data[$req] === null) {
                $errors[] = ucfirst(str_replace('_', ' ', $req)) . " is required";
            }
        }
        
        // If there are errors, throw exception
        if (!empty($errors)) {
            throw new Exception(implode('; ', $errors));
        }
        
        // Set SMART defaults for optional fields
        if (empty($data['session'])) {
            $data['session'] = date('Y') . '-' . (date('Y') + 1);
        }
        
        // Category and Family Category default to 1 if not mapped
        if (!isset($data['category_id']) || $data['category_id'] === null) {
            $data['category_id'] = 1;
        }
        if (!isset($data['fcategory_id']) || $data['fcategory_id'] === null) {
            $data['fcategory_id'] = 1;
        }
        
        return $data;
    }
    
    /**
     * Validate a single field value
     */
    private static function validateField(string $fieldName, string $value, array $fieldMeta): ?string
    {
        // Skip validation for empty optional fields
        if (!$fieldMeta['required'] && $value === '') {
            return null;
        }
        
        // Required field check
        if ($fieldMeta['required'] && $value === '') {
            return "Required field";
        }
        
        // Type-specific validation
        switch ($fieldMeta['type']) {
            case 'number':
                if ($value !== '' && !is_numeric($value)) {
                    return "Must be a number";
                }
                break;
                
            case 'date':
                if ($value !== '' && !self::isValidDate($value)) {
                    return "Invalid date format (use YYYY-MM-DD)";
                }
                break;
        }
        
        // Field-specific validation
        if ($fieldName === 'cnic' || $fieldName === 'b_form') {
            if ($value !== '' && !preg_match('/^\d{13}$/', $value)) {
                return "Must be exactly 13 digits";
            }
        }
        
        if ($fieldName === 'email') {
            if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return "Invalid email format";
            }
        }
        
        if ($fieldName === 'mobile') {
            $digits = preg_replace('/\D/', '', $value);
            if ($value !== '' && (strlen($digits) < 7 || strlen($digits) > 15)) {
                return "Mobile number must be 7-15 digits";
            }
        }
        
        if ($fieldName === 'session') {
            if ($value !== '' && !preg_match('/^\d{4}-\d{4}$/', $value)) {
                return "Session must be in format YYYY-YYYY (e.g., 2025-2026)";
            }
        }
        
        return null;
    }
    
    /**
     * Convert value to appropriate type
     */
    private static function convertValue(string $value, array $fieldMeta)
    {
        if ($value === '') {
            return null;
        }
        
        switch ($fieldMeta['type']) {
            case 'number':
                return is_numeric($value) ? (int)$value : null;
                
            default:
                return $value;
        }
    }
    
    /**
     * Check if date is valid
     */
    private static function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
