<?php

namespace App\Exception;

final class MissingCartException extends \DomainException
{
    public function __construct(string $message = 'The user does not have a cart.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
