<?php

use Clickbar\AgGrid\AgGridQueryBuilder;
use Clickbar\AgGrid\Tests\TestClasses\Models\Flamingo;
use Clickbar\AgGrid\Tests\TestClasses\Models\Keeper;
use Illuminate\Database\Eloquent\Factories\Sequence;

beforeEach(function () {
    $this->keeper = Keeper::factory()->createOne();
});

it('handles sorts correctly', function () {
    $flamingos = Flamingo::factory()->count(10)->sequence(
        fn (Sequence $sequence) => ['weight' => floatval($sequence->index)]
    )->for($this->keeper)->create();

    $sortedByWeightAsc = new AgGridQueryBuilder([
        'sortModel' => [
            [
                'colId' => 'weight',
                'sort' => 'asc',
            ],
        ],
    ], Flamingo::class);

    $sortedByWeightDesc = new AgGridQueryBuilder([
        'sortModel' => [
            [
                'colId' => 'weight',
                'sort' => 'desc',
            ],
        ],
    ], Flamingo::class);

    expect($sortedByWeightAsc->pluck('id')->all())->toBe($flamingos->sortBy('weight')->pluck('id')->all());
    expect($sortedByWeightDesc->pluck('id')->all())->toBe($flamingos->sortBy('weight', descending: true)->pluck('id')->all());
});
