<?php

namespace Clickbar\AgGrid\Contracts;

interface AgGridFilterable
{
    /**
     * The colIds (columns, dotted relation paths or json paths) clients may filter and sort by.
     * Use ['*'] to allow every column that is not hidden on its model.
     *
     * @return string[]
     */
    public static function getAgGridAllowedColumns(): array;
}
