<?php

namespace Clickbar\AgGrid\Formatters;

use Clickbar\AgGrid\AgGridFormatterContext;
use Clickbar\AgGrid\Contracts\AgGridValueFormatter;

class AgGridBooleanFormatter implements AgGridValueFormatter
{
    public function format(AgGridFormatterContext $context, $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value ? __('Ja') : __('Nein');
    }
}
