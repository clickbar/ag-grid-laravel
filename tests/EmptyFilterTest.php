<?php

use Clickbar\AgGrid\AgGridQueryBuilder;
use Clickbar\AgGrid\Tests\TestClasses\Models\Flamingo;
use Clickbar\AgGrid\Tests\TestClasses\Models\Keeper;

beforeEach(function () {
    $this->keeper = Keeper::factory()->createOne();
});

it('returns all models if no filter model is set', function () {
    $flamingos = Flamingo::factory()->count(5)->for($this->keeper)->create();

    $queryBuilder = new AgGridQueryBuilder([], Flamingo::class);

    expect($queryBuilder->count())->toBe($flamingos->count());
});
