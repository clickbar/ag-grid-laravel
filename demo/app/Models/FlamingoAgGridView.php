<?php

namespace App\Models;

use App\AgGrid\VirtualColumns\FlamingoKeeperColumn;
use Clickbar\AgGrid\AgGridColumnDefinition;
use Clickbar\AgGrid\Contracts\AgGridExportable;
use Clickbar\AgGrid\Contracts\AgGridHasVirtualColumns;
use Clickbar\AgGrid\Model\AgGridViewModel;

class FlamingoAgGridView extends AgGridViewModel implements AgGridExportable, AgGridHasVirtualColumns
{
    protected $table = 'flamingo_ag_grid_view';

    protected $casts = [
        // -- Flamingo
        'flamingo_weight' => 'float',
        'flamingo_preferred_food_types' => 'array',
        'flamingo_last_vaccinated_on' => 'date',
        'flamingo_custom_properties' => 'array',
        'flamingo_created_at' => 'immutable_datetime',
        'flamingo_updated_at' => 'immutable_datetime',
        // Keeper
        'keeper_created_at' => 'immutable_datetime',
        'keeper_updated_at' => 'immutable_datetime',
        // Zoo
        'zoo_address' => 'array',
        'zoo_created_at' => 'immutable_datetime',
        'zoo_updated_at' => 'immutable_datetime',
    ];

    public function getVirtualColumns(): array
    {
        return ['keeper' => new FlamingoKeeperColumn()];
    }

    public static function getAgGridColumnDefinitions(): array
    {
        return [
            new AgGridColumnDefinition('id', 'ID'),
            new AgGridColumnDefinition('flamingo_name', 'Flamingo Name'),
            new AgGridColumnDefinition('flamingo_species', 'Flamingo Species'),
            new AgGridColumnDefinition('flamingo_weight', 'Flamingo Weight'),
            new AgGridColumnDefinition('flamingo_preferred_food_types', 'Flamingo Preferred Food Types'),
            new AgGridColumnDefinition('flamingo_custom_properties', 'Flamingo Custom Properties'),
            new AgGridColumnDefinition('flamingo_is_hungry', 'Flamingo is Hungry'),
            new AgGridColumnDefinition('flamingo_last_vaccinated_on', 'Flamingo last vaccinated on'),
            new AgGridColumnDefinition('keeper_id', 'Keeper ID'),
            new AgGridColumnDefinition('keeper', 'Keeper Name'),
            new AgGridColumnDefinition('zoo_id', 'Zoo ID'),
            new AgGridColumnDefinition('zoo_name', 'Zoo Name'),
            new AgGridColumnDefinition('zoo_address.street', 'Zoo Street'),
            new AgGridColumnDefinition('zoo_address.city', 'Zoo City'),
            new AgGridColumnDefinition('zoo_address.email', 'Zoo Mail'),
            new AgGridColumnDefinition('zoo_address.phone', 'Zoo Phone'),
        ];

    }
}
