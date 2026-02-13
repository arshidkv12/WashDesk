<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    const TYPE_PRODUCT = 'product';
    const TYPE_CUSTOM_PRODUCT = 'custom_product';
    const TYPE_SERVICE = 'service';

    protected $fillable = [
        'invoice_id',
        'item_type',
        'product_id',
        'service_id',
        'name',
        'quantity',
        'unit_price',
        'unit',
        'tax_rate',
        'discount_percentage',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'integer',
        'discount_percentage' => 'decimal:2',
    ];


    public function getItemDescriptionAttribute()
    {
        switch ($this->item_type) {
            case self::TYPE_PRODUCT:
                return $this->description ?: $this->product?->description;
            case self::TYPE_CUSTOM_PRODUCT:
                return $this->custom_description ?: $this->description;
            case self::TYPE_SERVICE:
                return $this->description ?: $this->service?->description;
            default:
                return $this->description;
        }
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeProductItems($query)
    {
        return $query->where('item_type', self::TYPE_PRODUCT);
    }

    public function scopeCustomProductItems($query)
    {
        return $query->where('item_type', self::TYPE_CUSTOM_PRODUCT);
    }

    public function scopeServiceItems($query)
    {
        return $query->where('item_type', self::TYPE_SERVICE);
    }

    public static function createCustomProduct($name, $price, $quantity = 1, $taxRate = 0)
    {
        return new self([
            'item_type' => self::TYPE_CUSTOM_PRODUCT,
            'custom_name' => $name,
            'quantity' => $quantity,
            'unit_price' => $price,
            'tax_rate' => $taxRate,
        ]);
    }

    public static function createService($name, $price, $quantity = 1, $taxRate = 0)
    {
        return new self([
            'item_type' => self::TYPE_SERVICE,
            'custom_name' => $name,
            'quantity' => $quantity,
            'unit_price' => $price,
            'tax_rate' => $taxRate,
        ]);
    }

}
