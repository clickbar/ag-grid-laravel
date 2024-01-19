<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FlamingoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->firstName(),
            'weight' => $this->faker->randomFloat(),
            'preferred_food_types' => $this->faker->randomElements(['shrimp', 'algae', 'fish', 'insects', 'pellets', 'vegetables']),
            'custom_properties' => null,
            'is_hungry' => $this->faker->boolean(),
            'last_vaccinated_on' => $this->faker->date(),
        ];
    }
}
