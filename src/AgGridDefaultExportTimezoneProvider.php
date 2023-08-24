<?php

namespace Clickbar\AgGrid;

use Clickbar\AgGrid\Contracts\AgGridExportTimezoneProvider;

class AgGridDefaultExportTimezoneProvider implements AgGridExportTimezoneProvider
{
    public function getAgGridExportTimezone(): string
    {
        return config('app.timezone');
    }
}
