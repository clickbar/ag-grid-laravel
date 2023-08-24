<?php

namespace Clickbar\AgGrid\Contracts;

use Clickbar\AgGrid\AgGridFormatterContext;

interface AgGridValueFormatter
{
    public function format(AgGridFormatterContext $context, mixed $value): string|int|float|null;

    /**
     * @var string|null
     */
    public const EXCEL_FORMAT = null;
}
