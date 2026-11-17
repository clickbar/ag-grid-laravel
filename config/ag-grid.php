<?php

// config for Clickbar/AgGrid
return [
    /*
     * The class that contains the provider for determining the timezone in exports.
     */
    'export_timezone_provider' => \Clickbar\AgGrid\AgGridDefaultExportTimezoneProvider::class,

    /*
     * When true, grids refuse every filter and sort column unless the controller passes allowed
     * columns or the model implements AgGridFilterable. When false, grids without an allowlist
     * accept every column that is not hidden on its model. This becomes true in the next major version.
     */
    'require_allowed_columns' => false,
];
