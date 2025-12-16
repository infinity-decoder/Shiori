<?php
// app/Services/ImageService.php
class ImageService
{
    // Allowed MIME types and extensions
    private static array $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * Validate an uploaded file (array from $_FILES).
     */
    public static function validateUpload(array $file): array
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['ok' => false, 'error' => 'No file uploaded.'];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'Upload error code ' . $file['error']];
        }

        $maxBytes = 3 * 1024 * 1024; // 3 MB
        if ($file['size'] > $maxBytes) {
            return ['ok' => false, 'error' => 'File too large (max 3 MB).'];
        }

        $info = @getimagesize($file['tmp_name']);
        if ($info === false) {
            return ['ok' => false, 'error' => 'Not a valid image file.'];
        }

        $mime = $info['mime'] ?? '';
        if (!array_key_exists($mime, self::$allowed)) {
            return ['ok' => false, 'error' => 'Unsupported image type. Allowed: jpg, png, webp.'];
        }

        return ['ok' => true];
    }

    /**
     * Save student photo with BLOB storage for portability
     * Returns ['ok'=>bool, 'filename'=>string, 'photo_blob'=>binary, 'photo_mime'=>string, 'photo_hash'=>string, 'thumbnail_blob'=>binary]
     */
    public static function saveStudentPhoto(array $file, int $studentId): array
    {
        $valid = self::validateUpload($file);
        if (!$valid['ok']) {
            return ['ok' => false, 'error' => $valid['error']];
        }

        $info = @getimagesize($file['tmp_name']);
        $mime = $info['mime'] ?? 'image/jpeg';
        $ext = self::$allowed[$mime] ?? 'jpg';

        // Optimize and resize image before storing
        $optimized = self::optimizeImage($file['tmp_name'], $mime);
        if (!$optimized['ok']) {
            return ['ok' => false, 'error' => $optimized['error']];
        }

        $uploadsDir = BASE_PATH . '/public/uploads/students';
        if (!is_dir($uploadsDir)) {
            if (!mkdir($uploadsDir, 0755, true) && !is_dir($uploadsDir)) {
                return ['ok' => false, 'error' => 'Unable to create uploads directory.'];
            }
        }

        // Stored filename convention: {id}.{ext}
        $filename = $studentId . '.' . $ext;
        $dest = $uploadsDir . DIRECTORY_SEPARATOR . $filename;

        // Save optimized image to filesystem cache
        $saved = @file_put_contents($dest, $optimized['data']);
        if ($saved === false) {
            return ['ok' => false, 'error' => 'Failed to save image file.'];
        }

        @chmod($dest, 0644);

        // Generate thumbnail
        $thumbData = null;
        $resized = self::createThumbnail($dest, 300, 300);
        
        if ($resized['ok']) {
            $thumbData = $resized['data'];
            $thumbPath = $uploadsDir . DIRECTORY_SEPARATOR . 'thumb_' . $studentId . '.jpg';
            @file_put_contents($thumbPath, $thumbData);
        }

        // Calculate hash for integrity
        $hash = hash('sha256', $optimized['data']);

        return [
            'ok' => true,
            'filename' => $filename,
            'photo_blob' => $optimized['data'],  // For DB storage
            'photo_mime' => $mime,
            'photo_hash' => $hash,
            'thumbnail_blob' => $thumbData
        ];
    }
    
    /**
     * Optimize image for web (resize if large, compress)
     */
    private static function optimizeImage(string $srcPath, string $mime): array
    {
        if (!function_exists('imagecreatetruecolor')) {
            // GD not available, return raw file
            $data = @file_get_contents($srcPath);
            return $data ? ['ok' => true, 'data' => $data] : ['ok' => false, 'error' => 'Cannot read image'];
        }

        // Create image resource
        switch ($mime) {
            case 'image/jpeg': $srcImg = @imagecreatefromjpeg($srcPath); break;
            case 'image/png':  $srcImg = @imagecreatefrompng($srcPath); break;
            case 'image/webp': 
                $srcImg = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($srcPath) : false;
                break;
            default: $srcImg = false;
        }

        if (!$srcImg) {
            $data = @file_get_contents($srcPath);
            return $data ? ['ok' => true, 'data' => $data] : ['ok' => false, 'error' => 'Cannot create image resource'];
        }

        $w = imagesx($srcImg);
        $h = imagesy($srcImg);

        // Resize if too large (max 1200px)
        $maxDim = 1200;
        if ($w > $maxDim || $h > $maxDim) {
            $ratio = min($maxDim / $w, $maxDim / $h);
            $newW = (int)round($w * $ratio);
            $newH = (int)round($h * $ratio);
            
            $resized = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($resized, $srcImg, 0, 0, 0, 0, $newW, $newH, $w, $h);
            imagedestroy($srcImg);
            $srcImg = $resized;
        }

        // Output as JPEG with compression
        ob_start();
        imagejpeg($srcImg, null, 85);
        $data = ob_get_clean();
        imagedestroy($srcImg);

        return ['ok' => true, 'data' => $data];
    }

    private static function createThumbnail(string $srcPath, int $maxW, int $maxH): array
    {
        $info = @getimagesize($srcPath);
        if ($info === false) {
            return ['ok' => false, 'error' => 'Cannot read image for thumbnail.'];
        }
        $mime = $info['mime'] ?? 'image/jpeg';

        // If GD functions are missing
        if (!function_exists('imagecreatetruecolor') || !function_exists('imagecopyresampled') || !function_exists('imagejpeg')) {
            return ['ok' => false, 'error' => 'GD extension not available.'];
        }

        // Create source image resource
        switch ($mime) {
            case 'image/jpeg': $srcImg = @imagecreatefromjpeg($srcPath); break;
            case 'image/png':  $srcImg = @imagecreatefrompng($srcPath); break;
            case 'image/webp': 
                if (function_exists('imagecreatefromwebp')) $srcImg = @imagecreatefromwebp($srcPath);
                else $srcImg = @imagecreatefromstring(file_get_contents($srcPath));
                break;
            default: $srcImg = @imagecreatefromstring(file_get_contents($srcPath));
        }

        if (!$srcImg) {
            return ['ok' => false, 'error' => 'Failed to create image resource.'];
        }

        $w = imagesx($srcImg);
        $h = imagesy($srcImg);
        if ($w <= 0 || $h <= 0) {
            imagedestroy($srcImg);
            return ['ok' => false, 'error' => 'Invalid image dimensions.'];
        }

        // maintain aspect ratio
        $ratio = min($maxW / $w, $maxH / $h, 1);
        $newW = (int)round($w * $ratio);
        $newH = (int)round($h * $ratio);

        $thumb = imagecreatetruecolor($newW, $newH);
        $white = imagecolorallocate($thumb, 255, 255, 255);
        imagefill($thumb, 0, 0, $white);

        imagecopyresampled($thumb, $srcImg, 0, 0, 0, 0, $newW, $newH, $w, $h);

        // Capture output
        ob_start();
        $saved = @imagejpeg($thumb, null, 85);
        $data = ob_get_clean();

        imagedestroy($thumb);
        imagedestroy($srcImg);

        if (!$saved || empty($data)) {
            return ['ok' => false, 'error' => 'Failed to generate thumbnail data.'];
        }
        return ['ok' => true, 'data' => $data];
    }

    /**
     * Delete both original photo and thumbnail if exist.
     */
    public static function deleteStudentPhoto(string $filename): void
    {
        $uploadsDir = BASE_PATH . '/public/uploads/students';
        $orig = $uploadsDir . DIRECTORY_SEPARATOR . $filename;
        $base = pathinfo($filename, PATHINFO_FILENAME);
        $thumb = $uploadsDir . DIRECTORY_SEPARATOR . 'thumb_' . $base . '.jpg';

        if (is_file($orig)) {
            @unlink($orig);
        }
        if (is_file($thumb)) {
            @unlink($thumb);
        }
    }
    
    /**
     * Regenerate filesystem cache from database BLOB
     */
    public static function regenerateFromBlob(int $studentId, string $blob, string $mime): ?string
    {
        $uploadsDir = BASE_PATH . '/public/uploads/students';
        if (!is_dir($uploadsDir)) {
            @mkdir($uploadsDir, 0755, true);
        }

        $ext = self::$allowed[$mime] ?? 'jpg';
        $filename = $studentId . '.' . $ext;
        $dest = $uploadsDir . DIRECTORY_SEPARATOR . $filename;

        $saved = @file_put_contents($dest, $blob);
        if ($saved === false) {
            return null;
        }

        @chmod($dest, 0644);
        
        // Also regenerate thumbnail
        $resized = self::createThumbnail($dest, 300, 300);
        if ($resized['ok']) {
            $thumbPath = $uploadsDir . DIRECTORY_SEPARATOR . 'thumb_' . $studentId . '.jpg';
            @file_put_contents($thumbPath, $resized['data']);
        }

        return $filename;
    }
    
    /**
     * Serve image with proper headers
     */
    public static function serveImage(string $data, string $mime): void
    {
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . strlen($data));
        header('Cache-Control: public, max-age=2592000'); // 30 days
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 2592000) . ' GMT');
        echo $data;
        exit;
    }
}
