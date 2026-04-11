<?php
namespace Validators;

class YearValidator extends AbstractValidator
{
    protected string $message = 'Год издания должен быть между :arg1 и :arg2';

    public function rule(): bool
    {
        if (empty($this->value)) {
            return true;
        }

        $min = $this->args[0] ?? 1000;
        $max = $this->args[1] ?? date('Y');

        return $this->value >= $min && $this->value <= $max;
    }
}