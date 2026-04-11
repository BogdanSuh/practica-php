<?php
namespace Validators;

use Illuminate\Database\Capsule\Manager as Capsule;

class UniqueValidator extends AbstractValidator
{
    protected string $message = 'Значение поля :field уже существует в системе';

    public function rule(): bool
    {
        if (empty($this->args[0]) || empty($this->args[1])) {
            return true;
        }

        $table = $this->args[0];
        $column = $this->args[1];

        $query = Capsule::table($table)->where($column, $this->value);

        if (isset($this->args[2])) {
            $query->where('id', '!=', $this->args[2]);
        }

        return !$query->exists();
    }
}