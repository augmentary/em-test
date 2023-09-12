<?php

declare(strict_types=1);

namespace App\Request\Fragment;

use Symfony\Component\Validator\Constraints as Assert;

class DeliveryAddress
{
    #[Assert\NotBlank]
    public string $line1;
    public ?string $line2 = null;
    public ?string $city = null;
    #[Assert\NotBlank]
    public string $postCode;
}
