<?php

namespace App\Service;

use App\Repository\ProductRepository;
use App\Entity\Product;

final class ProductService
{

    public function __construct(
        private readonly ProductRepository $productRepository,
    ) {
    }

    /**
     * @return Product[]
     */
    public function getDemoProducts(): array
    {
        return $this->productRepository->findDemoProducts();
    }

    /**
     * @return Product[]
     */
    public function getApiProducts(): array
    {
        return $this->productRepository->findDemoProducts();
    }
}