<?php

namespace App\Listeners;

use App\Models\Service;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateDefaultServicesAfterVerification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Verified $event): void
    {
        $user = $event->user;

        // Prevent duplicate insertion
        if (Service::where('user_id', $user->id)->exists()) {
            return;
        }

       $now = now();

        $services = [
            ['user_id' => $user->id, 'name' => 'Shirt Wash', 'price' => 45, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => $user->id, 'name' => 'T-Shirt Wash', 'price' => 40, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => $user->id, 'name' => 'Pants Wash', 'price' => 55, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => $user->id, 'name' => 'Saree Wash', 'price' => 110, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => $user->id, 'name' => 'Shirt Iron', 'price' => 18, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => $user->id, 'name' => 'Shirt Dry Clean', 'price' => 140, 'created_at' => $now, 'updated_at' => $now],
        ];

        Service::insert($services);
    }
}
