<?php
namespace Validators;
//Базовый абстрактный класс валидатора
abstract class AbstractValidator
{
    protected string $field;
    protected $value;
    protected array $args = [];
    protected string $message = 'Поле :field не прошло валидацию';
    protected array $messageKeys = [];

    public function __construct(string $fieldName, $value, array $args = [], ?string $message = null)
    {
        $this->field = $fieldName;
        $this->value = $value;
        $this->args = $args;

        if ($message) {
            $this->message = $message;
        }

        $this->messageKeys = [
            ':value' => $this->value,
            ':field' => $this->field
        ];

        foreach ($this->args as $key => $arg) {
            $this->messageKeys[':arg' . ($key + 1)] = $arg;
        }
    }

    public function validate()
    {
        if (!$this->rule()) {
            return $this->messageError();
        }
        return true;
    }

    protected function messageError(): string
    {
        $message = $this->message;
        foreach ($this->messageKeys as $key => $value) {
            $message = str_replace($key, (string)$value, $message);
        }
        return $message;
    }

    abstract public function rule(): bool;
}