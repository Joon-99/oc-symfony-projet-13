<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Exception\EmptyCartException;
use App\Exception\MissingCartException;
use App\Exception\ProductNotPublishedException;
use App\Repository\CartItemRepository;
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
     * Add an item to the user's cart. If the item already exists in the cart, increase its quantity.
     *
     * @throws ProductNotPublishedException
     * @throws \Exception
     */
    public function addItemToCart(User $user, Product $product, int $quantity = 1): void
    {
        if (!$product->isPublished()) {
            throw new ProductNotPublishedException();
        }
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
     * Place an order from the user's cart. The cart is only emptied once the order has been persisted.
     *
     * @throws EmptyCartException
     * @throws MissingCartException
     * @throws \Exception
     */
    public function checkout(User $user): Order
    {
        // TODO: check stock
        $order = new Order($user);

        $this->entityManager->wrapInTransaction(function () use ($order, $user): void {
            $this->entityManager->persist($order);
            $this->entityManager->flush();
            $this->emptyCart($user);
        });

        return $order;
    }
}
