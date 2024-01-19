<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('flamingos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->float('weight');
            $table->jsonb('preferred_food_types');
            $table->jsonb('custom_properties')->nullable();
            $table->boolean('is_hungry');
            $table->date('last_vaccinated_on');
            $table->foreignId('keeper_id')->index()->constrained();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flamingos');
    }
};
