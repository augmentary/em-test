<?php

declare(strict_types=1);

namespace App\Enum;

enum OrderStatus: string
{
    case NEW = 'NEW';
    case DELAYED = 'DELAYED';
    case SHIPPED = 'SHIPPED';

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
