<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\OrderService;
use App\Service\UserService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class UserController extends AbstractController
{
    public const API_ENABLED_MSG = "L'accès API a été activé, utilisez vos identifiants habituels pour vous y connecter, puis utilisez le jeton JWT pour vos requêtes.";
    public const API_DISABLED_MSG = "Attention! L'accès API a été désactivé, même si vous avez encore un jeton JWT valide, vous ne pourrez plus l'utiliser.";

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

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
    public function toggleApiAccess(#[CurrentUser] User $user, UserService $userService): Response
    {
        try {
            $userService->toggleApiAccess($user);
            $flashType = $user->isApiEnabled() ? 'success' : 'warning';
            $msgText = $user->isApiEnabled() ? self::API_ENABLED_MSG : self::API_DISABLED_MSG;
            $this->addFlash($flashType, $msgText);
        } catch (\Exception $e) {
            $this->addFlash('error', "Une erreur est survenue lors de la modification de l'accès API.");
            $this->logger->error('Error while toggling API access', ['exception' => $e]);
        }

        return $this->redirectToRoute('app_profile');
    }
}
