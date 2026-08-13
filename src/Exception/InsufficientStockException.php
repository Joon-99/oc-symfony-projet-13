<?php

namespace App\Exception;

final class InsufficientStockException extends \DomainException
{
    public function __construct(string $productName, int $available, int $requested, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Stock insuffisant pour le produit "%s" : %d disponible, %d demandé.', $productName, $available, $requested),
            $code,
            $previous,
        );
    }
}
