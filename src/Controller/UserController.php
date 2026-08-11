<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\OrderService;
use App\Service\UserService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class UserController extends AbstractController
{
    public const API_ENABLED_MSG = "L'accès API a été activé, utilisez vos identifiants habituels pour vous y connecter, puis utilisez le jeton JWT pour vos requêtes.";
    public const API_DISABLED_MSG = "Attention! L'accès API a été désactivé, même si vous avez encore un jeton JWT valide, vous ne pourrez plus l'utiliser.";

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly UserService $userService,
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
    #[IsCsrfTokenValid('app_user_delete', tokenKey: '_csrf_token')]
    public function delete(#[CurrentUser] User $user, Security $security): Response
    {
        try {
            $this->userService->deleteUserAccount($user);
            $security->logout(false);
            $this->addFlash('success', 'Votre compte a été supprimé avec succès.');
        } catch (\Exception $e) {
            $this->logger->error('Error while deleting user account', ['exception' => $e]);
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression de votre compte. Merci de nous contacter.');
        }
        return $this->redirectToRoute('app_home');
    }

    #[Route('/profile/toggle-api', name: 'app_user_toggle_api', methods: ['POST'])]
    #[IsCsrfTokenValid('app_user_toggle_api', tokenKey: '_csrf_token')]
    public function toggleApiAccess(#[CurrentUser] User $user): Response
    {
        try {
            $this->userService->toggleApiAccess($user);
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
