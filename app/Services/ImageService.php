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
     * Save student photo. Saves original and generates thumbnail.
     * Returns ['ok'=>bool, 'filename' => stored filename (relative), 'error'=>string]
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

        $uploadsDir = BASE_PATH . '/public/uploads/students';
        if (!is_dir($uploadsDir)) {
            if (!mkdir($uploadsDir, 0755, true) && !is_dir($uploadsDir)) {
                return ['ok' => false, 'error' => 'Unable to create uploads directory.'];
            }
        }

        // Stored filename convention: {id}.{ext}
        $filename = $studentId . '.' . $ext;
        $dest = $uploadsDir . DIRECTORY_SEPARATOR . $filename;

        // Primary move; try move_uploaded_file, otherwise fallback to copy
        $moved = false;
        if (is_uploaded_file($file['tmp_name']) && @move_uploaded_file($file['tmp_name'], $dest)) {
            $moved = true;
        } else {
            // fallback: copy then unlink
            if (@copy($file['tmp_name'], $dest)) {
                @unlink($file['tmp_name']);
                $moved = true;
            }
        }

        if (!$moved) {
            return ['ok' => false, 'error' => 'Failed to move uploaded file. Check permissions on uploads folder.'];
        }

        // Make file readable by webserver
        if (function_exists('chmod')) {
            @chmod($dest, 0644);
        }

        // Generate thumbnail (max 300x300) saved as thumb_{id}.jpg for consistent display
        $thumbPath = $uploadsDir . DIRECTORY_SEPARATOR . 'thumb_' . $studentId . '.jpg';

        $resized = self::createThumbnail($dest, $thumbPath, 300, 300);
        if (!$resized['ok']) {
            // Not critical: log the error (but keep original)
            try {
                $logDir = BASE_PATH . '/storage/logs';
                if (is_dir($logDir)) {
                    @file_put_contents($logDir . '/image_errors.log', '['.date('c').'] Thumb error for '.$filename.': '.$resized['error'].PHP_EOL, FILE_APPEND);
                }
            } catch (Throwable $_) {
                // ignore
            }
            // still consider upload OK
            return ['ok' => true, 'filename' => $filename];
        }

        if (function_exists('chmod')) {
            @chmod($thumbPath, 0644);
        }

        return ['ok' => true, 'filename' => $filename];
    }

    private static function createThumbnail(string $srcPath, string $destPath, int $maxW, int $maxH): array
    {
        $info = @getimagesize($srcPath);
        if ($info === false) {
            return ['ok' => false, 'error' => 'Cannot read image for thumbnail.'];
        }
        $mime = $info['mime'] ?? 'image/jpeg';

        // If GD functions are missing, abort thumbnail creation gracefully
        if (!function_exists('imagecreatetruecolor') || !function_exists('imagecopyresampled') || !function_exists('imagejpeg')) {
            return ['ok' => false, 'error' => 'GD extension not available for thumbnail generation.'];
        }

        // Create source image resource based on mime
        switch ($mime) {
            case 'image/jpeg':
                $srcImg = @imagecreatefromjpeg($srcPath);
                break;
            case 'image/png':
                $srcImg = @imagecreatefrompng($srcPath);
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $srcImg = @imagecreatefromwebp($srcPath);
                } else {
                    $srcImg = @imagecreatefromstring(file_get_contents($srcPath));
                }
                break;
            default:
                $srcImg = @imagecreatefromstring(file_get_contents($srcPath));
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

        // White background for JPEG; preserve for PNG/WebP by copying background where possible
        $white = imagecolorallocate($thumb, 255, 255, 255);
        imagefill($thumb, 0, 0, $white);

        imagecopyresampled($thumb, $srcImg, 0, 0, 0, 0, $newW, $newH, $w, $h);

        // Save as JPEG (most compatible) with quality 85
        $saved = @imagejpeg($thumb, $destPath, 85);

        imagedestroy($thumb);
        imagedestroy($srcImg);

        if (!$saved) {
            return ['ok' => false, 'error' => 'Failed to save thumbnail.'];
        }
        return ['ok' => true];
    }

    /**
     * Delete both original photo and thumbnail if exist.
     */
    public static function deleteStudentPhoto(string $filename): void
    {
        $uploadsDir = BASE_PATH . '/public/uploads/students';
        $orig = $uploadsDir . DIRECTORY_SEPARATOR . $filename;
        // derive base name (filename without ext) to find thumb_{basename}.jpg
        $base = pathinfo($filename, PATHINFO_FILENAME);
        $thumb = $uploadsDir . DIRECTORY_SEPARATOR . 'thumb_' . $base . '.jpg';

        if (is_file($orig)) {
            @unlink($orig);
        }
        if (is_file($thumb)) {
            @unlink($thumb);
        }
    }
}
