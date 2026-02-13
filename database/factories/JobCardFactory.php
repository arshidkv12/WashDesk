<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\JobCard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobCard>
 */
class JobCardFactory extends Factory
{
    protected $model = JobCard::class;

    public function definition(): array
    {
        return [
            'job_no' => 'JC-' . $this->faker->unique()->numberBetween(1000, 9999),
            'user_id' => 1,

            // If customer_id not passed, create one automatically
            'customer_id' => Customer::factory(),

            'item' => $this->faker->randomElement([
                'Mobile Phone',
                'Laptop',
                'Bike',
                'Washing Machine',
                'TV',
            ]),

            'problem' => $this->faker->sentence(6),

            'status' => $this->faker->randomElement([
                'pending',
                'in_progress',
                'completed',
                'delivered',
            ]),

            'estimated_cost' => $this->faker->randomFloat(2, 500, 15000),

            'delivery_date' => $this->faker->optional()->dateTimeBetween('now', '+10 days'),

            'notes' => $this->faker->optional()->paragraph(),
        ];
    }
}


