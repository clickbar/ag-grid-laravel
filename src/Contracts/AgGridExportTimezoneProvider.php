<?php

namespace Clickbar\AgGrid\Contracts;

interface AgGridExportTimezoneProvider
{
    public function getAgGridExportTimezone(): ?\DateTimeZone;
}
