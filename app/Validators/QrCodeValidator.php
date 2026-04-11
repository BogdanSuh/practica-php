<?php
namespace Validators;

class QrCodeValidator extends AbstractValidator
{
    protected string $message = 'Поле :field должно соответствовать формату QR-кода (QR-XXXXX)';

    public function rule(): bool
    {
        if (empty($this->value)) {
            return true;
        }

        return preg_match('/^QR-[A-Z0-9]{3,10}$/i', $this->value);
    }
}