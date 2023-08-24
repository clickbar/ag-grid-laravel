<?php

use Clickbar\AgGrid\AgGridQueryBuilder;
use Clickbar\AgGrid\Enums\AgGridRowModel;
use Clickbar\AgGrid\Tests\TestClasses\Models\Flamingo;
use Clickbar\AgGrid\Tests\TestClasses\Models\Keeper;
use Illuminate\Database\Eloquent\Factories\Sequence;

beforeEach(function () {
    $this->keeper = Keeper::factory()->createOne();

    $this->flamingos = Flamingo::factory()->count(10)
        ->sequence(fn (Sequence $sequence) => ['weight' => floatval($sequence->index)])
        ->for($this->keeper)->create();
});

it('handles client side selection correctly', function () {
    $queryBuilder = AgGridQueryBuilder::forSelection(
        [
            'rowModel' => AgGridRowModel::ClientSide->value,
            'toggledNodes' => [1, 2],
        ],
        Flamingo::class,
    );

    expect($queryBuilder->get()->count())->toBe(2);
});

it('handles server side selection correctly', function () {
    $queryBuilder = AgGridQueryBuilder::forSelection(
        [
            'rowModel' => AgGridRowModel::ServerSide->value,
            'selectAll' => false,
            'toggledNodes' => [1, 2],
        ],
        Flamingo::class,
    );

    expect($queryBuilder->get()->count())->toBe(2);
});

it('handles server side select all correctly', function () {
    $queryBuilder = AgGridQueryBuilder::forSelection(
        [
            'rowModel' => AgGridRowModel::ServerSide->value,
            'selectAll' => true,
            'toggledNodes' => [],
        ],
        Flamingo::class,
    );

    expect($queryBuilder->get()->count())->toBe($this->flamingos->count());
});

it('handles server side select all with deselection correctly', function () {
    $queryBuilder = AgGridQueryBuilder::forSelection(
        [
            'rowModel' => AgGridRowModel::ServerSide->value,
            'selectAll' => true,
            'toggledNodes' => [1, 2],
        ],
        Flamingo::class,
    );

    expect($queryBuilder->get()->count())->toBe($this->flamingos->count() - 2);
});

it('handles server side selection with filters correctly', function () {
    $queryBuilder = AgGridQueryBuilder::forSelection(
        [
            'rowModel' => AgGridRowModel::ServerSide->value,
            'selectAll' => true,
            'toggledNodes' => [1, 2],
            'filterModel' => [
                'weight' => [
                    'filterType' => 'number',
                    'type' => 'lessThan',
                    'filter' => 5.0,
                ],
            ],
        ],
        Flamingo::class,
    );

    expect($queryBuilder->get()->count())->toBe($this->flamingos->where('weight', '<', 5.0)->count() - 2);
});
