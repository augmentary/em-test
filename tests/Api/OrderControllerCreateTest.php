<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\DataFixtures\AppFixtures;
use App\Entity\DeliveryOption;
use App\Entity\Order;
use App\Enum\OrderStatus;
use App\Handler\OrderCreateHandler;
use App\Repository\OrderRepository;
use App\Request\Fragment\DeliveryAddress;
use App\Request\Fragment\OrderItem;
use App\Request\OrderCreateRequest;
use App\Tests\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class OrderControllerCreateTest extends ApiTestCase
{
    private DeliveryOption $deliveryOption;

    public function setUp(): void
    {
        parent::setUp();

        $this->deliveryOption = $this->databaseTool
            ->loadFixtures([AppFixtures::class])
            ->getReferenceRepository()
            ->getReference(AppFixtures::DELIVERY_OPTION_FREE);
    }

    public function testOrderCreateSuccess(): void
    {
        $req = [
          "name" => "string",
          "deliveryAddress" => [
            "line1" => "l1",
            "city" => "string",
            "postCode" => "string"
          ],
          "orderItems" => [
            ["identifier" => "id2", "quantity" => 4]
          ],
          "deliveryOption" => $this->deliveryOption->getId(),
        ];
        $this->client->request('POST', '/api/orders', $req);
        $response = $this->client->getResponse();
        self::assertResponseIsSuccessful();
        $ord = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals('NEW', $ord['status']);
        self::assertNotNull($ord['id']);
        self::assertEquals($req['name'], $ord['name']);
        self::assertEquals($req['deliveryAddress'], array_filter($ord['deliveryAddress']));
        self::assertEquals($req['orderItems'][0]['identifier'], $ord['items'][0]['identifier']);
        self::assertEquals($req['deliveryOption'], $ord['deliveryOption']['id']);
        self::assertNotNull($ord['estimatedDeliveryDate']);
        self::assertNotNull(\DateTime::createFromFormat(\DateTime::ATOM, $ord['estimatedDeliveryDate']));

        /** @var OrderRepository $rep */
        $rep = self::getContainer()->get(OrderRepository::class);
        $created = $rep->find($ord['id']);
        $etaDays = $this->deliveryOption->getAverageDeliveryDays();
        self::assertEqualsWithDelta(
            $created->getCreatedAt()->modify('+'.$etaDays.' days')->getTimestamp(),
            $created->getEstimatedDeliveryDate()->getTimestamp(),
            5
        );
    }

    public function testOrderValidationFailed(): void
    {
        $req = [
            "name" => "string",
            "deliveryAddress" => [
                "city" => "string",
                "postCode" => "string"
            ],
            "orderItems" => [],
            "deliveryOption" => $this->deliveryOption->getId(),
        ];
        $this->client->request('POST', '/api/orders', $req);
        $response = $this->client->getResponse();
        self::assertResponseIsUnprocessable();
        $response = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals(422, $response['code']);
        self::assertEquals('Validation Failed', $response['message']);
        self::assertEquals("This value should not be blank.", $response['errors']['deliveryAddress.line1']);
        self::assertEquals("This collection should contain 1 element or more.", $response['errors']['orderItems']);
    }
}
