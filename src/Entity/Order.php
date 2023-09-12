<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Embeddable\Address;
use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get'])]
    private ?int $id = null;

    #[ORM\Column(length: 20, enumType: OrderStatus::class)]
    #[Groups(['get'])]
    private ?OrderStatus $status = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['get'])]
    private ?string $name = null;

    // This might make more sense as an entity in its own right
    #[ORM\Embedded(class: Address::class)]
    #[Assert\NotBlank]
    #[Assert\Valid]
    #[Groups(['get'])]
    private ?Address $deliveryAddress = null;

    /** @var Collection<int, OrderItem> */
    #[ORM\OneToMany(mappedBy: 'parentOrder', targetEntity: OrderItem::class, cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['get'])]
    #[Assert\Count(min: 1)]
    private Collection $items;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get'])]
    private ?DeliveryOption $deliveryOption = null;

    #[ORM\Column(nullable: false)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    #[Groups(['get'])]
    private ?\DateTimeImmutable $estimatedDeliveryDate = null;



    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?OrderStatus
    {
        return $this->status;
    }

    public function setStatus(?OrderStatus $status): Order
    {
        $this->status = $status;
        return $this;
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

    public function getDeliveryAddress(): Address
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(Address $deliveryAddress): Order
    {
        $this->deliveryAddress = $deliveryAddress;
        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(OrderItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setParentOrder($this);
        }

        return $this;
    }

    public function removeItem(OrderItem $item): static
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getParentOrder() === $this) {
                $item->setParentOrder(null);
            }
        }

        return $this;
    }

    public function getDeliveryOption(): ?DeliveryOption
    {
        return $this->deliveryOption;
    }

    public function setDeliveryOption(?DeliveryOption $deliveryOption): static
    {
        $this->deliveryOption = $deliveryOption;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getEstimatedDeliveryDate(): ?\DateTimeImmutable
    {
        return $this->estimatedDeliveryDate;
    }

    public function setEstimatedDeliveryDate(\DateTimeImmutable $estimatedDeliveryDate): static
    {
        $this->estimatedDeliveryDate = $estimatedDeliveryDate;

        return $this;
    }
}
