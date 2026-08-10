<?php

namespace App\Entity;

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
    private readonly int $quantity;

    /** @var numeric-string */
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: false)]
    private readonly string $unitAmount;

    #[ORM\Column(length: 255, nullable: false)]
    private readonly string $productName;

    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    private readonly int $productId;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: false)]
    private readonly Order $purchaseOrder;

    public function __construct(Order $purchaseOrder, CartItem $cartItem)
    {
        parent::__construct();
        $product = $cartItem->getProduct();
        $productId = $product->getId();
        if ($productId === null) {
            throw new \DomainException("Cannot create an order item: product {$product->getName()} is not persisted.");
        }
        $this->purchaseOrder = $purchaseOrder;
        $this->quantity = $cartItem->getQuantity();
        $this->unitAmount = $cartItem->getPrice();
        $this->productName = $product->getName();
        $this->productId = $productId;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function getUnitAmount(): string
    {
        return $this->unitAmount;
    }

    public function getPurchaseOrder(): Order
    {
        return $this->purchaseOrder;
    }
    
    public function getProductName(): string
    {
        return $this->productName;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

}
