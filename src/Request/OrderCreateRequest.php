<?php

declare(strict_types=1);

namespace App\Request;

use App\Request\Fragment\DeliveryAddress;
use App\Request\Fragment\OrderItem;
use Symfony\Component\Validator\Constraints as Assert;

class OrderCreateRequest
{
    #[Assert\NotBlank]
    public string $name;

    #[Assert\Valid]
    public DeliveryAddress $deliveryAddress;

    /** @var OrderItem[] */
    #[Assert\Valid]
    #[Assert\Count(min: 1)]
    public array $orderItems;

    #[Assert\NotBlank]
    #[Assert\GreaterThan(0)]
    public int $deliveryOption;
}
