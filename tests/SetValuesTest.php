<?php

use Clickbar\AgGrid\AgGridQueryBuilder;
use Clickbar\AgGrid\Exceptions\InvalidSetValueOperation;
use Clickbar\AgGrid\Exceptions\UnauthorizedSetFilterColumn;
use Clickbar\AgGrid\Tests\TestClasses\Models\Flamingo;
use Clickbar\AgGrid\Tests\TestClasses\Models\Keeper;
use Clickbar\AgGrid\Tests\TestClasses\Models\Zoo;

beforeEach(function () {
    $this->zooNamedVivarium = Zoo::factory()->state(['name' => 'Vivarium', 'address' => [
        'street' => 'Schnampelweg',
        'house_number' => '5',
        'postcode' => '64287',
        'city' => 'Darmstadt',
        'contact' => [
            'phone' => '+49 6151 1346900',
            'email' => 'zoo-vivarium@darmstadt.de',
        ],
    ]])->createOne();
    $this->zooNamedOpelZoo = Zoo::factory()->state(['name' => 'Opel-Zoo', 'address' => [
        'street' => 'Am Opelzoo',
        'house_number' => '3',
        'postcode' => '61476',
        'city' => 'Kronberg im Taunus',
        'contact' => [
            'phone' => '+49 6173 3259030',
            'email' => 'info@opel-zoo.de',
        ],
    ]])->createOne();

    $this->keeperNamedJohn = Keeper::factory()->for($this->zooNamedVivarium)->state(['name' => 'John'])->createOne();
    $this->keeperNamedOliver = Keeper::factory()->for($this->zooNamedOpelZoo)->state(['name' => 'Oliver'])->createOne();

    $this->johnsFlamingos = Flamingo::factory()->count(2)->for($this->keeperNamedJohn)->create();
    $this->oliversFlamingos = Flamingo::factory()->count(3)->for($this->keeperNamedOliver)->create();
});

it('can retrieve set filter values for a regular column', function () {
    $queryBuilder = new AgGridQueryBuilder(
        [
            'column' => 'name',
        ],
        Flamingo::class,
    );

    $names =
        $this->johnsFlamingos->pluck('name')
            ->concat($this->oliversFlamingos->pluck('name'))
            ->unique()
            ->sort()
            ->values();

    expect($queryBuilder->toSetValues(['*'])->toArray())->toMatchArray($names->toArray());
});

it('can retrieve set filter values for a related column', function () {
    $queryBuilder = new AgGridQueryBuilder(
        [
            'column' => 'keeper.name',
        ],
        Flamingo::class,
    );

    $names =
        $this->johnsFlamingos->map(fn (Flamingo $flamingo) => $flamingo->keeper->name)
            ->concat($this->oliversFlamingos->map(fn (Flamingo $flamingo) => $flamingo->keeper->name))
            ->unique()
            ->sort()
            ->values();

    expect($queryBuilder->toSetValues(['*'])->toArray())->toMatchArray($names->toArray());
});

it('can retrieve set filter values for a nested related column', function () {
    $queryBuilder = new AgGridQueryBuilder(
        [
            'column' => 'keeper.zoo.name',
        ],
        Flamingo::class,
    );

    $names =
        $this->johnsFlamingos->map(fn (Flamingo $flamingo) => $flamingo->keeper->zoo->name)
            ->concat($this->oliversFlamingos->map(fn (Flamingo $flamingo) => $flamingo->keeper->zoo->name))
            ->unique()
            ->sort()
            ->values();

    expect($queryBuilder->toSetValues(['*'])->toArray())->toMatchArray($names->toArray());
});

it('can retrieve set filter values for a nested related json column field', function () {
    $queryBuilder = new AgGridQueryBuilder(
        [
            'column' => 'keeper.zoo.address.street',
        ],
        Flamingo::class,
    );

    $names =
        $this->johnsFlamingos->map(fn (Flamingo $flamingo) => $flamingo->keeper->zoo->address['street'])
            ->concat($this->oliversFlamingos->map(fn (Flamingo $flamingo) => $flamingo->keeper->zoo->address['street']))
            ->unique()
            ->sort()
            ->values();

    expect($queryBuilder->toSetValues(['*'])->toArray())->toMatchArray($names->toArray());
});

it('can retrieve set filter values for a deep nested related json column field', function () {
    $queryBuilder = new AgGridQueryBuilder(
        [
            'column' => 'keeper.zoo.address.contact.phone',
        ],
        Flamingo::class,
    );

    $phoneNumbers =
        $this->johnsFlamingos->map(fn (Flamingo $flamingo) => $flamingo->keeper->zoo->address['contact']['phone'])
            ->concat($this->oliversFlamingos->map(fn (Flamingo $flamingo) => $flamingo->keeper->zoo->address['contact']['phone']))
            ->unique()
            ->sort()
            ->values();

    expect($queryBuilder->toSetValues(['*'])->toArray())->toMatchArray($phoneNumbers->toArray());
});

it('can retrieve set filter values for a json array column', function () {
    $queryBuilder = new AgGridQueryBuilder(
        [
            'column' => 'preferred_food_types',
        ],
        Flamingo::class,
    );

    $foodTypes = $this
        ->johnsFlamingos
        ->concat($this->oliversFlamingos)
        ->flatMap(fn (Flamingo $flamingo) => $flamingo->preferred_food_types)
        ->unique()
        ->sort()
        ->values();

    expect($queryBuilder->toSetValues(['*'])->toArray())->toMatchArray($foodTypes->toArray());
});

it('throws exception when trying to retrieve set filter values without wildcard', function () {
    $queryBuilder = new AgGridQueryBuilder(
        [
            'column' => 'name',
        ],
        Flamingo::class,
    );

    $queryBuilder->toSetValues();
})->throws(UnauthorizedSetFilterColumn::class);

it('throws exception when trying to retrieve set filter values with wrong allowed column name', function () {
    $queryBuilder = new AgGridQueryBuilder(
        [
            'column' => 'name',
        ],
        Flamingo::class,
    );

    $queryBuilder->toSetValues(['flamingo_name']);
})->throws(UnauthorizedSetFilterColumn::class);

it('applies filters when retrieving set filter values', function () {
    $queryBuilder = new AgGridQueryBuilder(
        [
            'column' => 'name',
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

    $names =
        $this->johnsFlamingos->pluck('name')
            ->unique()
            ->sort()
            ->values();

    expect($queryBuilder->toSetValues(['*'])->toArray())->toMatchArray($names->toArray());
});

it('throws exception when trying to retrieve set filter values without the column in params', function () {
    $queryBuilder = new AgGridQueryBuilder(
        [
            'column2' => 'name',
        ],
        Flamingo::class,
    );

    $queryBuilder->toSetValues(['flamingo_name']);
})->throws(InvalidSetValueOperation::class);

it('throws exception when trying to retrieve set filter with empty column in params', function () {
    $queryBuilder = new AgGridQueryBuilder(
        [
            'column' => '',
        ],
        Flamingo::class,
    );

    $queryBuilder->toSetValues(['flamingo_name']);
})->throws(InvalidSetValueOperation::class);

it('throws exception when trying to retrieve set filter with null column in params', function () {
    $queryBuilder = new AgGridQueryBuilder(
        [
            'column' => null,
        ],
        Flamingo::class,
    );

    $queryBuilder->toSetValues(['flamingo_name']);
})->throws(InvalidSetValueOperation::class);
