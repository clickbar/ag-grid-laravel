<?php

use Clickbar\AgGrid\AgGridQueryBuilder;
use Clickbar\AgGrid\Tests\TestClasses\Models\Flamingo;
use Clickbar\AgGrid\Tests\TestClasses\Models\Keeper;
use Clickbar\AgGrid\Tests\TestClasses\Models\Zoo;

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
    expect($pelletsQueryBuilder->count())->toBe($flamingosPreferringPellets->count());
});

it('handles the nested set filter correctly', function () {

    $zooNamedVivarium = Zoo::factory()->state(['name' => 'Vivarium', 'address' => [
        'street' => 'Schnampelweg',
        'house_number' => '5',
        'postcode' => '64287',
        'city' => 'Darmstadt',
        'contact' => [
            'phone' => '+49 6151 1346900',
            'email' => 'zoo-vivarium@darmstadt.de',
        ],
    ]])->createOne();
    $zooNamedOpelZoo = Zoo::factory()->state(['name' => 'Opel-Zoo', 'address' => [
        'street' => 'Am Opelzoo',
        'house_number' => '3',
        'postcode' => '61476',
        'city' => 'Kronberg im Taunus',
        'contact' => [
            'phone' => '+49 6173 3259030',
            'email' => 'info@opel-zoo.de',
        ],
    ]])->createOne();

    $vivariumQueryBuilder = new AgGridQueryBuilder([
        'filterModel' => [
            'address.contact.phone' => [
                'filterType' => 'set',
                'values' => [
                    '+49 6151 1346900',
                ],
            ],
        ],
    ], Zoo::class);

    $opelZooQueryBuilder = new AgGridQueryBuilder([
        'filterModel' => [
            'address.contact.email' => [
                'filterType' => 'set',
                'values' => [
                    'info@opel-zoo.de',
                ],
            ],
        ],
    ], Zoo::class);

    expect($vivariumQueryBuilder->count())->toBe(1);
    expect($vivariumQueryBuilder->first()->id)->toBe($zooNamedVivarium->id);
    expect($opelZooQueryBuilder->count())->toBe(1);
    expect($opelZooQueryBuilder->first()->id)->toBe($zooNamedOpelZoo->id);
});
