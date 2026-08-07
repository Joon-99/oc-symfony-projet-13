<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class CartService
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

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
}
