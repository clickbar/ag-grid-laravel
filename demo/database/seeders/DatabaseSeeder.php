<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Flamingo;
use App\Models\Keeper;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $keepers = Keeper::factory()->count(10)->create();
        Flamingo::factory()
            ->count(1000)
            ->sequence(...$keepers->map(fn(Keeper $keeper) => ['keeper_id' => $keeper->id])->all())
            ->create();
    }
}
