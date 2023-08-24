<?php

namespace Clickbar\AgGrid\Tests;

use Clickbar\AgGrid\AgGridServiceProvider;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Maatwebsite\Excel\ExcelServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use DatabaseMigrations;

    protected function getPackageProviders($app): array
    {
        return [
            AgGridServiceProvider::class,
            ExcelServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessModelNamesUsing(
            fn (Factory $factory) => 'Clickbar\\AgGrid\\Tests\\TestClasses\\Models\\'.Str::before(class_basename($factory::class), 'Factory')
        );

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Clickbar\\AgGrid\\Tests\\TestClasses\\Factories\\'.class_basename($modelName).'Factory'
        );

        /** @var DatabaseManager $db */
        $db = $this->app->get('db');

        $db->connection()->getSchemaBuilder()->create('keepers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $db->connection()->getSchemaBuilder()->create('flamingos', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->float('weight');
            $table->jsonb('preferred_food_types');
            $table->date('last_vaccinated_on')->nullable();
            $table->boolean('is_hungry')->default(false);
            $table->softDeletes();
            $table->foreignId('keeper_id')->constrained();
        });
    }
}
