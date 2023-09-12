<?php

declare(strict_types=1);

namespace App\Request\Fragment;

use Symfony\Component\Validator\Constraints as Assert;

class OrderItem
{
    #[Assert\NotBlank]
    public string $identifier;

    #[Assert\NotBlank]
    #[Assert\GreaterThan(0)]
    public int $quantity;
}
