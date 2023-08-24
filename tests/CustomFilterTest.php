<?php

use Clickbar\AgGrid\AgGridQueryBuilder;
use Clickbar\AgGrid\Tests\TestClasses\Models\Flamingo;
use Clickbar\AgGrid\Tests\TestClasses\Models\Keeper;

it('applies custom filters correctly', function () {
    $keeper = Keeper::factory()->createOne();
    $notTrashed = Flamingo::factory()->count(2)->for($keeper)->create();
    $trashed = Flamingo::factory()->count(2)->trashed()->for($keeper)->create();

    $withoutTrashed = new AgGridQueryBuilder([
        'customFilters' => [
            'withTrashed' => false,
        ],
    ], Flamingo::class);

    $withTrashed = new AgGridQueryBuilder([
        'customFilters' => [
            'withTrashed' => true,
        ],
    ], Flamingo::class);

    expect($withoutTrashed->count())->toBe($notTrashed->count());
    expect($withTrashed->count())->toBe($trashed->count() + $notTrashed->count());
});
