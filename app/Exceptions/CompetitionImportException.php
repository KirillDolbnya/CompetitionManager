<?php

namespace App\Exceptions;

use Exception;

class CompetitionImportException extends Exception
{
    public function __construct(
        private readonly array $errors,
    )
    {
        parent::__construct('Эксель файл содержит ошибки');
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
