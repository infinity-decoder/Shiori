<?php

/**
 * ImportResult
 * 
 * Data Transfer Object for CSV import results.
 * Tracks success, failures, and detailed error information.
 */
class ImportResult
{
    public int $totalRows = 0;
    public int $successCount = 0;
    public int $failedCount = 0;
    public array $errors = [];
    public array $warnings = [];
    
    /**
     * Add an error for a specific row
     */
    public function addError(int $row, string $field, string $message): void
    {
        $this->errors[] = [
            'row' => $row,
            'field' => $field,
            'message' => $message
        ];
        $this->failedCount++;
    }
    
    /**
     * Add a warning (non-fatal issue)
     */
    public function addWarning(int $row, string $message): void
    {
        $this->warnings[] = [
            'row' => $row,
            'message' => $message
        ];
    }
    
    /**
     * Increment success count
     */
    public function incrementSuccess(): void
    {
        $this->successCount++;
    }
    
    /**
     * Get summary message for flash display
     */
    public function toFlashMessage(): string
    {
        $parts = [];
        
        if ($this->successCount > 0) {
            $parts[] = "{$this->successCount} student(s) imported successfully";
        }
        
        if ($this->failedCount > 0) {
            $parts[] = "{$this->failedCount} row(s) failed";
        }
        
        if (empty($parts)) {
            return "No rows were processed";
        }
        
        return implode('. ', $parts) . '.';
    }
    
    /**
     * Get detailed error message
     */
    public function getErrorSummary(int $maxErrors = 10): string
    {
        if (empty($this->errors)) {
            return '';
        }
        
        $errorLines = [];
        $displayErrors = array_slice($this->errors, 0, $maxErrors);
        
        foreach ($displayErrors as $error) {
            $errorLines[]  = "Row {$error['row']}, {$error['field']}: {$error['message']}";
        }
        
        $summary = implode(' | ', $errorLines);
        
        if (count($this->errors) > $maxErrors) {
            $remaining = count($this->errors) - $maxErrors;
            $summary .= " | ...and {$remaining} more error(s)";
        }
        
        return $summary;
    }
    
    /**
     * Check if import was completely successful
     */
    public function isFullSuccess(): bool
    {
        return $this->failedCount === 0 && $this->successCount > 0;
    }
    
    /**
     * Check if import had partial success
     */
    public function isPartialSuccess(): bool
    {
        return $this->successCount > 0 && $this->failedCount > 0;
    }
    
    /**
     * Check if import completely failed
     */
    public function isCompleteFailure(): bool
    {
        return $this->successCount === 0 && $this->failedCount > 0;
    }
}
