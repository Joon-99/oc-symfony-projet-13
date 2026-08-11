<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'products')]
#[Broadcast]
class Product extends BaseEntity
{
    #[ORM\Column(length: 255, nullable: false)]
    private string $name;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $picturePath = null;

    /** @var numeric-string|null */
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $price = null;

    #[ORM\Column(length: 255, nullable: false)]
    private string $shortDescription = '';

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    private string $longDescription = '';

    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    private bool $isPublished = false;

    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    private int $nbStock = 0;

    /**
     * @var Collection<int, CartItem>
     */
    #[ORM\OneToMany(targetEntity: CartItem::class, mappedBy: 'product', orphanRemoval: true)]
    private Collection $cartItems;

    public function __construct(string $name)
    {
        parent::__construct();
        $this->cartItems = new ArrayCollection();
        $this->setName($name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPicturePath(): ?string
    {
        return $this->picturePath;
    }

    public function setPicturePath(?string $picture): static
    {
        $this->picturePath = $picture;

        return $this;
    }

    /**
     * @return numeric-string|null
     */
    public function getPrice(): ?string
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

    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(string $shortDescription): static
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getLongDescription(): string
    {
        return $this->longDescription;
    }

    public function setLongDescription(string $longDescription): static
    {
        $this->longDescription = $longDescription;

        return $this;
    }

    public function isPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getNbStock(): ?int
    {
        return $this->nbStock;
    }

    public function setNbStock(int $nbStock): static
    {
        $this->nbStock = $nbStock;

        return $this;
    }

    /**
     * @return Collection<int, CartItem>
     */
    public function getCartItems(): Collection
    {
        return $this->cartItems;
    }

    public function addCartItem(CartItem $cartItem): static
    {
        if (!$this->cartItems->contains($cartItem)) {
            $this->cartItems->add($cartItem);
            $cartItem->setProduct($this);
        }

        return $this;
    }

    public function removeCartItem(CartItem $cartItem): static
    {
        $this->cartItems->removeElement($cartItem);

        return $this;
    }

    public function getProductReference(): string
    {
        return sprintf('PRD-%09d', $this->getId() ?? 0);
    }
}
