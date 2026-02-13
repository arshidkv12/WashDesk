<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft          = 'draft';
    case Sent           = 'sent';
    case Paid           = 'paid';
    case Overdue        = 'overdue';
    case Cancelled      = 'cancelled';
    case PartiallyPaid  = 'partially_paid';

    public function label(): string
    {
        return match ($this) {
            self::Draft          => 'Draft',
            self::Sent           => 'Sent',
            self::Paid           => 'Paid',
            self::Overdue        => 'Overdue',
            self::Cancelled      => 'Cancelled',
            self::PartiallyPaid  => 'Partially Paid',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft          => 'text-gray-600 bg-gray-50',
            self::Sent           => 'text-blue-600 bg-blue-50',
            self::Paid           => 'text-green-600 bg-green-50',
            self::Overdue        => 'text-red-600 bg-red-50',
            self::Cancelled      => 'text-gray-400 bg-gray-100',
            self::PartiallyPaid  => 'text-yellow-600 bg-yellow-50',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Draft          => 'FileMinus',
            self::Sent           => 'PaperPlane',
            self::Paid,
            self::PartiallyPaid  => 'CircleCheckBig',
            self::Overdue        => 'ExclamationCircle',
            self::Cancelled      => 'CircleX',
        };
    }

    public static function options(): array
    {
        return array_map(fn ($status) => [
            'value' => $status->value,
            'label' => $status->label(),
            // 'icon'  => $status->icon(),
            // 'color' => $status->color(),
        ], self::cases());
    }
}
