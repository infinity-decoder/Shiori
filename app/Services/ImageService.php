<?php
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

        $info = getimagesize($file['tmp_name']);
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

        $info = getimagesize($file['tmp_name']);
        $mime = $info['mime'];
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

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return ['ok' => false, 'error' => 'Failed to move uploaded file.'];
        }

        // Generate thumbnail (max 300x300) saved as thumb_{id}.jpg for consistent display
        $thumbPath = $uploadsDir . DIRECTORY_SEPARATOR . 'thumb_' . $studentId . '.jpg';
        $resized = self::createThumbnail($dest, $thumbPath, 300, 300);
        if (!$resized['ok']) {
            // Not critical; keep original
            return ['ok' => true, 'filename' => $filename];
        }

        return ['ok' => true, 'filename' => $filename];
    }

    private static function createThumbnail(string $srcPath, string $destPath, int $maxW, int $maxH): array
    {
        $info = getimagesize($srcPath);
        if ($info === false) {
            return ['ok' => false, 'error' => 'Cannot read image for thumbnail.'];
        }
        $mime = $info['mime'];

        switch ($mime) {
            case 'image/jpeg':
                $srcImg = imagecreatefromjpeg($srcPath);
                break;
            case 'image/png':
                $srcImg = imagecreatefrompng($srcPath);
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $srcImg = imagecreatefromwebp($srcPath);
                } else {
                    // fallback convert via JPEG if webp functions not available
                    $srcImg = imagecreatefromstring(file_get_contents($srcPath));
                }
                break;
            default:
                $srcImg = imagecreatefromstring(file_get_contents($srcPath));
        }
        if (!$srcImg) {
            return ['ok' => false, 'error' => 'Failed to create image resource.'];
        }

        $w = imagesx($srcImg);
        $h = imagesy($srcImg);

        // maintain aspect ratio
        $ratio = min($maxW / $w, $maxH / $h, 1);
        $newW = (int)round($w * $ratio);
        $newH = (int)round($h * $ratio);

        $thumb = imagecreatetruecolor($newW, $newH);
        // for PNG/WebP preserve transparency
        imagefill($thumb, 0, 0, imagecolorallocate($thumb, 255, 255, 255));
        imagecopyresampled($thumb, $srcImg, 0, 0, 0, 0, $newW, $newH, $w, $h);

        // Save as JPEG (most compatible) with quality 85
        $saved = imagejpeg($thumb, $destPath, 85);
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
        $thumb = $uploadsDir . DIRECTORY_SEPARATOR . 'thumb_' . pathinfo($filename, PATHINFO_FILENAME) . '.jpg';

        if (is_file($orig)) {
            @unlink($orig);
        }
        if (is_file($thumb)) {
            @unlink($thumb);
        }
    }
}
