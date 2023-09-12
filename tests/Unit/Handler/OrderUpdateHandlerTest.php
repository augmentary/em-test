<?php

namespace App\Tests\Unit\Handler;

use App\Entity\DeliveryOption;
use App\Entity\Order;
use App\Entity\OrderItem as OrderItemEntity;
use App\Enum\OrderStatus;
use App\Handler\OrderUpdateHandler;
use App\Repository\DeliveryOptionRepository;
use App\Repository\OrderRepository;
use App\Request\Fragment\DeliveryAddress;
use App\Request\Fragment\OrderItem;
use App\Request\OrderUpdateRequest;
use App\Service\ErrorHandling\ErrorCollection;
use App\Service\ErrorHandling\ErrorCollectionBuilder;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrderUpdateHandlerTest extends TestCase
{
    private MockObject & ErrorCollection $errorCollection;
    private MockObject & OrderRepository $orderRepository;
    private MockObject & EntityManagerInterface $entityManager;
    private OrderUpdateHandler $handler;

    public function setup(): void
    {
        $this->errorCollection = $this->createMock(ErrorCollection::class);
        $ecb = $this->createMock(ErrorCollectionBuilder::class);
        $ecb->expects(self::once())
            ->method('build')
            ->willReturn($this->errorCollection);

        $this->orderRepository = $this->createMock(OrderRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->handler = new OrderUpdateHandler(
            $ecb,
            $this->orderRepository,
            $this->entityManager
        );
    }

    public static function getSampleOrderUpdateRequest(): OrderUpdateRequest
    {
        $req = new OrderUpdateRequest();
        $req->id = 4;
        $req->status = 'SHIPPED';
        return $req;
    }

    public function testOrderUpdateSuccess(): void
    {
        $req = self::getSampleOrderUpdateRequest();
        $orderMock = $this->createMock(Order::class);
        $orderMock
            ->expects(self::once())
            ->method('setStatus')
            ->with(OrderStatus::SHIPPED);

        $this->orderRepository
            ->expects(self::once())
            ->method('find')
            ->with(4)
            ->willReturn($orderMock);

        $this->errorCollection
            ->expects(self::once())
            ->method('throwIfInvalid');

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        ($this->handler)($req);
    }

    public function testOrderUpdateNotFound(): void
    {
        $req = self::getSampleOrderUpdateRequest();

        $this->orderRepository
            ->expects(self::once())
            ->method('find')
            ->with(4)
            ->willReturn(null);

        $this->errorCollection
            ->expects(self::once())
            ->method('add')
            ->with('id', 4, 'Order not found');

        $exception = new \Exception();
        $this->errorCollection
            ->expects(self::once())
            ->method('throwIfInvalid')
            ->willThrowException($exception);

        $this->entityManager
            ->expects(self::never())
            ->method('flush');

        $this->expectExceptionObject($exception);

        ($this->handler)($req);
    }
}
