<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(): Response
    {
        return $this->render('login/login.html.twig', [
            'controller_name' => 'LoginController',
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Controller can be blank: it will be intercepted by the logout key on your firewall.
    }

    #[Route('/register', name: 'app_register')]
    public function register(): Response
    {
        return $this->render('login/register.html.twig', [
            'controller_name' => 'LoginController',
        ]);
    }
}

