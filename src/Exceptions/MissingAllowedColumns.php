<?php

namespace Clickbar\AgGrid\Exceptions;

use LogicException;

class MissingAllowedColumns extends LogicException
{
    public static function make(string $model): self
    {
        return new self("The grid for {$model} has no allowed columns. Pass them to the query builder or implement AgGridFilterable, since ag-grid.require_allowed_columns is enabled.");
    }
}
