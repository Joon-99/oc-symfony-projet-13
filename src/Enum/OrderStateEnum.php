<?php

namespace App\Enum;

enum OrderStateEnum: string
{
    case Ordered = 'ordered';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
}