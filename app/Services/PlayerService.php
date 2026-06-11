<?php

namespace App\Services;

use App\DTO\PlayerCreateDTO;
use App\Models\Category;
use App\Models\Discipline;
use App\Models\Player;
use App\Repositories\PlayerRepository;

class PlayerService
{
    public function __construct(
        private readonly PlayerRepository $playerRepository,
    )
    {
    }

    public function __invoke(PlayerCreateDTO $playerCreateDTO): Player
    {
        $player = $this->playerRepository->findUnique($playerCreateDTO->fullName, $playerCreateDTO->competitionId);

        if ($player !== null) {
            return $player;
        }

        return $this->playerRepository->create($playerCreateDTO->fullName, $playerCreateDTO->competitionId, $playerCreateDTO->coachId);
    }

    public function attachToCategoryAndDiscipline(Player $player, Discipline $discipline, Category $category): void
    {
        $player->disciplines()->syncWithoutDetaching([$discipline->id]);
        $player->categories()->syncWithoutDetaching([$category->id]);
    }
}
