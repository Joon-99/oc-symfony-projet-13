<?php

namespace App\Entity;

use App\Entity\BaseEntity;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;
use DateTimeImmutable;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'orders')]
#[Broadcast]
class Order extends BaseEntity
{
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: false)]
    private ?string $totalAmount = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private User $owner;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'purchaseOrder', orphanRemoval: true)]
    private Collection $orderItems;

    #[ORM\Column(nullable: false)]
    private DateTimeImmutable $orderDate;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $cancelDate = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $shipmentDate = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $deliveryDate = null;

    public function __construct()
    {
        parent::__construct();
        $this->orderItems = new ArrayCollection();
        $this->orderDate = new DateTimeImmutable("now");
    }

    public function getTotalAmount(): ?string
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(string $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

        return $this;
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
            $orderItem->setPurchaseOrder($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        $this->orderItems->removeElement($orderItem);

        return $this;
    }

    public function getOrderDate(): ?DateTimeImmutable
    {
        return $this->orderDate;
    }

    public function setOrderDate(DateTimeImmutable $orderDate): static
    {
        $this->orderDate = $orderDate;

        return $this;
    }

    public function getCancelDate(): ?DateTimeImmutable
    {
        return $this->cancelDate;
    }

    public function setCancelDate(?DateTimeImmutable $cancelDate): static
    {
        $this->cancelDate = $cancelDate;

        return $this;
    }

    public function getShipmentDate(): ?DateTimeImmutable
    {
        return $this->shipmentDate;
    }

    public function setShipmentDate(?DateTimeImmutable $shipmentDate): static
    {
        $this->shipmentDate = $shipmentDate;

        return $this;
    }

    public function getDeliveryDate(): ?DateTimeImmutable
    {
        return $this->deliveryDate;
    }

    public function setDeliveryDate(?DateTimeImmutable $deliveryDate): static
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
    }
}
