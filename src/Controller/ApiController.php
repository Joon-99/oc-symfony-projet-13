<?php

namespace App\Controller;

use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class ApiController extends AbstractController
{
    #[Route('/api/products', name: 'app_api_products', methods: ['GET'])]
    public function products(ProductService $productService, SerializerInterface $serializer): JsonResponse
    {
        $products = $productService->getApiProducts();
        $serializedProducts = $serializer->serialize($products, 'json', ['groups' => ['api']]);

        return new JsonResponse(
            data: $serializedProducts,
            status: Response::HTTP_OK,
            headers: [],
            json: true,
        );
    }
}
