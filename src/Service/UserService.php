<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class UserService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager)
    {
    }

    /** 
     * Toggles the API access for a user.
     * @throws \Exception
     */
    public function toggleApiAccess(User $user): void
    {
        $user->setApiEnabled(!$user->isApiEnabled());
        $this->entityManager->flush();
    }

}
