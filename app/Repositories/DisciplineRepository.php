<?php

namespace App\Repositories;

use App\Models\Discipline;

class DisciplineRepository
{
    public function create(string $name, int $competitionId): Discipline
    {
        return Discipline::create([
            'name' => $name,
            'competition_id' => $competitionId
        ]);
    }

    public function getByName(string $name, int $competitionId): ?Discipline
    {
        return Discipline::where([
            'name' => $name,
            'competition_id' => $competitionId
        ])->first();
    }
}
