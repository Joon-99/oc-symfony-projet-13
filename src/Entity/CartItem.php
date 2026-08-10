<?php

namespace App\Entity;

use App\Repository\CartItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: CartItemRepository::class)]
#[ORM\Table(name: 'cart_items')]
#[Broadcast]
class CartItem extends BaseEntity
{
    #[ORM\Column(nullable: false)]
    #[Positive(message: 'La quantité doit être supérieure à zéro.')]
    private int $quantity = 1;

    #[ORM\ManyToOne(inversedBy: 'cartItems')]
    #[ORM\JoinColumn(nullable: false)]
    private Cart $cart;

    #[ORM\ManyToOne(inversedBy: 'cartItems')]
    #[ORM\JoinColumn(nullable: false)]
    private Product $product;

    /** @var numeric-string */
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: false)]
    #[NotBlank(message: 'Le prix ne peut pas être vide.')]
    #[Positive(message: 'Le prix doit être supérieur à zéro.')]
    #[Regex(
        pattern: '/^\d{1,8}(\.\d{1,2})?$/',
        message: 'Le prix ne peut pas dépasser 8 chiffres avant la virgule et 2 chiffres après.',
    )]
    private string $price;

    public function __construct(Cart $cart, int $quantity, Product $product)
    {
        parent::__construct();
        $this->setCart($cart);
        $this->setQuantity($quantity);
        $this->setProduct($product);

        $price = $product->getPrice();
        if ($price === null) {
            throw new \DomainException("Cannot add product {$product->getName()} to a cart: it has no price.");
        }
        $this->setPrice($price);
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function setCart(Cart $cart): static
    {
        $this->cart = $cart;

        return $this;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return numeric-string 
     */
    public function getPrice(): string
    {
        return $this->price;
    }

    /**
     * @param numeric-string $price 
     */
    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return numeric-string
     */
    public function getTotalPrice(): string
    {
        return bcmul($this->price, (string)$this->quantity, 2);
    }
}
