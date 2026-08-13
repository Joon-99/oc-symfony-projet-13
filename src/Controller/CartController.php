<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Exception\InsufficientStockException;
use App\Exception\ProductNotPublishedException;
use App\Form\AddToCartType;
use App\Service\CartService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class CartController extends AbstractController
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/cart', name: 'app_cart')]
    public function index(#[CurrentUser] User $user): Response
    {
        $cart = null;
        try {
            $cart = $this->cartService->getOrCreateCart($user);
        } catch (\Exception $e) {
            $this->logger->error('Error while fetching the cart', ['exception' => $e]);
            $this->addFlash('error', 'Une erreur est survenue lors de la récupération du panier.');
        }
        $cartItems = $cart ? $cart->getCartItems() : [];

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'cartItems' => $cartItems,
        ]);
    }

    #[Route('/cart/add/{product}', name: 'app_cart_add', methods: ['POST'])]
    public function add(Product $product, #[CurrentUser] User $user, Request $request): Response
    {
        $form = $this->createForm(AddToCartType::class, options: ['max_quantity' => $product->getNbStock() ?? 0]);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('error', 'La quantité renseignée est invalide.');

            return $this->redirectToRoute('app_home', ['product' => $product->getId()]);
        }

        /** @var array{quantity: int} $data */
        $data = $form->getData();
        $quantity = $data['quantity'];

        try {
            $this->cartService->setItemQuantity($user, $product, $quantity);
            $errMsg = 0 === $quantity ? 'Le produit a été retiré du panier.' : 'Le panier a été mis à jour.';
            $this->addFlash('success', $errMsg);
        } catch (InsufficientStockException $e) {
            $this->logger->warning('Insufficient stock while adding product to cart', ['exception' => $e]);
            $this->addFlash('error', "Stock insuffisant pour le produit : {$e->getMessage()}");
        } catch (ProductNotPublishedException $e) {
            $this->logger->warning('Attempted to add unpublished product to cart', ['exception' => $e]);
            $this->addFlash('error', "Le produit n'est pas disponible.");
        } catch (\Exception $e) {
            $this->logger->error('Error while adding an item to the cart', ['exception' => $e]);
            $this->addFlash('error', "Une erreur est survenue lors de l'ajout du produit au panier.");
        }

        return $this->redirectToRoute('app_home');
    }

    #[Route('/cart/empty', name: 'app_cart_empty', methods: ['POST'])]
    public function emptyCart(#[CurrentUser] User $user): Response
    {
        try {
            $this->cartService->emptyCart($user);
            $this->addFlash('success', 'Le panier a été vidé.');
        } catch (\Exception $e) {
            $this->logger->error('Error while emptying the cart', ['exception' => $e]);
            $this->addFlash('error', 'Une erreur est survenue lors de la vidange du panier.');
        }

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/checkout', name: 'app_cart_checkout', methods: ['POST'])]
    public function checkout(#[CurrentUser] User $user): Response
    {
        try {
            $this->cartService->checkout($user);
            $this->addFlash('success', 'La commande a été passée.');
        } catch (InsufficientStockException $e) {
            $this->logger->warning('Insufficient stock during checkout', ['exception' => $e]);
            $this->addFlash('error', "Stock insuffisant pour le produit : {$e->getMessage()}");
        } catch (ProductNotPublishedException $e) {
            $this->logger->error('Product not published during checkout', ['exception' => $e]);
            $this->addFlash('error', "Le produit n'est pas disponible.");
        } catch (\Exception $e) {
            $this->logger->error('Error on checkout', ['exception' => $e]);
            $this->addFlash('error', 'Une erreur est survenue lors de la création de la commande.');
        }

        return $this->redirectToRoute('app_cart');
    }
}
