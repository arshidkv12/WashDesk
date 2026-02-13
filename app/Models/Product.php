<?php

namespace App\Models;

use App\Models\Scopes\OwnerScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'sku',
        'image',
        'barcode',
        'price',
        'tax',
        'quantity',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    protected $appends = [
        'image_url', 
        'created_at_formatted'
    ];

    public function getImageUrlAttribute()
    {
        return $this->getImageUrl();
    }

    public function getImageUrl()
    {
        if ($this->image) {
            /** @phpstan-ignore-next-line */
            // return Storage::disk('public_uploads')->url($this->image);
            return '';
        }
        return asset('img/img-placeholder.jpg');
    }

    public function getCreatedAtFormattedAttribute(): string
    {
        return $this->created_at
            ? $this->created_at->format('d/m/Y h:i A')  
            : '-';
    }

    protected static function booted()
    {
        static::addGlobalScope(new OwnerScope);

        static::creating(function ($jobCard) {
            if (Auth::check() && empty($jobCard->user_id)) {
                $jobCard->user_id = Auth::id();
            }
        });
    }
}
