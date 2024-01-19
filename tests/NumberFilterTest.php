<?php

use Clickbar\AgGrid\AgGridQueryBuilder;
use Clickbar\AgGrid\Enums\AgGridNumberFilterType;
use Clickbar\AgGrid\Tests\TestClasses\Models\Flamingo;
use Clickbar\AgGrid\Tests\TestClasses\Models\Keeper;

beforeEach(function () {
    $this->keeper = Keeper::factory()->createOne();

    $this->flamingosWeighing10 = Flamingo::factory()->count(2)->state([
        'weight' => 10.0,
    ])->for($this->keeper)->create();

    $this->flamingosWeighing5 = Flamingo::factory()->count(3)->state([
        'weight' => 5.0,
    ])->for($this->keeper)->create();
});

function buildNumberFilter(AgGridNumberFilterType $type, float $value1, ?float $value2 = null): array
{
    return [
        'filterModel' => [
            'weight' => [
                'filterType' => 'number',
                'type' => $type->value,
                'filter' => $value1,
                'filterTo' => $value2,
            ],
        ],
    ];
}

it('handles the number equals filter correctly', function () {
    $queryBuilder = new AgGridQueryBuilder(
        buildNumberFilter(AgGridNumberFilterType::Equals, 5.0),
        Flamingo::class,
    );

    expect($queryBuilder->count())->toBe($this->flamingosWeighing5->count());
});

it('handles the number not equals filter correctly', function () {
    $queryBuilder = new AgGridQueryBuilder(
        buildNumberFilter(AgGridNumberFilterType::NotEqual, 5.0),
        Flamingo::class,
    );

    expect($queryBuilder->count())->toBe($this->flamingosWeighing10->count());
});

it('handles the number greater than filter correctly', function () {
    $queryBuilder = new AgGridQueryBuilder(
        buildNumberFilter(AgGridNumberFilterType::GreaterThan, 5.0),
        Flamingo::class
    );

    expect($queryBuilder->count())->toBe($this->flamingosWeighing10->count());
});

it('handles the number greater than or equal filter correctly', function () {
    $queryBuilder = new AgGridQueryBuilder(
        buildNumberFilter(AgGridNumberFilterType::GreaterThanOrEqual, 5.0),
        Flamingo::class
    );

    expect($queryBuilder->count())->toBe($this->flamingosWeighing5->count() + $this->flamingosWeighing10->count());
});

it('handles the number less than filter correctly', function () {
    $queryBuilder = new AgGridQueryBuilder(
        buildNumberFilter(AgGridNumberFilterType::LessThan, 10.0),
        Flamingo::class
    );

    expect($queryBuilder->count())->toBe($this->flamingosWeighing5->count());
});

it('handles the number less than or equal filter correctly', function () {
    $queryBuilder = new AgGridQueryBuilder(
        buildNumberFilter(AgGridNumberFilterType::LessThanOrEqual, 10.0),
        Flamingo::class
    );

    expect($queryBuilder->count())->toBe($this->flamingosWeighing5->count() + $this->flamingosWeighing10->count());
});

it('handles the in range filter correctly', function () {
    $queryBuilder = new AgGridQueryBuilder(
        buildNumberFilter(AgGridNumberFilterType::InRange, 4.0, 6.0),
        Flamingo::class
    );

    expect($queryBuilder->count())->toBe($this->flamingosWeighing5->count());
});
