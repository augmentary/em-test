<?php

namespace App\DataFixtures;

use App\Entity\DeliveryOption;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public const DELIVERY_OPTION_FREE = 'delivery_option_free';

    public function load(ObjectManager $manager): void
    {
        $deliveryOptions = [
            'Next Day' => 1,
            'First Class' => 2,
            'Free' => 4
        ];

        foreach($deliveryOptions as $name => $days) {
            $deliveryOption = new DeliveryOption();
            $deliveryOption
                ->setName($name)
                ->setAverageDeliveryDays($days);
            $manager->persist($deliveryOption);

            if($name === 'Free') {
                $this->setReference(self::DELIVERY_OPTION_FREE, $deliveryOption);
            }
        }

        $manager->flush();
    }
}
