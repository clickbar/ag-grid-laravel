<?php

namespace Clickbar\AgGrid\Enums;

enum AgGridExportFormat: string
{
    case Excel = 'excel';
    case Csv = 'csv';
    case Tsv = 'tsv';
}
