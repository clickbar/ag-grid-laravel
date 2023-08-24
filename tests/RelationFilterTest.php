<?php

use Clickbar\AgGrid\AgGridQueryBuilder;
use Clickbar\AgGrid\Tests\TestClasses\Models\Flamingo;
use Clickbar\AgGrid\Tests\TestClasses\Models\Keeper;

beforeEach(function () {
    $this->keeperNamedJohn = Keeper::factory()->state(['name' => 'John'])->createOne();
    $this->keeperNamedOliver = Keeper::factory()->state(['name' => 'Oliver'])->createOne();

    $this->johnsFlamingos = Flamingo::factory()->count(2)->for($this->keeperNamedJohn)->create();
    $this->oliversFlamingos = Flamingo::factory()->count(3)->for($this->keeperNamedOliver)->create();
});

it('handles filters on relations correctly', function () {
    $queryBuilder = new AgGridQueryBuilder(
        [
            'filterModel' => [
                'keeper.name' => [
                    'filterType' => 'text',
                    'type' => 'equals',
                    'filter' => 'John',
                ],
            ],
        ],
        Flamingo::class,
    );

    expect($queryBuilder->get()->count())->toBe($this->johnsFlamingos->count());
});
