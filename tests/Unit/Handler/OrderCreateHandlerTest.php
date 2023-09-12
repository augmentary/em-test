<?php

namespace App\Tests\Unit\Handler;

use App\Entity\DeliveryOption;
use App\Entity\Order;
use App\Entity\OrderItem as OrderItemEntity;
use App\Enum\OrderStatus;
use App\Handler\OrderCreateHandler;
use App\Repository\DeliveryOptionRepository;
use App\Request\Fragment\DeliveryAddress;
use App\Request\Fragment\OrderItem;
use App\Request\OrderCreateRequest;
use App\Service\ErrorHandling\ErrorCollection;
use App\Service\ErrorHandling\ErrorCollectionBuilder;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrderCreateHandlerTest extends TestCase
{
    private MockObject & ErrorCollection $errorCollection;
    private MockObject & DeliveryOptionRepository $deliveryOptionRepository;
    private MockObject & EntityManagerInterface $entityManager;
    private OrderCreateHandler $handler;

    public function setup(): void
    {
        $this->errorCollection = $this->createMock(ErrorCollection::class);
        $ecb = $this->createMock(ErrorCollectionBuilder::class);
        $ecb->expects(self::once())
            ->method('build')
            ->willReturn($this->errorCollection);

        $this->deliveryOptionRepository = $this->createMock(DeliveryOptionRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->handler = new OrderCreateHandler(
            $ecb,
            $this->deliveryOptionRepository,
            $this->entityManager
        );
    }

    public static function getSampleOrderCreateRequest(): OrderCreateRequest
    {
        $req = new OrderCreateRequest();
        $req->name = "Customer name";
        $req->deliveryAddress = new DeliveryAddress();
        $req->deliveryAddress->line1 = "4 Main St";
        $req->deliveryAddress->postCode = 'NG13 9ZZ';
        $req->orderItems = [new OrderItem()];
        $req->orderItems[0]->identifier = "product-5";
        $req->orderItems[0]->quantity = 4;
        $req->deliveryOption = 99;
        return $req;
    }

    public static function validateOrder(Order $o, ?DeliveryOption $deliveryOption): bool
    {
        self::assertEquals("Customer name", $o->getName());
        self::assertEquals("4 Main St", $o->getDeliveryAddress()->getLine1());
        self::assertEquals('NG13 9ZZ', $o->getDeliveryAddress()->getPostCode());
        $items = $o->getItems();
        self::assertCount(1, $items);
        self::validateOrderItem($items[0]);

        if($deliveryOption) {
            self::assertSame($deliveryOption, $o->getDeliveryOption());
            self::assertEquals(OrderStatus::NEW, $o->getStatus());
            $eta = (new \DateTimeImmutable())->modify('+ '.$deliveryOption->getAverageDeliveryDays().' days');
            self::assertEqualsWithDelta(
                $eta->getTimestamp(),
                $o->getEstimatedDeliveryDate()->getTimestamp(),
                5
            );
        }

        return true;
    }

    public static function validateOrderItem(OrderItemEntity $i): void
    {
        self::assertEquals('product-5', $i->getIdentifier());
        self::assertEquals(4, $i->getQuantity());
    }

    public function testOrderCreateSuccess(): void
    {
        $req = self::getSampleOrderCreateRequest();

        $deliveryOption = new DeliveryOption();
        $deliveryOption->setAverageDeliveryDays(12);
        $this->deliveryOptionRepository
            ->expects(self::once())
            ->method('find')
            ->with(99)
            ->willReturn($deliveryOption);

        $this->errorCollection
            ->expects(self::exactly(2))
            ->method('validate')
            ->willReturnCallback(function (object $entity) use ($deliveryOption) {
                match (get_class($entity)) {
                    Order::class => self::validateOrder($entity, $deliveryOption),
                    OrderItemEntity::class => self::validateOrderItem($entity),
                    default => self::fail('Unexpected class')
                };
                return true;
            });

        $this->errorCollection
            ->expects(self::once())
            ->method('throwIfInvalid');

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with(self::callback(static fn ($entity) => self::validateOrder($entity, $deliveryOption)));

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        ($this->handler)($req);
    }

    public function testOrderCreateDeliveryOptionNotFound(): void
    {
        $req = self::getSampleOrderCreateRequest();

        $this->deliveryOptionRepository
            ->expects(self::once())
            ->method('find')
            ->with(99)
            ->willReturn(null);

        $this->errorCollection
            ->expects(self::exactly(2))
            ->method('validate')
            ->willReturnCallback(function (object $entity) {
                match (get_class($entity)) {
                    Order::class => self::validateOrder($entity, null),
                    OrderItemEntity::class => self::validateOrderItem($entity),
                    default => self::fail('Unexpected class')
                };
                return true;
            });

        $this->errorCollection
            ->expects(self::once())
            ->method('add')
            ->with('deliveryOption', 99, 'Invalid delivery option');

        $exception = new \Exception();
        $this->errorCollection
            ->expects(self::once())
            ->method('throwIfInvalid')
            ->willThrowException($exception);

        $this->entityManager
            ->expects(self::never())
            ->method('persist');

        $this->entityManager
            ->expects(self::never())
            ->method('flush');

        $this->expectExceptionObject($exception);
        ($this->handler)($req);
    }
}
