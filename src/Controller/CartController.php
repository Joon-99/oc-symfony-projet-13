<?php

namespace App\Controller;

use App\Entity\CartItem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(): Response
    {
        return $this->render('cart/index.html.twig', [
            'controller_name' => 'CartController',
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add')]
    public function add(CartItem $item): Response
    {
        // Logic to add the product to the cart goes here
        return $this->redirectToRoute('app_cart');
    }
}
