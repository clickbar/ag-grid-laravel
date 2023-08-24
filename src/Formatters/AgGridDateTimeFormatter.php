<?php

namespace Clickbar\AgGrid\Formatters;

use Carbon\Carbon;
use Clickbar\AgGrid\AgGridFormatterContext;
use Clickbar\AgGrid\Contracts\AgGridValueFormatter;
use DateTimeInterface;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class AgGridDateTimeFormatter implements AgGridValueFormatter
{
    public const EXCEL_FORMAT = NumberFormat::FORMAT_DATE_DDMMYYYY.' '.NumberFormat::FORMAT_DATE_TIME6;

    /**
     * @param  DateTimeInterface|null  $value
     */
    public function format(AgGridFormatterContext $context, $value): ?float
    {
        if ($value === null) {
            return null;
        }

        if ($context->timezone !== null) {
            $value = (new Carbon($value))->setTimezone($context->timezone);
        }

        return \PhpOffice\PhpSpreadsheet\Shared\Date::dateTimeToExcel($value);
    }
}
