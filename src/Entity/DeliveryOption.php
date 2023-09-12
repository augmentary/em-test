<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\DeliveryOptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DeliveryOptionRepository::class)]
class DeliveryOption
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['get'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $averageDeliveryDays = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getAverageDeliveryDays(): ?int
    {
        return $this->averageDeliveryDays;
    }

    public function setAverageDeliveryDays(int $averageDeliveryDays): static
    {
        $this->averageDeliveryDays = $averageDeliveryDays;

        return $this;
    }
}
