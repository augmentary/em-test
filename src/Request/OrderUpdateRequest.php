<?php

declare(strict_types=1);

namespace App\Request;

use App\Enum\OrderStatus;
use Symfony\Component\Validator\Constraints as Assert;

class OrderUpdateRequest
{
    #[Assert\NotBlank]
    public int $id;

    #[Assert\NotBlank]
    #[Assert\Choice(callback: [OrderStatus::class, 'values'])]
    public string $status;
}
