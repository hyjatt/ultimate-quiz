<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            ['name' => 'McLaren', 'color' => '#FF8700'],
            ['name' => 'Red Bull Racing', 'color' => '#367FA9'],
            ['name' => 'Ferrari', 'color' => '#E80020'],
            ['name' => 'Mercedes-AMG', 'color' => '#27F4D2'],
            ['name' => 'Williams', 'color' => '#37BEDD'],
            ['name' => 'Aston Martin', 'color' => '#229971'],
            ['name' => 'Alpine', 'color' => '#0093CC'],
            ['name' => 'Haas', 'color' => '#B6BABD'],
            ['name' => 'Racing Bulls', 'color' => '#66BBFF'],
            ['name' => 'Audi', 'color' => '#F5192F'],
            ['name' => 'Cadillac', 'color' => '#FFFFFF'],
        ];

        foreach ($teams as $team) {
            Team::query()->updateOrCreate(['name' => $team['name']], $team);
        }
    }
}
