<?php

namespace App\Models;

use Clickbar\AgGrid\Model\AgGridViewModel;

class FlamingoAgGridView extends AgGridViewModel
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
}
