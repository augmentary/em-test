<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\DataFixtures\AppFixtures;
use App\Enum\OrderStatus;
use App\Handler\OrderCreateHandler;
use App\Request\Fragment\DeliveryAddress;
use App\Request\Fragment\OrderItem;
use App\Request\OrderCreateRequest;
use App\Tests\CommandTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class MarkDelayedOrdersCommandTest extends CommandTestCase
{
    public function testExecute(): void
    {
        $overdue = 'overdue';
        $future = 'future';

        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $container = self::getContainer();
        $createHandler = $container->get(OrderCreateHandler::class);

        $deliveryOption = $this->databaseTool
            ->loadFixtures([AppFixtures::class])
            ->getReferenceRepository()
            ->getReference(AppFixtures::DELIVERY_OPTION_FREE);

        $orders = [];
        foreach([$overdue, $future] as $eta) {
            $req = new OrderCreateRequest();
            $req->name = "Customer $eta";
            $req->deliveryAddress = new DeliveryAddress();
            $req->deliveryAddress->line1 = "$eta Main St";
            $req->deliveryAddress->postCode = 'NG13 9ZZ';
            $req->orderItems = [new OrderItem()];
            $req->orderItems[0]->identifier = "product-$eta";
            $req->orderItems[0]->quantity = 4;
            $req->deliveryOption = $deliveryOption->getId();
            $created = $createHandler($req);
            self::assertEquals(OrderStatus::NEW, $created->getStatus());
            $orders[$eta] = $created;
        }

        $now = new \DateTimeImmutable();
        $orders[$overdue]->setEstimatedDeliveryDate($now->modify('-10 minutes'));
        $orders[$future]->setEstimatedDeliveryDate($now->modify('+10 minutes'));

        $em = $container->get(EntityManagerInterface::class);
        $em->flush();

        $command = $application->find('app:mark-delayed-orders');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
        self::assertStringContainsString('Marking 1 orders as delayed', $commandTester->getDisplay());

        $em->refresh($orders[$overdue]);
        $em->refresh($orders[$future]);

        self::assertEquals(OrderStatus::DELAYED, $orders[$overdue]->getStatus());
        self::assertEquals(OrderStatus::NEW, $orders[$future]->getStatus());
    }
}
