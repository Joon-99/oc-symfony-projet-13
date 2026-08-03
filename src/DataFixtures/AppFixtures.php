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
        $products = $this->createProducts($manager);

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
                'name' => 'Ergonomic Oak Standing Desk',
                'short' => 'Height-adjustable oak desk for healthy posture.',
                'long' => 'A premium solid-oak standing desk with whisper-quiet motorized lift, cable routing tray, and memory presets designed for all-day comfort.',
                'price' => '749.00',
                'stock' => 14,
            ],
            [
                'name' => 'Noise-Cancelling Travel Headphones X2',
                'short' => 'Foldable over-ear headphones with deep active noise cancelling.',
                'long' => 'These lightweight headphones deliver balanced studio tuning, adaptive ANC, and 40-hour battery life for flights, commutes, and focused work.',
                'price' => '229.90',
                'stock' => 35,
            ],
            [
                'name' => 'UltraBright 4K Webcam Pro',
                'short' => 'Crystal-clear video calls even in low-light rooms.',
                'long' => 'A 4K webcam with autofocus, HDR correction, integrated dual microphones, and magnetic privacy shutter for professional remote meetings.',
                'price' => '139.00',
                'stock' => 52,
            ],
            [
                'name' => 'Mechanical Keyboard Aurora TKL',
                'short' => 'Compact tenkeyless keyboard with tactile switches.',
                'long' => 'Engineered for speed and comfort, this hot-swappable keyboard includes dampened stabilizers, per-key backlight, and durable PBT keycaps.',
                'price' => '119.50',
                'stock' => 41,
            ],
            [
                'name' => 'Precision Wireless Mouse M8',
                'short' => 'Silent-click ergonomic mouse with programmable buttons.',
                'long' => 'Designed for productivity, the M8 offers multi-device pairing, high-precision optical tracking, and a sculpted grip for long sessions.',
                'price' => '69.99',
                'stock' => 68,
            ],
            [
                'name' => 'Smart LED Desk Lamp Halo',
                'short' => 'Eye-friendly lamp with tunable warmth and brightness.',
                'long' => 'A modern aluminum desk lamp featuring touch controls, auto-dimming ambient sensor, and flicker-free illumination for reading and coding.',
                'price' => '89.00',
                'stock' => 27,
            ],
            [
                'name' => 'USB-C Docking Station 12-in-1',
                'short' => 'Expand your laptop with display, power, and networking.',
                'long' => 'This dock adds dual display output, Gigabit Ethernet, fast SD readers, and 100W pass-through charging in a compact heat-dissipating shell.',
                'price' => '159.95',
                'stock' => 30,
            ],
            [
                'name' => 'Thermal Stainless Water Bottle 1L',
                'short' => 'Keeps beverages cold for 24h or hot for 12h.',
                'long' => 'Double-wall vacuum insulation, leak-proof cap, and durable powder coating make this bottle ideal for office, gym, and travel.',
                'price' => '34.90',
                'stock' => 120,
            ],
            [
                'name' => 'Portable SSD Thunder 2TB',
                'short' => 'High-speed external storage for creators and developers.',
                'long' => 'A rugged USB 3.2 Gen2 SSD with up to 1,050 MB/s transfer speed, hardware encryption support, and shock-resistant design.',
                'price' => '249.00',
                'stock' => 22,
            ],
            [
                'name' => 'Merino Wool Zip Hoodie',
                'short' => 'Breathable all-season hoodie with premium comfort.',
                'long' => 'Made from soft merino blend, this hoodie regulates temperature naturally and includes reinforced seams and secure zip pockets.',
                'price' => '129.00',
                'stock' => 45,
            ],
        ];

        $products = [];

        foreach ($productBlueprints as $blueprint) {
            $product = (new Product($blueprint['name']))
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
     * @param list<User> $regularUsers
     * @param list<Product> $products
     */
    private function createCartsForUsers(ObjectManager $manager, array $regularUsers, array $products): void
    {
        $userIndexes = $this->pickRandomKeys($regularUsers, 3);

        foreach ($userIndexes as $index) {
            $user = $regularUsers[$index];
            $cart = new Cart($user);

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
     * @param list<User> $regularUsers
     * @param list<Product> $products
     */
    private function createOrdersForUsers(ObjectManager $manager, array $regularUsers, array $products): void
    {
        $frequentBuyer = $regularUsers[0];
        $frequentBuyerOrderCount = $this->randomizer->getInt(2, 3);

        for ($i = 0; $i < $frequentBuyerOrderCount; $i++) {
            $this->createOrderForUser($manager, $frequentBuyer, $products);
        }

        $extraOrderCandidates = array_slice($regularUsers, 1);
        $extraBuyerCount = $this->randomizer->getInt(1, 2);
        $extraBuyerIndexes = $this->pickRandomKeys($extraOrderCandidates, $extraBuyerCount);

        foreach ($extraBuyerIndexes as $index) {
            $extraUser = $extraOrderCandidates[$index];
            $this->createOrderForUser($manager, $extraUser, $products);
        }
    }

    /**
     * @param list<Product> $products
     */
    private function createOrderForUser(ObjectManager $manager, User $user, array $products): void
    {
        $order = (new Order())
            ->setOwner($user);

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
     * @param array<mixed> $items
     * @return list<int>
     */
    private function pickRandomKeys(array $items, int $count): array
    {
        $keys = $this->randomizer->pickArrayKeys($items, $count);

        return array_values(array_map(static fn (int|string $key): int => (int) $key, $keys));
    }
}
