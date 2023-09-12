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

class OrderControllerIndexTest extends ApiTestCase
{
    /** @var OrderCreateRequest[] */
    private array $orderReqs = [];

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

        foreach([0,1] as $i) {
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
            $this->orderReqs[] = $req;
        }
    }

    public function testOrderIndex(): void
    {
        $this->client->request('GET', '/api/orders');
        $response = $this->client->getResponse();
        self::assertResponseIsSuccessful();
        $json = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(2, $json);

        usort($json, fn ($a, $b) => $a['name'] <=> $b['name']);

        foreach($json as $i => $ord) {
            $req = $this->orderReqs[$i];
            self::assertEquals('NEW', $ord['status']);
            self::assertEquals($req->name, $ord['name']);
            self::assertEquals((array)$req->deliveryAddress, $ord['deliveryAddress']);
            self::assertEquals($req->orderItems[0]->identifier, $ord['items'][0]['identifier']);
            self::assertEquals($req->deliveryOption, $ord['deliveryOption']['id']);
            self::assertNotNull($ord['estimatedDeliveryDate']);
        }
    }

    public function testOrderIndexFilterId(): void
    {
        $this->client->request('GET', '/api/orders?id[]=' . $this->orders[0]->getId());
        $response = $this->client->getResponse();
        self::assertResponseIsSuccessful();
        $json = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(1, $json);

        $ord = $json[0];
        $req = $this->orderReqs[0];
        self::assertEquals('NEW', $ord['status']);
        self::assertEquals($req->name, $ord['name']);
        self::assertEquals((array)$req->deliveryAddress, $ord['deliveryAddress']);
        self::assertEquals($req->orderItems[0]->identifier, $ord['items'][0]['identifier']);
        self::assertEquals($req->deliveryOption, $ord['deliveryOption']['id']);
        self::assertNotNull($ord['estimatedDeliveryDate']);
    }

    public function testOrderIndexStatus(): void
    {
        $this->orders[1]->setStatus(OrderStatus::SHIPPED);
        self::getContainer()->get(EntityManagerInterface::class)->flush();

        $this->client->request('GET', '/api/orders?status[]=SHIPPED');
        $response = $this->client->getResponse();
        self::assertResponseIsSuccessful();
        $json = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(1, $json);

        $ord = $json[0];
        $req = $this->orderReqs[1];
        self::assertEquals('SHIPPED', $ord['status']);
        self::assertEquals($req->name, $ord['name']);
        self::assertEquals((array)$req->deliveryAddress, $ord['deliveryAddress']);
        self::assertEquals($req->orderItems[0]->identifier, $ord['items'][0]['identifier']);
        self::assertEquals($req->deliveryOption, $ord['deliveryOption']['id']);
        self::assertNotNull($ord['estimatedDeliveryDate']);
    }
}
