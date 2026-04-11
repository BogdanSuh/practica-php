<?php
namespace Src\Traits;

trait UploadTrait
{
    protected function uploadFile(array $file, string $directory, array $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'], int $maxSize = 5242880): string|false
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }

        if ($file['size'] > $maxSize) {
            return false;
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $directory . '/';

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        if (move_uploaded_file($file['tmp_name'], $uploadPath . $filename)) {
            return '/uploads/' . $directory . '/' . $filename;
        }

        return false;
    }
}