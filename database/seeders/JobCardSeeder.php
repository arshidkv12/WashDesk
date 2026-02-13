<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use App\Models\JobCard;
use Illuminate\Support\Str;

class JobCardSeeder extends Seeder
{
    public function run()
    {
        Customer::all()->each(function ($customer) {
            JobCard::factory()
                ->count(rand(1, 3))
                ->create([
                    'customer_id' => $customer->id,
                ]);
        });
    }
}

