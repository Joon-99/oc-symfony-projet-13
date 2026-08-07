<?php

namespace App\Controller;

use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\CartItemRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
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
        private readonly EntityManagerInterface $entityManager,
        private readonly CartItemRepository $cartItemRepo,)
    {
    }

    #[Route('/cart', name: 'app_cart')]
    public function index(#[CurrentUser] User $user): Response
    {
        $cart = $this->cartService->getOrCreateCart($user);
        $cartItems = $cart->getCartItems();

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'cartItems' => $cartItems,
        ]);
    }

    #[Route('/cart/add/{product}', name: 'app_cart_add')]
    public function add(Product $product, #[CurrentUser] User $user): Response
    {
        //TODO refactor this into a service
        $cart = $this->cartService->getOrCreateCart($user);
        $cartItem = $this->cartItemRepo->findFromCartByProduct($cart, $product);
        if ($cartItem) {
            $cartItem->setQuantity($cartItem->getQuantity() + 1);
        } else {
            $cartItem = new CartItem($cart, 1, $product);
            $cart->addCartItem($cartItem);
        }

        try {
            $this->entityManager->persist($cart);
            $this->entityManager->persist($cartItem);
            $this->entityManager->flush();
            $this->addFlash('success', "Le produit a été ajouté au panier.");
        } catch (\Exception $e) {
            $this->addFlash('error', "Une erreur est survenue lors de l'ajout du produit au panier.");
        }

        return $this->redirectToRoute('app_cart');
    }
}
