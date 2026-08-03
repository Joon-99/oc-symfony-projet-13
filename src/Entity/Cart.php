<?php

namespace App\Entity;

use App\Entity\BaseEntity;
use App\Repository\CartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: CartRepository::class)]
#[ORM\Table(name: 'carts')]
#[Broadcast]
class Cart extends BaseEntity
{
    #[ORM\OneToOne(inversedBy: 'cart')]
    #[ORM\JoinColumn(nullable: false, unique: true)]
    private User $owner;

    /**
     * @var Collection<int, CartItem>
     */
    #[ORM\OneToMany(targetEntity: CartItem::class, mappedBy: 'cart', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $cartItems;

    public function __construct(User $owner)
    {
        parent::__construct();
        $this->setOwner($owner);
        $this->cartItems = new ArrayCollection();
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): static
    {
        if ($owner->getCart() instanceof Cart && $owner->getCart() !== $this) {
            throw new \LogicException('Cannot set a new owner when the user already has a cart. Remove the existing cart from the user first.');
        }
    
        if (isset($this->owner)) {
            if ($this->owner === $owner) {
                return $this;
            } else {
                throw new \LogicException('Cannot change the owner of the cart. Remove the existing cart from the user first.');
            }
        }
        
        $this->owner = $owner;
        if ($owner->getCart() !== $this) {
            $owner->setCart($this);
        }

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
            $cartItem->setCart($this);
        }

        return $this;
    }

    public function removeCartItem(CartItem $cartItem): static
    {
        if ($this->cartItems->contains($cartItem)) {
            $this->cartItems->removeElement($cartItem);
        }

        return $this;
    }
}