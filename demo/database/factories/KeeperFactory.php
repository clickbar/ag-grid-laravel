<?php

namespace Database\Factories;

use App\Models\Zoo;
use Illuminate\Database\Eloquent\Factories\Factory;

class KeeperFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'zoo_id' => Zoo::factory(),
        ];
    }
}
