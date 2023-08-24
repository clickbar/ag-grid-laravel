<?php

namespace Clickbar\AgGrid\Formatters;

use Clickbar\AgGrid\AgGridFormatterContext;
use Clickbar\AgGrid\Contracts\AgGridValueFormatter;

class AgGridArrayFormatter implements AgGridValueFormatter
{
    public function __construct(private readonly string $separator = ',', private readonly ?AgGridValueFormatter $itemFormatter = null)
    {
    }

    public function format(AgGridFormatterContext $context, $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if ($this->itemFormatter !== null) {
            $value = array_map(function ($item) use ($context) {
                return $this->itemFormatter->format($context, $item);
            }, $value);
        }

        return implode($this->separator, $value);
    }
}
