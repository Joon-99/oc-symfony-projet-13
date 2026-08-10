<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\User;
use App\Entity\Product;
use App\Entity\CartItem;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CartItemRepository;

final class CartService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CartItemRepository $cartItemRepo,)
    {
    }

    /**
     * Get the user's cart or create a new one if it doesn't exist.
     * 
     * @throws \Exception
     */
    public function getOrCreateCart(User $user): Cart
    {
        $cart = $user->getCart();

        if ($cart instanceof Cart) {
            return $cart;
        }

        $cart = new Cart($user);
        $this->entityManager->persist($cart);
        $this->entityManager->flush();

        return $cart;
    }

    /**
     * Add an item to the user's cart. If the item already exists in the cart, increase its quantity.
     * 
     * @throws \Exception
     */
    public function addItemToCart(User $user, Product $product, int $quantity = 1): void
    {
        $cart = $this->getOrCreateCart($user);
        $cartItem = $this->cartItemRepo->findFromCartByProduct($cart, $product);

        if ($cartItem) {
            $cartItem->setQuantity($cartItem->getQuantity() + $quantity);
        } else {
            $cartItem = new CartItem($cart, $quantity, $product);
            $cart->addCartItem($cartItem);
        }

        $this->entityManager->persist($cartItem);
        $this->entityManager->flush();
    }

    /**
     * Empty the user's cart by removing all items.
     * 
     * @throws \Exception
     */
    public function emptyCart(User $user): void
    {
        $cart = $this->getOrCreateCart($user);
        foreach ($cart->getCartItems() as $cartItem) {
            $this->entityManager->remove($cartItem);
        }
        $this->entityManager->flush();
    }

    /**
     * Validate the user's cart. This is a placeholder for actual validation logic.
     * 
     * @throws \Exception
     */
    public function validateCart(User $user): void
    {
        $cart = $this->getOrCreateCart($user);
        // Placeholder for actual validation logic
        // For example, you might check if all products are in stock
    }
}
