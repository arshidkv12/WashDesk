<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Models\Scopes\OwnerScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Invoice extends Model
{
    protected $fillable = [
        'user_id',
        'invoice_no',
        'customer_id',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'status',
        'job_card_id',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];


    protected $appends = [
        'created_at_formatted',
    ];

    public function getInvoiceNoAttribute($value)
    {
        return str_pad($value, 5, '0', STR_PAD_LEFT);
    }
    
    public function getCreatedAtFormattedAttribute(): string
    {
        return $this->created_at
            ? $this->created_at->format('d/m/Y h:i A')  
            : '-';
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function jobCard()
    {
        return $this->belongsTo(JobCard::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function completedPayments()
    {
        return $this->payments()
            ->where('status', PaymentStatus::Completed);
    }

    public function paidAmount(): float
    {
        return $this->completedPayments()->sum('amount');
    }

    public function balanceAmount(): float
    {
        return $this->total_amount - $this->paidAmount();
    }

    protected static function booted()
    {
        static::creating(function ($invoice) {
            if (Auth::check() && empty($invoice->user_id)) {
                $invoice->user_id = Auth::id();
            }

            DB::transaction(function () use ($invoice) {
                $last = Invoice::where('user_id', $invoice->user_id)
                    ->lockForUpdate()
                    ->max('invoice_no');

                $invoice->invoice_no = ($last ?? 0) + 1;
            });
        });
        
        static::addGlobalScope(new OwnerScope);
    }
}
