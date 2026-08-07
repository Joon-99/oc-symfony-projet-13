<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{
    #[Route('/product/{product}', name: 'app_product')]
    public function index(Product $product): Response
    {
        return $this->render('product/product_view.html.twig', [
            'product' => $product,
        ]);
    }
}
