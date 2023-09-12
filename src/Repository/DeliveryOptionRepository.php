<?php

namespace App\Repository;

use App\Entity\DeliveryOption;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DeliveryOption>
 *
 * @method DeliveryOption|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeliveryOption|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeliveryOption[]    findAll()
 * @method DeliveryOption[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeliveryOptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeliveryOption::class);
    }
}
