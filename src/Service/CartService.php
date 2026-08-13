<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Exception\EmptyCartException;
use App\Exception\InsufficientStockException;
use App\Exception\MissingCartException;
use App\Exception\ProductNotPublishedException;
use App\Repository\CartItemRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;

final class CartService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CartItemRepository $cartItemRepo, )
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
     * Set the quantity of a product in the user's cart. A quantity of 0 removes the item from the cart.
     *
     * @throws ProductNotPublishedException
     * @throws InsufficientStockException
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function setItemQuantity(User $user, Product $product, int $quantity): void
    {
        if ($quantity < 0) {
            throw new \InvalidArgumentException('Quantity cannot be negative.');
        }

        $cart = $this->getOrCreateCart($user);
        $cartItem = $this->cartItemRepo->findFromCartByProduct($cart, $product);

        if (0 === $quantity) {
            if ($cartItem) {
                $cart->removeCartItem($cartItem);
                $this->entityManager->remove($cartItem);
                $this->entityManager->flush();
            }

            return;
        }

        if (!$product->isPublished()) {
            throw new ProductNotPublishedException();
        }

        if ($quantity > ($product->getNbStock() ?? 0)) {
            throw new InsufficientStockException($product->getName(), $product->getNbStock() ?? 0, $quantity);
        }

        if ($cartItem) {
            $cartItem->setQuantity($quantity);
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
     * Place an order from the user's cart.
     *
     * @throws EmptyCartException
     * @throws InsufficientStockException
     * @throws MissingCartException
     * @throws \Exception
     */
    public function checkout(User $user): Order
    {
        return $this->entityManager->wrapInTransaction(function () use ($user): Order {
            $cart = $this->getOrCreateCart($user);

            // sorts $cartItems in a fixed order across all checkouts so two concurrent
            // transactions locking the same products can never deadlock on each other (avoids scenarios like
            // checkout 1 locking product B and waiting for product A to unlock while
            // checkout 2 locking product A and waiting for product B to unlock).
            $cartItems = $cart->getCartItems()->toArray();
            usort($cartItems, static fn (CartItem $a, CartItem $b): int => $a->getProduct()->getId() <=> $b->getProduct()->getId());

            // Row-lock and re-check each product's stock inside the transaction: it may have
            // changed since the item was added to the cart
            foreach ($cartItems as $cartItem) {
                $product = $this->entityManager->find(Product::class, $cartItem->getProduct()->getId(), LockMode::PESSIMISTIC_WRITE);
                if (!$product?->isPublished()) {
                    throw new ProductNotPublishedException();
                }
                $nbAvailable = $product->getNbStock() ?? 0;

                if ($nbAvailable < $cartItem->getQuantity()) {
                    throw new InsufficientStockException($cartItem->getProduct()->getName(), $nbAvailable, $cartItem->getQuantity());
                }

                $product->setNbStock($nbAvailable - $cartItem->getQuantity());
            }

            $order = new Order($user);
            $this->entityManager->persist($order);
            $this->entityManager->flush();
            $this->emptyCart($user);

            return $order;
        });
    }

    /**
     * @throws \Exception
     */
    public function getProductInCart(User $user, Product $product): ?CartItem
    {
        $cart = $this->getOrCreateCart($user);

        return $this->cartItemRepo->findFromCartByProduct($cart, $product);
    }
}
