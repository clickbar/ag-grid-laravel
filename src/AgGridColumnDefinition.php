<?php

namespace Clickbar\AgGrid;

use Clickbar\AgGrid\Contracts\AgGridValueFormatter;

class AgGridColumnDefinition
{
    public function __construct(
        public string $id,
        public string $name,
        public ?AgGridValueFormatter $valueFormatter = null,
        public ?\Closure $valueGetter = null,
        public ?string $excelFormat = null
    ) {
    }
}
