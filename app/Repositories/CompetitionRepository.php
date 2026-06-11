<?php

namespace App\Repositories;

use App\Models\Competition;

class CompetitionRepository
{
    public function getCurrentCompetition(): ?Competition
    {
        $now = now();

        return Competition::query()->where('start_at', '<=', $now->startOfDay())->where('end_at', '>=', $now->startOfDay())->first();
    }
}
