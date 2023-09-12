<?php

declare(strict_types=1);

namespace App\Entity\Embeddable;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[Embeddable]
class Address
{
    #[Column(type: "string")]
    #[Assert\NotBlank]
    #[Groups(['get'])]
    private string $line1;

    #[Column(type: "string", nullable: true)]
    #[Groups(['get'])]
    private ?string $line2;

    #[Column(type: "string", nullable: true)]
    #[Groups(['get'])]
    private ?string $city;

    #[Column(type: "string")]
    #[Groups(['get'])]
    #[Assert\NotBlank]
    private string $postCode;

    public function getLine1(): string
    {
        return $this->line1;
    }

    public function setLine1(string $line1): Address
    {
        $this->line1 = $line1;
        return $this;
    }

    public function getLine2(): ?string
    {
        return $this->line2;
    }

    public function setLine2(?string $line2): Address
    {
        $this->line2 = $line2;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): Address
    {
        $this->city = $city;
        return $this;
    }

    public function getPostCode(): string
    {
        return $this->postCode;
    }

    public function setPostCode(string $postCode): Address
    {
        $this->postCode = $postCode;
        return $this;
    }


}
