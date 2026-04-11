<?php
namespace Validators;

class FileValidator extends AbstractValidator
{
    protected string $message = 'Файл в поле :field не соответствует требованиям';

    public function rule(): bool
    {
        if (empty($this->value) || $this->value['error'] === UPLOAD_ERR_NO_FILE) {
            return true;
        }

        if ($this->value['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $allowedTypes = $this->args[0] ?? 'image/jpeg,image/png';
        $allowedTypesArray = explode(',', $allowedTypes);

        if (!in_array($this->value['type'], $allowedTypesArray)) {
            return false;
        }

        $maxSize = isset($this->args[1]) ? (int)$this->args[1] : 5242880;

        if ($this->value['size'] > $maxSize) {
            return false;
        }

        return true;
    }
}