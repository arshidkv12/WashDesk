<?php

namespace App\Models;

use App\Models\Scopes\OwnerScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Customer extends Model
{
    use HasFactory; 

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
    ];

    protected $appends = [
        'created_at_formatted',
    ];

    public function getCreatedAtFormattedAttribute(): string
    {
        return $this->created_at
            ? $this->created_at->format('d/m/Y h:i A')  
            : '-';
    }

    public function jobCards()
    {
        return $this->hasMany(JobCard::class);
    }

    protected static function booted()
    {
        static::creating(function ($jobCard) {
            if (Auth::check() && empty($jobCard->user_id)) {
                $jobCard->user_id = Auth::id();
            }
        });

        static::addGlobalScope(new OwnerScope);
    }
}

