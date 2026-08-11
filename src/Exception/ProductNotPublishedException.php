<?php

namespace App\Exception;

final class ProductNotPublishedException extends \DomainException
{
    public function __construct(string $message = 'The product is not published and cannot be added to the cart.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
