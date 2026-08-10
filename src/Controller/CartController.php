<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Service\CartService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class CartController extends AbstractController
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/cart', name: 'app_cart')]
    public function index(#[CurrentUser] User $user): Response
    {
        $cart = null;
        try {
            $cart = $this->cartService->getOrCreateCart($user);
        } catch (\Exception $e) {
            $this->logger->error(__METHOD__ . "Error while fetching the cart: " . $e->getMessage());
            $this->addFlash('error', "Une erreur est survenue lors de la récupération du panier.");
        }
        $cartItems = $cart ? $cart->getCartItems() : [];

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'cartItems' => $cartItems,
        ]);
    }

    #[Route('/cart/add/{product}', name: 'app_cart_add')]
    public function add(Product $product, #[CurrentUser] User $user, int $quantity = 1): Response
    {
        try {
            $this->cartService->addItemToCart($user, $product, $quantity);
            $this->addFlash('success', "Le produit a été ajouté au panier.");
        } catch (\Exception $e) {
            $this->logger->error(__METHOD__ . "Error while adding an item to the cart: " . $e->getMessage());
            $this->addFlash('error', "Une erreur est survenue lors de l'ajout du produit au panier.");
        }

        return $this->redirectToRoute('app_cart');
    }
}
