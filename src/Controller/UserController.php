<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class UserController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function profile(#[CurrentUser] User $user, OrderService $orderService): Response
    {
        return $this->render('user/index.html.twig', [
            'user' => $user,
            'orders' => $orderService->getOrdersByUserSortedByDate($user),
        ]);
    }
}
