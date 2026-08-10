<?php

namespace App\AgGrid\VirtualColumns;

use App\Models\FlamingoAgGridView;
use Clickbar\AgGrid\AgGridQueryBuilder;
use Clickbar\AgGrid\AgGridVirtualColumn;
use Clickbar\AgGrid\Data\AgGridSetValue;
use Illuminate\Support\Collection;

/** @extends AgGridVirtualColumn<FlamingoAgGridView> */
class FlamingoKeeperColumn extends AgGridVirtualColumn
{
    public function getValue($row): mixed
    {
        return $row->keeper_name;
    }

    public function getSetValues(AgGridQueryBuilder $builder): Collection
    {
        return $builder
            ->distinct()
            ->select('keeper_id', 'keeper_name')
            ->orderBy('keeper_name')
            ->get()
            ->map(fn ($data) => new AgGridSetValue($data->keeper_id, $data->keeper_name));
    }

    public function getOrderColumns(): array
    {
        return ['keeper_name'];
    }

    public function getFilterColumn(): string
    {
        return 'keeper_id';
    }
}
