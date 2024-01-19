<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS flamingo_ag_grid_view;');

        $sql = <<<SQL
            CREATE VIEW flamingo_ag_grid_view AS
            SELECT
                -- Flamingo
                flamingos.id AS id,
                flamingos.name AS flamingo_name,
                flamingos.weight AS flamingo_weight,
                flamingos.preferred_food_types AS flamingo_preferred_food_types,
                flamingos.custom_properties AS flamingo_custom_properties,
                flamingos.is_hungry AS flamingo_is_hungry,
                flamingos.last_vaccinated_on AS flamingo_last_vaccinated_on,
                flamingos.keeper_id AS flamingo_keeper_id,
                flamingos.deleted_at AS flamingo_deleted_at,
                flamingos.created_at AS flamingo_created_at,
                flamingos.updated_at AS flamingo_updated_at,
                -- Kepper
                keepers.id AS keeper_id,
                keepers.name AS keeper_name,
                keepers.zoo_id AS keeper_zoo_id,
                keepers.created_at AS keeper_created_at,
                keepers.updated_at AS keeper_updated_at,
                -- Zoo
                zoos.id AS zoo_id,
                zoos.name AS zoo_name,
                zoos.address AS zoo_address,
                zoos.created_at AS zoo_created_at,
                zoos.updated_at AS zoo_updated_at
            FROM
                flamingos
                JOIN keepers ON keepers.id = keeper_id
                JOIN zoos ON zoos.id = zoo_id;
SQL;

        DB::statement($sql);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS flamingo_ag_grid_view;');
    }
};
