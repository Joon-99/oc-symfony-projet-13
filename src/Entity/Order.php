<?php

namespace App\Entity;

use App\Exception\EmptyCartException;
use App\Exception\MissingCartException;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'orders')]
#[Broadcast]
class Order extends BaseEntity
{
    /** @var numeric-string $totalAmount */
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: false)]
    private readonly string $totalAmount;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private User $owner;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'purchaseOrder', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $orderItems;

    #[ORM\Column(nullable: false)]
    private readonly \DateTimeImmutable $orderDate;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $cancelDate = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $shipmentDate = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deliveryDate = null;

    public function __construct(User $owner, ?\DateTimeImmutable $orderDate = null)
    {
        parent::__construct();
        $cart = $owner->getCart();
        if ($cart === null) {
            throw new MissingCartException();
        }
        $cartItems = $cart->getCartItems();
        if ($cartItems->isEmpty()) {
            throw new EmptyCartException();
        }
        $this->setOwner($owner);
        $this->orderItems = new ArrayCollection();
        foreach ($cartItems as $cartItem) {
            $orderItem = new OrderItem($this,  $cartItem);
            $this->addOrderItem($orderItem);
        }
        $this->totalAmount = $cart->getTotalPrice();
        $this->orderDate = $orderDate ?? new \DateTimeImmutable('now');
    }

    /** @return numeric-string */
    public function getTotalAmount(): string
    {
        return $this->totalAmount;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        $this->orderItems->removeElement($orderItem);

        return $this;
    }

    public function getOrderDate(): ?\DateTimeImmutable
    {
        return $this->orderDate;
    }

    public function getCancelDate(): ?\DateTimeImmutable
    {
        return $this->cancelDate;
    }

    public function setCancelDate(?\DateTimeImmutable $cancelDate): static
    {
        $this->cancelDate = $cancelDate;

        return $this;
    }

    public function getShipmentDate(): ?\DateTimeImmutable
    {
        return $this->shipmentDate;
    }

    public function setShipmentDate(?\DateTimeImmutable $shipmentDate): static
    {
        $this->shipmentDate = $shipmentDate;

        return $this;
    }

    public function getDeliveryDate(): ?\DateTimeImmutable
    {
        return $this->deliveryDate;
    }

    public function setDeliveryDate(?\DateTimeImmutable $deliveryDate): static
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
    }

    public function getOrderReference(): string
    {
        return sprintf('ORD-%09d', $this->getId() ?? 0);
    }
}
