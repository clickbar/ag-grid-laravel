<?php

namespace Clickbar\AgGrid\Formatters;

use Clickbar\AgGrid\AgGridFormatterContext;
use Clickbar\AgGrid\Contracts\AgGridValueFormatter;

class AgGridBackedEnumFormatter implements AgGridValueFormatter
{
    public function format(AgGridFormatterContext $context, $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        if (property_exists($value, 'name')) {
            return $value->name;
        }

        return $value;
    }
}
