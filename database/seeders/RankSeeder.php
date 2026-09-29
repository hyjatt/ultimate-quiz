<?php

namespace Database\Seeders;

use App\Models\Rank;
use Illuminate\Database\Seeder;

class RankSeeder extends Seeder
{
    public function run(): void
    {
        $ranks = [
            ['name' => 'Rookie', 'min_points' => 0],
            ['name' => 'Test Driver', 'min_points' => 20],
            ['name' => 'Midfield Contender', 'min_points' => 50],
            ['name' => 'Podium Finisher', 'min_points' => 90],
            ['name' => 'Grand Prix Winner', 'min_points' => 140],
            ['name' => 'World Champion', 'min_points' => 200],
        ];

        foreach ($ranks as $rank) {
            Rank::query()->updateOrCreate(['name' => $rank['name']], $rank);
        }
    }
}
