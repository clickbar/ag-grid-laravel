<?php

use Clickbar\AgGrid\AgGridQueryBuilder;
use Clickbar\AgGrid\Enums\AgGridTextFilterType;
use Clickbar\AgGrid\Tests\TestClasses\Models\Flamingo;
use Clickbar\AgGrid\Tests\TestClasses\Models\Keeper;

beforeEach(function () {
    $this->keeper = Keeper::factory()->createOne();

    $this->flamingosNamedSir = Flamingo::factory()->count(2)->state([
        'name' => 'Sir Stand-a-lot',
    ])->for($this->keeper)->create();

    $this->flamingosNamedLulu = Flamingo::factory()->count(3)->state([
        'name' => 'Longneck Lulu',
    ])->for($this->keeper)->create();
});

function buildTextFilter(AgGridTextFilterType $type, ?string $value = null): array
{
    return [
        'filterModel' => [
            'name' => [
                'filterType' => 'text',
                'type' => $type->value,
                'filter' => $value,
            ],
        ],
    ];
}

it('handles the text equals filter correctly', function () {
    $queryBuilder = new AgGridQueryBuilder(
        buildTextFilter(AgGridTextFilterType::Equals, 'Sir Stand-a-lot'),
        Flamingo::class,
    );

    expect($queryBuilder->count())->toBe($this->flamingosNamedSir->count());
});

it('handles the text not equals filter correctly', function () {
    $queryBuilder = new AgGridQueryBuilder(
        buildTextFilter(AgGridTextFilterType::NotEqual, 'Sir Stand-a-lot'),
        Flamingo::class,
    );

    expect($queryBuilder->count())->toBe($this->flamingosNamedLulu->count());
});

it('handles the text contains filter correctly', function () {
    $queryBuilder = new AgGridQueryBuilder(
        buildTextFilter(AgGridTextFilterType::Contains, 'Stand'),
        Flamingo::class,
    );

    expect($queryBuilder->count())->toBe($this->flamingosNamedSir->count());
});

it('handles the text contains not filter correctly', function () {
    $queryBuilder = new AgGridQueryBuilder(
        buildTextFilter(AgGridTextFilterType::NotContains, 'Stand'),
        Flamingo::class,
    );

    expect($queryBuilder->count())->toBe($this->flamingosNamedLulu->count());
});

it('handles the text starts with filter correctly', function () {
    $queryBuilder = new AgGridQueryBuilder(
        buildTextFilter(AgGridTextFilterType::StartsWith, 'Sir'),
        Flamingo::class,
    );

    expect($queryBuilder->count())->toBe($this->flamingosNamedSir->count());
});

it('handles the text blank filter correctly', function () {
    $queryBuilder = new AgGridQueryBuilder(
        buildTextFilter(AgGridTextFilterType::Blank),
        Flamingo::class,
    );

    expect($queryBuilder->count())->toBe(0);
});

it('handles the text not blank filter correctly', function () {
    $queryBuilder = new AgGridQueryBuilder(
        buildTextFilter(AgGridTextFilterType::NotBlank),
        Flamingo::class,
    );

    expect($queryBuilder->count())->toBe($this->flamingosNamedSir->count() + $this->flamingosNamedLulu->count());
});
