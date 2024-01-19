<?php

namespace Clickbar\AgGrid\Contracts;

use Clickbar\AgGrid\AgGridVirtualColumn;

interface AgGridHasVirtualColumns
{
    /** @return array<string, AgGridVirtualColumn> */
    public function getVirtualColumns(): array;
}
