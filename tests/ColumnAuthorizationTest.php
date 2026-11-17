<?php

use Clickbar\AgGrid\AgGridQueryBuilder;
use Clickbar\AgGrid\Exceptions\UnauthorizedColumn;
use Clickbar\AgGrid\Exceptions\UnauthorizedSetFilterColumn;
use Clickbar\AgGrid\Tests\TestClasses\Models\Flamingo;
use Clickbar\AgGrid\Tests\TestClasses\Models\Keeper;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;

beforeEach(function () {
    $this->keeper = Keeper::factory()->createOne(['secret' => 'hunter2']);
    $this->flamingos = Flamingo::factory()->count(3)->for($this->keeper)->create();
    Flamingo::factory()->count(2)->for(Keeper::factory()->state(['secret' => 'other']))->create();
});

function secretFilter(string $prefix): array
{
    return ['filterType' => 'text', 'type' => 'startsWith', 'filter' => $prefix];
}

it('refuses filters on hidden columns', function (string $subject, string $colId) {
    new AgGridQueryBuilder(['filterModel' => [$colId => secretFilter('hun')]], $subject);
})->with([
    'own column' => [Keeper::class, 'secret'],
    'related column' => [Flamingo::class, 'keeper.secret'],
])->throws(UnauthorizedColumn::class);

it('refuses sorts on hidden columns', function () {
    new AgGridQueryBuilder(['sortModel' => [['colId' => 'keeper.secret', 'sort' => 'asc']]], Flamingo::class);
})->throws(UnauthorizedColumn::class);

it('refuses columns outside an explicit allowlist', function () {
    new AgGridQueryBuilder(
        ['filterModel' => ['species' => ['filterType' => 'set', 'values' => ['chilean']]]],
        Flamingo::class,
        ['name', 'keeper.name'],
    );
})->throws(UnauthorizedColumn::class);

it('filters allowlisted columns, including hidden ones the developer names explicitly', function () {
    $queryBuilder = new AgGridQueryBuilder(
        [
            'filterModel' => ['keeper.secret' => secretFilter('hun')],
            'sortModel' => [['colId' => 'keeper.name', 'sort' => 'asc']],
        ],
        Flamingo::class,
        ['keeper.name', 'keeper.secret'],
    );

    expect($queryBuilder->get()->pluck('id')->sort()->values()->all())
        ->toBe($this->flamingos->pluck('id')->sort()->values()->all());
});

it('refuses every column without an allowlist when the config requires one', function () {
    config(['ag-grid.require_allowed_columns' => true]);

    new AgGridQueryBuilder(['filterModel' => ['name' => secretFilter('a')]], Flamingo::class);
})->throws(UnauthorizedColumn::class);

it('applies the allowlist to the filters of a set values request', function () {
    AgGridQueryBuilder::forSelection(
        ['column' => 'name', 'filterModel' => ['keeper.secret' => secretFilter('hun')]],
        Flamingo::class,
    )->toSetValues(['name']);
})->throws(UnauthorizedColumn::class);

it('refuses set values for hidden columns under the wildcard', function () {
    (new AgGridQueryBuilder(['column' => 'keeper.secret'], Flamingo::class))->toSetValues(['*']);
})->throws(UnauthorizedSetFilterColumn::class);

it('quotes json paths instead of running them as sql', function () {
    // Unquoted, the subquery would decide whether rows match; quoted, it names a column that does not exist.
    $colId = "'{}'::jsonb, 'q') is null and (select count(*) > 0 from keepers) and jsonb_extract_path('{\"a\":[\"x\"]}'::jsonb.a";

    (new AgGridQueryBuilder(['filterModel' => [$colId => ['filterType' => 'set', 'values' => ['x']]]], Flamingo::class))->get();
})->throws(QueryException::class, 'does not exist');

it('exports only columns the model defines', function () {
    $queryBuilder = new AgGridQueryBuilder(
        ['exportFormat' => 'csv', 'exportColumns' => ['name', 'keeper.secret', 'keeper_id']],
        Flamingo::whereKey($this->flamingos->first()->id),
    );

    $response = TestResponse::fromBaseResponse($queryBuilder->toResponse(new Request));
    $csv = $response->baseResponse->getFile()->getContent();

    expect($csv)->not->toContain('hunter2')
        ->and($csv)->toContain($this->keeper->name)
        ->and(substr_count(strtok($csv, "\n"), ','))->toBe(1);
});
