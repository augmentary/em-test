<?php

declare(strict_types=1);

namespace App\Handler;

use App\Entity\Embeddable\Address;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Enum\OrderStatus;
use App\Repository\DeliveryOptionRepository;
use App\Request\OrderCreateRequest;
use App\Service\ErrorHandling\ErrorCollectionBuilder;
use Doctrine\ORM\EntityManagerInterface;

readonly class OrderCreateHandler
{
    public function __construct(
        private ErrorCollectionBuilder $errorBuilder,
        private DeliveryOptionRepository $deliveryOptionRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(OrderCreateRequest $payload): Order
    {
        $errors = $this->errorBuilder->build($payload);
        $order = new Order();
        $order->setStatus(OrderStatus::NEW);
        $order->setName($payload->name);

        $deliveryAddress = new Address();
        $deliveryAddress->setLine1($payload->deliveryAddress->line1);
        $deliveryAddress->setLine2($payload->deliveryAddress->line2);
        $deliveryAddress->setCity($payload->deliveryAddress->city);
        $deliveryAddress->setPostCode($payload->deliveryAddress->postCode);
        $order->setDeliveryAddress($deliveryAddress);

        foreach($payload->orderItems as $pi) {
            $oi = new OrderItem();
            $oi->setIdentifier($pi->identifier);
            $oi->setQuantity($pi->quantity);
            $errors->validate($oi);
            $order->addItem($oi);
        }

        $deliveryOption = $this->deliveryOptionRepository->find($payload->deliveryOption);
        if($deliveryOption === null) {
            $errors->add('deliveryOption', $payload->deliveryOption, 'Invalid delivery option');
        } else {
            $order->setDeliveryOption($deliveryOption);
            $order->setEstimatedDeliveryDate(
                (new \DateTimeImmutable())
                    ->modify('+ ' . $deliveryOption->getAverageDeliveryDays() . ' days')
            );
        }

        $errors->validate($order);
        $errors->throwIfInvalid();

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $order;
    }
}
