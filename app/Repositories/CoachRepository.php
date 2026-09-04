<?php

namespace App\Repositories;

use App\Models\Coach;
use Illuminate\Support\Collection;

class CoachRepository
{
    public function create(string $fullName, int $competitionId): Coach
    {
        return Coach::create([
            'full_name' => $fullName,
            'competition_id' => $competitionId
        ]);
    }

    public function findUnique(string $fullName, int $competitionId): ?Coach
    {
        return Coach::where(['full_name' => $fullName, 'competition_id' => $competitionId])->first();
    }

    public function getLikeName(string $fullName, int $competitionId): Collection
    {
        return Coach::query()
            ->where('competition_id', $competitionId)
            ->where('full_name', 'like', "%$fullName%")
            ->get();
    }

    public function getById(int $id, int $competitionId): Coach
    {
        return Coach::query()->where('id', $id)->where('competition_id', $competitionId)->firstOrFail();
    }

    public function getPlayersAndRelation(int $coachId, int $competitionId): Collection
    {
        $coachWithPlayers = Coach::where([
            'id' => $coachId,
            'competition_id' => $competitionId,
        ])->with([
            'players' => function ($query) use ($competitionId) {
                return $query->where('competition_id', $competitionId)->with('categories');
            }
        ])->first();

        return $coachWithPlayers ? $coachWithPlayers->players : collect();
    }
}
