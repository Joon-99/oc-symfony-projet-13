<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class UserService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CartService $cartService,
    ) {
    }

    /**
     * Toggles the API access for a user.
     *
     * @throws \Exception
     */
    public function toggleApiAccess(User $user): void
    {
        $user->setApiEnabled(!$user->isApiEnabled());
        $this->entityManager->flush();
    }

    /**
     * Deletes a user account and all associated data.
     *
     * Business rule: keep the order history for accounting, but remove the live user link.
     * The order keeps a copy of the buyer's name so it still identifies who bought it after
     * the account is deleted.
     *
     * @throws \Exception
     */
    public function deleteUserAccount(User $user): void
    {
        $this->entityManager->wrapInTransaction(function () use ($user): void {
            foreach ($user->getOrders() as $order) {
                $order->setArchiveBuyerFirstName($user->getFirstName());
                $order->setArchiveBuyerLastName($user->getLastName());
                $order->setOwner(null);
            }

            $cart = $user->getCart();
            if ($cart) {
                $this->cartService->emptyCart($user);
                $this->entityManager->remove($cart);
            }

            $this->entityManager->remove($user);
            $this->entityManager->flush();
        });
    }
}
