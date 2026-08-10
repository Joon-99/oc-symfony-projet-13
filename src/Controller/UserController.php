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

    #[Route('/profile/delete', name: 'app_user_delete', methods: ['POST'])]
    public function delete(#[CurrentUser] User $user): Response
    {
        // Implement the logic to delete the user account here.
        return $this->redirectToRoute('app_home');
    }

    #[Route('/profile/toggle-api', name: 'app_user_toggle_api', methods: ['POST'])]
    public function toggleApiAccess(#[CurrentUser] User $user): Response
    {
        // Implement the logic to toggle the API access for the user here.
        return $this->redirectToRoute('app_profile');
    }
}