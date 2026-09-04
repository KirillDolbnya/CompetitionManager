<?php

namespace App\Repositories;

use App\Models\Coach;
use App\Models\Player;
use Illuminate\Support\Collection;

class PlayerRepository
{
    public function create(string $fullName, int $competitionId, int $coachId): Player
    {
        return Player::create([
           'full_name' => $fullName,
           'competition_id' => $competitionId,
           'coach_id' => $coachId
        ]);
    }

    public function findUnique(string $fullName, int $competitionId): ?Player
    {
        return Player::where([
            'full_name' => $fullName,
            'competition_id' => $competitionId
        ])->first();
    }

    public function getLikeName(string $fullName, int $competitionId): Collection
    {
        return Player::query()
            ->where('competition_id', $competitionId)
            ->where('full_name', 'like', "%$fullName%")
            ->get();
    }

    public function getById(int $id, int $competitionId): Player
    {
        return Player::query()->where('id', $id)->where('competition_id', $competitionId)->firstOrFail();
    }

    public function getCoach(int $id): Coach
    {
        return Player::findOrFail($id)->coach;
    }

    public function getCategories(int $id): Collection
    {
        return Player::findOrFail($id)->categories;
    }
}
