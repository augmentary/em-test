<?php

declare(strict_types=1);

namespace App\Handler;

use App\Entity\Order;
use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use App\Request\OrderUpdateRequest;
use App\Service\ErrorHandling\ErrorCollectionBuilder;
use Doctrine\ORM\EntityManagerInterface;

readonly class OrderUpdateHandler
{
    public function __construct(
        private ErrorCollectionBuilder $errorBuilder,
        private OrderRepository $orderRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(OrderUpdateRequest $payload): Order
    {
        $errors = $this->errorBuilder->build($payload);
        $order = $this->orderRepository->find($payload->id);

        if(null === $order) {
            $errors->add('id', $payload->id, 'Order not found');
        }

        $errors->throwIfInvalid();
        $order->setStatus(OrderStatus::from($payload->status));

        $this->entityManager->flush();

        return $order;
    }
}
