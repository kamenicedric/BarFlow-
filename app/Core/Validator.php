<?php

declare(strict_types=1);

namespace App\Core;

class Validator
{
    private array $errors = [];

    public function required(array $data, array $fields): self
    {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                $this->errors[$field][] = 'Champ requis';
            }
        }

        return $this;
    }

    public function numeric(array $data, array $fields): self
    {
        foreach ($fields as $field) {
            if (isset($data[$field]) && $data[$field] !== '' && !is_numeric($data[$field])) {
                $this->errors[$field][] = 'Doit etre numerique';
            }
        }

        return $this;
    }

    public function min(array $data, string $field, int $length): self
    {
        if (isset($data[$field]) && mb_strlen((string) $data[$field]) < $length) {
            $this->errors[$field][] = "Minimum {$length} caracteres";
        }

        return $this;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
