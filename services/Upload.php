<?php

class Upload
{
    public static function saveProfileImage(array $file): ?string
    {
        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload error');
        }
        if ($file['size'] > 2 * 1024 * 1024) { // 2MB
            throw new RuntimeException('File too large');
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif'
        ];
        if (!isset($allowed[$mime])) {
            throw new RuntimeException('Invalid file type');
        }
        $ext = $allowed[$mime];
        $uploadsDir = dirname(__DIR__) . '/uploads';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }
        $filename = 'profile_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = $uploadsDir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new RuntimeException('Failed to move uploaded file');
        }
        // Return web path
        return BASE_URL . '/uploads/' . $filename;
    }
}


