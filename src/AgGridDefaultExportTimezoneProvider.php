<?php

namespace Clickbar\AgGrid;

use Clickbar\AgGrid\Contracts\AgGridExportTimezoneProvider;

class AgGridDefaultExportTimezoneProvider implements AgGridExportTimezoneProvider
{
    public function getAgGridExportTimezone(): ?\DateTimeZone
    {
        try {
            return new \DateTimeZone(config('app.timezone'));
        } catch (\Exception $exception) {
            return null;
        }
    }
}
