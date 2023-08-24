<?php

use Clickbar\AgGrid\AgGridQueryBuilder;
use Clickbar\AgGrid\Tests\TestClasses\Models\Flamingo;
use Clickbar\AgGrid\Tests\TestClasses\Models\Keeper;

beforeEach(function () {
    $this->keeper = Keeper::factory()->createOne();
});

it('handles the set filter correctly', function () {
    $flamingosPreferringSeafood = Flamingo::factory()->count(2)->state([
        'preferred_food_types' => ['shrimp', 'algae', 'fish'],
    ])->for($this->keeper)->create();

    $flamingosPreferringShrimp = Flamingo::factory()->count(2)->state([
        'preferred_food_types' => ['shrimp'],
    ])->for($this->keeper)->create();

    $flamingosPreferringPellets = Flamingo::factory()->count(3)->state([
        'preferred_food_types' => ['pellets'],
    ])->for($this->keeper)->create();

    $shrimpQueryBuilder = new AgGridQueryBuilder([
        'filterModel' => [
            'preferred_food_types' => [
                'filterType' => 'set',
                'values' => [
                    'shrimp',
                ],
            ],
        ],
    ], Flamingo::class);

    $pelletsQueryBuilder = new AgGridQueryBuilder([
        'filterModel' => [
            'preferred_food_types' => [
                'filterType' => 'set',
                'values' => [
                    'pellets',
                ],
            ],
        ],
    ], Flamingo::class);

    expect($shrimpQueryBuilder->count())->toBe($flamingosPreferringSeafood->count() + $flamingosPreferringShrimp->count());
    expect($shrimpQueryBuilder->count())->toBe($flamingosPreferringSeafood->count() + $flamingosPreferringShrimp->count());
    expect($pelletsQueryBuilder->count())->toBe($flamingosPreferringPellets->count());
});
