<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending     = 'pending';
    case Processing  = 'processing';
    case Completed   = 'completed';
    case Cancelled   = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending    => 'Pending',
            self::Processing => 'Processing',
            self::Completed  => 'Completed',
            self::Cancelled  => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending    => 'text-yellow-600 bg-yellow-50',
            self::Processing => 'text-blue-600 bg-blue-50',
            self::Completed  => 'text-green-600 bg-green-50',
            self::Cancelled  => 'text-red-600 bg-red-50',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Pending    => 'Clock',
            self::Processing => 'RefreshCw',
            self::Completed  => 'CircleCheckBig',
            self::Cancelled  => 'CircleX',
        };
    }

    public static function options(): array
    {
        return array_map(fn ($status) => [
            'value' => $status->value,
            'label' => $status->label(),
            'color' => $status->color(),
            'icon'  => $status->icon(),
        ], self::cases());
    }
}