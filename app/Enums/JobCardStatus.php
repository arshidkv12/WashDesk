<?php

namespace App\Enums;

enum JobCardStatus: string
{
    case Pending         = 'pending';
    case InProgress      = 'in_progress';    
    case WaitingParts    = 'waiting_parts';   
    case Completed       = 'completed';
    case Delivered       = 'delivered';
    case Cancelled       = 'cancelled';
    case OnHold          = 'on_hold';

    public function label(): string
    {
        return match ($this) {
            self::Pending         => 'Pending',
            self::InProgress      => 'In Progress',      
            self::WaitingParts    => 'Waiting Parts',    
            self::Completed       => 'Completed',
            self::Delivered       => 'Delivered',
            self::Cancelled       => 'Cancelled',
            self::OnHold          => 'On Hold',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending         => 'text-yellow-600 bg-yellow-50',
            self::InProgress      => 'text-orange-600 bg-orange-50',     
            self::WaitingParts    => 'bg-orange-100 text-orange-800',         
            self::Completed       => 'text-green-600 bg-green-50',
            self::Delivered       => 'text-emerald-600 bg-emerald-50',
            self::Cancelled       => 'text-red-600 bg-red-50',
            self::OnHold          => 'text-gray-600 bg-gray-50',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Pending         => 'Clock',
            self::InProgress      => 'Wrench',          // Updated icon
            self::WaitingParts    => 'CircleAlert',     // Added icon
            self::Completed,
            self::Delivered       => 'CircleCheckBig',
            self::Cancelled       => 'CircleX',
            self::OnHold          => 'CircleAlert',
        };
    }

    public static function options(): array
    {
        return array_map(fn ($status) => [
            'value' => $status->value,
            'label' => $status->label(),
            'icon'  => $status->icon(),
            'color' => $status->color(),
        ], self::cases());
    }
}