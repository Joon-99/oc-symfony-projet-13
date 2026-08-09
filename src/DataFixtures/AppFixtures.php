<?php

namespace App\DataFixtures;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Random\Engine\Mt19937;
use Random\Randomizer;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private const DEFAULT_PASSWORD = 'password';

    private readonly Randomizer $randomizer;

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        $this->randomizer = new Randomizer(new Mt19937(20260803));
    }

    public function load(ObjectManager $manager): void
    {
        /** @var non-empty-list<Product> $products */
        $products = $this->createProducts($manager);

        /** @var non-empty-list<User> $regularUsers */
        [$adminUser, $regularUsers] = $this->createUsers($manager);

        $this->createCartsForUsers($manager, $regularUsers, $products);

        $this->createOrdersForUsers($manager, $regularUsers, $products);

        $manager->flush();
    }

    /**
     * @return list<Product>
     */
    private function createProducts(ObjectManager $manager): array
    {
        $productBlueprints = [
            [
                'name' => "Kit d'hygiène recyclable",
                'short' => 'Pour une salle de bain éco-friendly',
                'long' => 'Un kit de salle de bain pensé pour réduire les déchets avec des essentiels réutilisables, durables et élégants au quotidien.',
                'picture' => 'images/products/kit-hygiene.jpg',
                'price' => '24.99',
                'stock' => 18,
            ],
            [
                'name' => 'Shot Tropical',
                'short' => 'Fruits frais, pressés à froid',
                'long' => 'Un shot fruité et énergisant, élaboré avec des fruits sélectionnés et pressés à froid pour conserver toute leur fraîcheur.',
                'picture' => 'images/products/shot-tropical.jpg',
                'price' => '4.50',
                'stock' => 60,
            ],
            [
                'name' => 'Gourde en bois',
                'short' => '50cl, bois d’olivier',
                'long' => 'Cette gourde en bois d’olivier offre un design naturel et chaleureux, idéal pour les boissons chaudes ou fraîches au quotidien.',
                'picture' => 'images/products/gourde-bois.jpg',
                'price' => '16.90',
                'stock' => 32,
            ],
            [
                'name' => 'Disques Démaquillants x3',
                'short' => 'Solution efficace pour vous démaquiller en douceur',
                'long' => 'Lot de trois disques démaquillants doux et efficaces, conçus pour retirer le maquillage en toute simplicité tout en respectant la peau.',
                'picture' => 'images/products/disques-demaquillants.jpg',
                'price' => '19.90',
                'stock' => 28,
            ],
            [
                'name' => 'Bougie Lavande & Patchouli',
                'short' => 'Cire naturelle',
                'long' => 'Une bougie artisanale à la cire naturelle, aux notes apaisantes de lavande et de patchouli pour créer une ambiance relaxante.',
                'picture' => 'images/products/bougie-lavande.jpg',
                'price' => '32.00',
                'stock' => 25,
            ],
            [
                'name' => 'Brosse à dent',
                'short' => 'Bois de hêtre rouge issu de forêts gérées durablement',
                'long' => 'Cette brosse à dents au manche en bois de hêtre rouge allie esthétique naturelle et engagement écologique pour un rituel quotidien plus responsable.',
                'picture' => 'images/products/brosse-a-dent.jpg',
                'price' => '5.40',
                'stock' => 40,
            ],
            [
                'name' => 'Kit couvert en bois',
                'short' => 'Revêtement Bio en olivier & sac de transport',
                'long' => 'Un kit de couvert en bois élégant et robuste, accompagné d’un sac de transport pratique pour les repas en déplacement.',
                'picture' => 'images/products/kit-couverts-bois.jpg',
                'price' => '12.30',
                'stock' => 24,
            ],
            [
                'name' => 'Nécessaire, déodorant Bio',
                'short' => '50ml déodorant à l’eucalyptus',
                'long' => "Déodorant Nécessaire, une formule révolutionnaire composée exclusivement d'ingrédients naturels pour une protection efficace et bienfaisante.\n\n" .
                    "Chaque flacon de 50 ml renferme le secret d'une fraîcheur longue durée, sans compromettre votre bien-être ni l'environnement. Conçu avec soin, ce déodorant allie le pouvoir antibactérien des extraits de plantes aux vertus apaisantes des huiles essentielles, assurant une sensation de confort toute la journée.\n\n" .
                    "Grâce à sa formule non irritante et respectueuse de votre peau, Nécessaire offre une alternative saine aux déodorants conventionnels, tout en préservant l'équilibre naturel de votre corps.",
                'picture' => 'images/products/deodorant-bio.jpg',
                'price' => '8.50',
                'stock' => 36,
            ],
            [
                'name' => 'Savon Bio',
                'short' => 'Thé, Orange & Girofle',
                'long' => 'Un savon bio au parfum enveloppant de thé, orange et girofle, formulé pour apporter douceur et caractère à votre rituel d’hygiène.',
                'picture' => 'images/products/savon-bio.jpg',
                'price' => '18.90',
                'stock' => 30,
            ],
        ];

        $products = [];

        foreach ($productBlueprints as $blueprint) {
            $product = (new Product($blueprint['name']))
                ->setPicturePath($blueprint['picture'])
                ->setShortDescription($blueprint['short'])
                ->setLongDescription($blueprint['long'])
                ->setPrice($blueprint['price'])
                ->setNbStock($blueprint['stock'])
                ->setIsPublished(true);

            $manager->persist($product);
            $products[] = $product;
        }

        return $products;
    }

    /**
     * @return array{0: User, 1: list<User>}
     */
    private function createUsers(ObjectManager $manager): array
    {
        $adminUser = (new User())
            ->setEmail('admin@green-goodies.com')
            ->setFirstName('Admin')
            ->setLastName('Manager')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($this->passwordHasher->hashPassword(new User(), self::DEFAULT_PASSWORD));

        $manager->persist($adminUser);

        $regularUserBlueprints = [
            ['email' => 'alice.martin@green-goodies.com', 'firstName' => 'Alice', 'lastName' => 'Martin'],
            ['email' => 'hugo.bernard@green-goodies.com', 'firstName' => 'Hugo', 'lastName' => 'Bernard'],
            ['email' => 'lea.dubois@green-goodies.com', 'firstName' => 'Lea', 'lastName' => 'Dubois'],
            ['email' => 'maxime.leroy@green-goodies.com', 'firstName' => 'Maxime', 'lastName' => 'Leroy'],
            ['email' => 'nora.faure@green-goodies.com', 'firstName' => 'Nora', 'lastName' => 'Faure'],
        ];

        $regularUsers = [];

        foreach ($regularUserBlueprints as $blueprint) {
            $user = (new User())
                ->setEmail($blueprint['email'])
                ->setFirstName($blueprint['firstName'])
                ->setLastName($blueprint['lastName'])
                ->setRoles([])
                ->setPassword($this->passwordHasher->hashPassword(new User(), self::DEFAULT_PASSWORD));

            $manager->persist($user);
            $regularUsers[] = $user;
        }

        return [$adminUser, $regularUsers];
    }

    /**
     * @param non-empty-list<User>    $regularUsers
     * @param non-empty-list<Product> $products
     */
    private function createCartsForUsers(ObjectManager $manager, array $regularUsers, array $products): void
    {
        $userIndexes = $this->pickRandomKeys($regularUsers, 3);

        foreach ($userIndexes as $index) {
            $user = $regularUsers[$index];
            $cart = new Cart($user);

            /** @var int<2, 5> $itemsCount */
            $itemsCount = $this->randomizer->getInt(2, 5);
            $productIndexes = $this->pickRandomKeys($products, $itemsCount);

            foreach ($productIndexes as $productIndex) {
                $cartItem = (new CartItem($cart, $this->randomizer->getInt(1, 4)))
                    ->setProduct($products[$productIndex]);

                $cart->addCartItem($cartItem);
            }

            $manager->persist($cart);
        }
    }

    /**
     * @param non-empty-list<User>    $regularUsers
     * @param non-empty-list<Product> $products
     */
    private function createOrdersForUsers(ObjectManager $manager, array $regularUsers, array $products): void
    {
        $frequentBuyer = $regularUsers[0];
        $frequentBuyerOrderCount = $this->randomizer->getInt(2, 3);

        for ($i = 0; $i < $frequentBuyerOrderCount; ++$i) {
            $this->createOrderForUser($manager, $frequentBuyer, $products);
        }

        /** @var non-empty-list<User> $extraOrderCandidates */
        $extraOrderCandidates = array_slice($regularUsers, 1);
        /** @var int<1, 2> $extraBuyerCount */
        $extraBuyerCount = $this->randomizer->getInt(1, 2);
        $extraBuyerIndexes = $this->pickRandomKeys($extraOrderCandidates, $extraBuyerCount);

        foreach ($extraBuyerIndexes as $index) {
            $extraUser = $extraOrderCandidates[$index];
            $this->createOrderForUser($manager, $extraUser, $products);
        }
    }

    /**
     * @param non-empty-list<Product> $products
     */
    private function createOrderForUser(ObjectManager $manager, User $user, array $products): void
    {
        $order = (new Order())
            ->setOwner($user);

        /** @var int<1, 4> $itemsCount */
        $itemsCount = $this->randomizer->getInt(1, 4);
        $productIndexes = $this->pickRandomKeys($products, $itemsCount);

        $total = 0.0;

        foreach ($productIndexes as $productIndex) {
            $product = $products[$productIndex];
            $quantity = $this->randomizer->getInt(1, 3);
            $unitAmount = $product->getPrice() ?? '0.00';

            $orderItem = (new OrderItem($order, $quantity, $unitAmount))
                ->setProduct($product);

            $order->addOrderItem($orderItem);
            $manager->persist($orderItem);

            $total += ((float) $unitAmount) * $quantity;
        }

        $order->setTotalAmount(number_format($total, 2, '.', ''));

        $manager->persist($order);
    }

    /**
     * @param non-empty-array<mixed> $items
     * @param int<1, max> $count
     *
     * @return list<int>
     */
    private function pickRandomKeys(array $items, int $count): array
    {
        $keys = $this->randomizer->pickArrayKeys($items, $count);

        return array_values(array_map(static fn (int|string $key): int => (int) $key, $keys));
    }
}
