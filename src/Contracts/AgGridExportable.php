<?php

namespace Clickbar\AgGrid\Contracts;

use Clickbar\AgGrid\AgGridColumnDefinition;

interface AgGridExportable
{
    /**
     * @return AgGridColumnDefinition[]
     */
    public static function getAgGridColumnDefinitions(): array;
}
