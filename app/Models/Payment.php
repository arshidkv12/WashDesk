<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\PaymentStatus;
use App\Models\Scopes\OwnerScope;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id',
        'user_id',
        'amount',
        'method',
        'status',
        'reference',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'status'  => PaymentStatus::class,  
    ];


    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }


    public function isCompleted(): bool
    {
        return $this->status === PaymentStatus::Completed;
    }

    protected static function booted()
    {
        
        static::addGlobalScope(new OwnerScope);
    }
}
