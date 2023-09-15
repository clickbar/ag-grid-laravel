<?php

use Clickbar\AgGrid\AgGridQueryBuilder;
use Clickbar\AgGrid\Tests\TestClasses\Models\Flamingo;
use Clickbar\AgGrid\Tests\TestClasses\Models\Keeper;
use Clickbar\AgGrid\Tests\TestClasses\Models\Zoo;

beforeEach(function () {
    $this->zooNamedVivarium = Zoo::factory()->state(['name' => 'Vivarium', 'address' => [
        'street' => 'Schnampelweg',
        'house_number' => '5',
        'postcode' => '64287',
        'city' => 'Darmstadt',
    ]])->createOne();
    $this->zooNamedOpelZoo = Zoo::factory()->state(['name' => 'Opel-Zoo', 'address' => [
        'street' => 'Am Opelzoo',
        'house_number' => '3',
        'postcode' => '61476',
        'city' => 'Kronberg im Taunus',
    ]])->createOne();

    $this->keeperNamedJohn = Keeper::factory()->for($this->zooNamedVivarium)->state(['name' => 'John'])->createOne();
    $this->keeperNamedOliver = Keeper::factory()->for($this->zooNamedOpelZoo)->state(['name' => 'Oliver'])->createOne();

    $this->johnsFlamingos = Flamingo::factory()->count(2)->for($this->keeperNamedJohn)->create();
    $this->oliversFlamingos = Flamingo::factory()->count(3)->for($this->keeperNamedOliver)->create();
});

it('handles filters on nested relations with json field correctly', function () {
    $queryBuilder = new AgGridQueryBuilder(
        [
            'filterModel' => [
                'keeper.zoo.address.street' => [
                    'filterType' => 'text',
                    'type' => 'equals',
                    'filter' => 'Schnampelweg',
                ],
            ],
        ],
        Flamingo::class,
    );

    expect($queryBuilder->get()->count())->toBe($this->johnsFlamingos->count());
});
