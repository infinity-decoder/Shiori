<?php

/**
 * ImageHelper
 * 
 * Provides consistent, dynamic image URL generation across all pages.
 * Handles filesystem cache, database BLOB fallback, and default placeholders.
 */

if (!function_exists('getStudentImageUrl')) {
    /**
     * Get student image URL with automatic fallback chain
     * 
     * @param array $student Student data (must include 'id')
     * @param string $baseUrl Application base URL
     * @param string $size 'full' or 'thumb'
     * @return string Public URL to image
     */
    function getStudentImageUrl(array $student, string $baseUrl, string $size = 'thumb'): string
    {
        $id = $student['id'] ?? 0;
        
        if ($id <= 0) {
            return getDefaultAvatarUrl($baseUrl);
        }
        
        // Use dynamic route that handles filesystem + BLOB fallback
        $sizeParam = ($size === 'full') ? '&size=full' : '';
        return "{$baseUrl}/students/photo?id={$id}{$sizeParam}";
    }
}

if (!function_exists('getDefaultAvatarUrl')) {
    /**
     * Get default avatar placeholder URL
     * 
     * @param string $baseUrl Application base URL
     * @return string URL to default avatar
     */
    function getDefaultAvatarUrl(string $baseUrl): string
    {
        // Use data URI for guaranteed availability
        return 'data:image/svg+xml;base64,' . base64_encode('
            <svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200">
                <rect fill="#e2e8f0" width="200" height="200"/>
                <circle cx="100" cy="80" r="35" fill="#94a3b8"/>
                <path d="M100 120 Q70 140 50 180 H150 Q130 140 100 120 Z" fill="#94a3b8"/>
            </svg>
        ');
    }
}

if (!function_exists('hasStudentImage')) {
    /**
     * Check if student has an image
     * 
     * @param array $student Student data
     * @return bool
     */
    function hasStudentImage(array $student): bool
    {
        return !empty($student['photo_path']) || !empty($student['photo_blob']);
    }
}
