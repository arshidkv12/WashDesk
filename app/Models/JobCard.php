<?php

namespace App\Models;

use App\Enums\JobCardStatus;
use App\Models\Scopes\OwnerScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JobCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_no',
        'customer_id',
        'item',
        'problem',
        'status',
        'estimated_cost',
        'delivery_date',
        'notes',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'estimated_cost' => 'decimal:2',
        'status' => JobCardStatus::class
    ];

    protected $appends = [
        'delivery_date_formatted',
        'created_at_formatted',
    ];

    public function getDeliveryDateFormattedAttribute(): string
    {
        return $this->delivery_date
            ? $this->delivery_date->format('M d, Y') // Jan 03, 2026
            : '-';
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

    public function files()
    {
        return $this->hasMany(JobCardFile::class, 'job_id');
    }


    protected static function booted()
    {
        static::creating(function ($jobCard) {
            if (Auth::check() && empty($jobCard->user_id)) {
                $jobCard->user_id = Auth::id();
            }

            DB::transaction(function () use ($jobCard) {
                $last = JobCard::where('user_id', $jobCard->user_id)
                    ->lockForUpdate()
                    ->max('job_no');

                $jobCard->job_no = ($last ?? 0) + 1;
            });
        });
        
        static::addGlobalScope(new OwnerScope);
    }
}
