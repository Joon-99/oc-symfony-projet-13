<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginFormType;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class LoginController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        $form = $this->createForm(LoginFormType::class, [
            'email' => $authenticationUtils->getLastUsername(),
        ], [
            'action' => $this->generateUrl('app_login'),
            'method' => 'POST',
        ]);

        return $this->render('login/login.html.twig', [
            'loginForm' => $form,
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // This method is intercepted by the firewall
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request,
                            UserPasswordHasherInterface $userPasswordHasher,
                            EntityManagerInterface $entityManager,
                            Security $security): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var string $plainPassword */
                $plainPassword = $form->get('plainPassword')->getData();

                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
                $entityManager->persist($user);
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('error', "Une erreur est survenue lors de l'inscription. Veuillez réessayer plus tard.");
                $this->logger->error('Error during user registration', ['exception' => $e]);
                return $this->redirectToRoute('app_register');
            }

            $this->addFlash('success', "Inscription réussie ! Vous êtes maintenant connecté.");

            $security->login($user, 'form_login', 'main');

            return $this->redirectToRoute('app_home');
        }
        return $this->render('login/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}

