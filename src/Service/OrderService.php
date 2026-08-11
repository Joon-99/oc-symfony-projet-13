<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\User;
use App\Repository\OrderRepository;

final class OrderService
{
    public function __construct(
        private readonly OrderRepository $orderRepo, )
    {
    }

    /**
     * Provides the orders of a user sorted by order date.
     *
     * @param 'ASC'|'DESC' $orderedBy
     *
     * @return Order[]
     */
    public function getOrdersByUserSortedByDate(User $user, string $orderedBy = 'DESC'): array
    {
        return $this->orderRepo->findByUserSortedByDate($user, $orderedBy);
    }
}
