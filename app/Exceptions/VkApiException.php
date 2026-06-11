<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class VkApiException extends RuntimeException
{
    public function __construct(
        string $message = 'VK API error',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
