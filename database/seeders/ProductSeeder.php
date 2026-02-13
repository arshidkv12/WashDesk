<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Skipping ProductSeeder.');
            return;
        }

        foreach ($users as $user) {
            for ($i = 1; $i <= 100; $i++) {
                Product::withoutGlobalScopes()->create([
                    'user_id'     => $user->id,
                    'name'        => 'Sample Product ' . $user->id,
                    'description' => 'Demo product for user ' . $user->id,
                    'image'       => null,
                    'barcode'     => 'BC-' . uniqid(),
                    'price'       => rand(100, 500),
                    'tax'         => rand(0, 18),
                    'quantity'    => rand(1, 50),
                    'status'      => true,
                ]);
            }
        }
    }
}
