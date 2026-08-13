<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Form\AddToCartType;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class ProductController extends AbstractController
{
    #[Route('/product/{product}', name: 'app_product')]
    public function index(Product $product, #[CurrentUser] User $user, CartService $cartService): Response
    {
        $cartItem = $cartService->getProductInCart($user, $product);
        $addToCartForm = $this->createForm(AddToCartType::class, [
            'quantity' => $cartItem?->getQuantity() ?? 0,
        ], [
            'action' => $this->generateUrl('app_cart_add', ['product' => $product->getId()]),
            'max_quantity' => $product->getNbStock() ?? 0,
        ]);

        return $this->render('product/product_view.html.twig', [
            'product' => $product,
            'cartItem' => $cartItem,
            'addToCartForm' => $addToCartForm,
        ]);
    }
}
