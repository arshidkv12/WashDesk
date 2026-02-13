<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending   = 'pending';
    case Completed = 'completed';
    case Failed    = 'failed';
    case Refunded  = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'Pending',
            self::Completed => 'Completed',
            self::Failed    => 'Failed',
            self::Refunded  => 'Refunded',
        };
    }

    public static function values(): array
    {
        return [
            self::Pending,
            self::Completed,
            self::Failed,
            self::Refunded,
        ];
    }
}
