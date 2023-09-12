<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\DataFixtures\AppFixtures;
use App\Entity\Order;
use App\Enum\OrderStatus;
use App\Handler\OrderCreateHandler;
use App\Request\Fragment\DeliveryAddress;
use App\Request\Fragment\OrderItem;
use App\Request\OrderCreateRequest;
use App\Tests\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class OrderControllerUpdateTest extends ApiTestCase
{
    /** @var Order[] */
    private array $orders = [];

    public function setUp(): void
    {
        parent::setUp();

        $handler = self::getContainer()->get(OrderCreateHandler::class);

        $deliveryOption = $this->databaseTool
            ->loadFixtures([AppFixtures::class])
            ->getReferenceRepository()
            ->getReference(AppFixtures::DELIVERY_OPTION_FREE);

        foreach([1,2] as $i) {
            $req = new OrderCreateRequest();
            $req->name = "Customer $i";
            $req->deliveryAddress = new DeliveryAddress();
            $req->deliveryAddress->line1 = "$i Main St";
            $req->deliveryAddress->postCode = 'NG13 9ZZ';
            $req->orderItems = [new OrderItem()];
            $req->orderItems[0]->identifier = "product-$i";
            $req->orderItems[0]->quantity = 4;
            $req->deliveryOption = $deliveryOption->getId();
            $this->orders[] = $handler($req);
        }
    }

    public function testOrderUpdateSuccess(): void
    {
        $this->client->request('PATCH', '/api/orders', [
            'id' => $this->orders[0]->getId(),
            'status' => 'SHIPPED',
        ], [], ['CONTENT_TYPE' => 'application/json']);
        $response = $this->client->getResponse();
        self::assertResponseIsSuccessful();
        $json = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertEquals('SHIPPED', $json['status']);

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->refresh($this->orders[0]);
        self::assertEquals(OrderStatus::SHIPPED, $this->orders[0]->getStatus());
        $em->refresh($this->orders[1]);
        self::assertEquals(OrderStatus::NEW, $this->orders[1]->getStatus());
    }

    public function testOrderUpdateValidationFailed(): void
    {
        $this->client->request('PATCH', '/api/orders', [
            'id' => 4,
            'status' => 'SHIPPED',
        ], [], ['CONTENT_TYPE' => 'application/json']);
        $response = $this->client->getResponse();
        self::assertResponseIsUnprocessable();
        $response = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals(422, $response['code']);
        self::assertEquals('Validation Failed', $response['message']);
        self::assertEquals('Order not found', $response['errors']['id']);

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->refresh($this->orders[0]);
        self::assertEquals(OrderStatus::NEW, $this->orders[0]->getStatus());
    }
}
