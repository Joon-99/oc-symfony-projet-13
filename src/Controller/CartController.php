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

    #[Route('/cart/add/{product}', name: 'app_cart_add', methods: ['POST'])]
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

    #[Route('/cart/empty', name: 'app_cart_empty', methods: ['POST'])]
    public function emptyCart(#[CurrentUser] User $user): Response
    {
        try {
            $this->cartService->emptyCart($user);
            $this->addFlash('success', "Le panier a été vidé.");
        } catch (\Exception $e) {
            $this->logger->error(__METHOD__ . "Error while emptying the cart: " . $e->getMessage());
            $this->addFlash('error', "Une erreur est survenue lors de la vidange du panier.");
        }

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/checkout', name: 'app_cart_checkout', methods: ['POST'])]
    public function checkout(#[CurrentUser] User $user): Response
    {
        try {
            $this->cartService->checkout($user);
            $this->addFlash('success', "La commande a été passée.");
        } catch (\Exception $e) {
            $this->logger->error(__METHOD__ . "Error on checkout: " . $e->getMessage());
            $this->addFlash('error', "Une erreur est survenue lors de la création de la commande.");
        }

        return $this->redirectToRoute('app_cart');
    }
}
