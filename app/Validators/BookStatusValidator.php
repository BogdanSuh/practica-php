<?php
namespace Validators;

class BookStatusValidator extends AbstractValidator
{
    protected string $message = 'Поле :field содержит недопустимое значение';

    public function rule(): bool
    {
        $allowed = ['in_hall', 'reserved', 'issued'];
        return in_array($this->value, $allowed);
    }
}