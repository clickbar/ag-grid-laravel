<?php

namespace Clickbar\AgGrid\Tests\TestClasses\Factories;

use Clickbar\AgGrid\Tests\TestClasses\Enums\FlamingoSpecies;
use Illuminate\Database\Eloquent\Factories\Factory;

class FlamingoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->firstName(),
            'species' => $this->faker->randomElement(FlamingoSpecies::cases())->value,
            'weight' => $this->faker->randomFloat(),
            'preferred_food_types' => $this->faker->randomElements(['shrimp', 'algae', 'fish', 'insects', 'pellets', 'vegetables'], 3),
            'custom_properties' => null,
            'is_hungry' => $this->faker->boolean(),
            'last_vaccinated_on' => $this->faker->date(),
        ];
    }
}
