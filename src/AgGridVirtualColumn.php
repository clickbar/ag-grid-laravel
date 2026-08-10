<?php

namespace Clickbar\AgGrid;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @template T of Model
 */
abstract class AgGridVirtualColumn
{
    /**
     * Build the value for this virtual column
     *
     * @param  T  $row  with all columns
     */
    abstract public function getValue($row): mixed;

    public function getSetValues(AgGridQueryBuilder $builder): ?Collection
    {
        return null;
    }

    abstract public function getOrderColumns(): array;

    abstract public function getFilterColumn(): string;
}
