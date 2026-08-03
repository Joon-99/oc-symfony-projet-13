<?php

namespace App\Entity;

use App\Entity\BaseEntity;
use App\Repository\OrderItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
#[ORM\Table(name: 'order_items')]
#[Broadcast]
class OrderItem extends BaseEntity
{
    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    private int $quantity = 0;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: false)]
    private string $unitAmount = "0.00";

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: false)]
    private Order $purchaseOrder;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Product $product = null;

    public function __construct(Order $purchaseOrder, int $quantity, string $unitAmount)
    {
        parent::__construct();
        $this->setPurchaseOrder($purchaseOrder);
        $this->setQuantity($quantity);
        $this->setUnitAmount($unitAmount);
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getUnitAmount(): string
    {
        return $this->unitAmount;
    }

    public function setUnitAmount(string $unitAmount): static
    {
        $this->unitAmount = $unitAmount;

        return $this;
    }

    public function getPurchaseOrder(): Order
    {
        return $this->purchaseOrder;
    }

    public function setPurchaseOrder(Order $purchaseOrder): static
    {
        $this->purchaseOrder = $purchaseOrder;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }
}
