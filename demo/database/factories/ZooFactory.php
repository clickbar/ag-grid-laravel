<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ZooFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'address' => [
                'street' => $this->faker->streetName(),
                'house_number' => $this->faker->buildingNumber(),
                'postcode' => $this->faker->postcode(),
                'city' => $this->faker->city(),
                'contact' => [
                    'phone' => $this->faker->phoneNumber(),
                    'email' => $this->faker->email(),
                ],
            ],
        ];
    }
}
