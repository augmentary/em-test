<?php

declare(strict_types=1);

namespace App\Request;

use App\Enum\OrderStatus;
use Doctrine\Common\Collections\Criteria;

class OrderFilterRequest
{
    /** @var ?array<int> $id */
    public ?array $id = null;

    /** @var ?array<OrderStatus> $status */
    public ?array $status = null;

    public function toCriteria(): Criteria
    {
        $criteria = Criteria::create();
        if ($this->id !== null) {
            $criteria->andWhere(Criteria::expr()->in('id', $this->id));
        }
        if ($this->status !== null) {
            $criteria->andWhere(Criteria::expr()->in('status', $this->status));
        }
        return $criteria;
    }
}
