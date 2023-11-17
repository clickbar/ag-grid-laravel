<?php

namespace Clickbar\AgGrid\Contracts;

use Clickbar\AgGrid\Data\AgGridSetValue;

interface AgGridSetValueProvider
{
    /** @return AgGridSetValue[]|null */
    public static function provideAgGridSetValues(string $column): ?array;
}
