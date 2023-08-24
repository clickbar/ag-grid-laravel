<?php

use Clickbar\AgGrid\AgGridQueryBuilder;
use Clickbar\AgGrid\Tests\TestClasses\Models\Flamingo;
use Clickbar\AgGrid\Tests\TestClasses\Models\Keeper;

beforeEach(function () {
    $this->keeper = Keeper::factory()->createOne();

    $this->flamingos = Flamingo::factory()->count(10)->for($this->keeper)->create();
});

it('handles limit and offset correctly', function () {
    $queryBuilder = new AgGridQueryBuilder(
        [
            'startRow' => 5,
            'endRow' => 8,
        ],
        Flamingo::class,
    );

    expect($queryBuilder->get()->count())->toBe(3);
});
