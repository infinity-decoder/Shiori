<?php
// Add photo serving route after existing StudentController methods

    /**
     * Serve student photo with filesystem-first, database-fallback strategy
     */
    public function servePhoto(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $size = $_GET['size'] ?? 'thumb';
        
        if ($id <= 0) {
            $this->serveDefaultAvatar();
            return;
        }
        
        $student = Student::find($id);
        if (!$student) {
            $this->serveDefaultAvatar();
            return;
        }
        
        $uploadsDir = BASE_PATH . '/public/uploads/students';
        
        // Try filesystem cache first (fastest)
        if (!empty($student['photo_path'])) {
            if ($size === 'thumb') {
                $base = pathinfo($student['photo_path'], PATHINFO_FILENAME);
                $thumbPath = $uploadsDir . DIRECTORY_SEPARATOR . 'thumb_' . $base . '.jpg';
                if (file_exists($thumbPath)) {
                    ImageService::serveImage(file_get_contents($thumbPath), 'image/jpeg');
                }
            }
            
            $fullPath = $uploadsDir . DIRECTORY_SEPARATOR . $student['photo_path'];
            if (file_exists($fullPath)) {
                $mime = $student['photo_mime'] ?? 'image/jpeg';
                ImageService::serveImage(file_get_contents($fullPath), $mime);
            }
        }
        
        // Fallback to database BLOB (portability)
        if (!empty($student['photo_blob'])) {
            // Regenerate filesystem cache for future requests
            ImageService::regenerateFromBlob($id, $student['photo_blob'], $student['photo_mime'] ?? 'image/jpeg');
            
            // Serve appropriate version
            if ($size === 'thumb' && !empty($student['thumbnail_blob'])) {
                ImageService::serveImage($student['thumbnail_blob'], 'image/jpeg');
            } else {
                ImageService::serveImage($student['photo_blob'], $student['photo_mime'] ?? 'image/jpeg');
            }
        }
        
        // Final fallback: default avatar
        $this->serveDefaultAvatar();
    }
    
    /**
     * Serve default avatar SVG
     */
    private function serveDefaultAvatar(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200">
            <rect fill="#e2e8f0" width="200" height="200"/>
            <circle cx="100" cy="80" r="35" fill="#94a3b8"/>
            <path d="M100 120 Q70 140 50 180 H150 Q130 140 100 120 Z" fill="#94a3b8"/>
        </svg>';
        
        header('Content-Type: image/svg+xml');
        header('Cache-Control: public, max-age=86400');
        echo $svg;
        exit;
    }
