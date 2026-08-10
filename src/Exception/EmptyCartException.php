<?php

namespace App\Exception;

final class EmptyCartException extends \DomainException
{
    public function __construct(string $message = "The cart cannot be empty.", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
