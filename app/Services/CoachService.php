<?php

namespace App\Services;

use App\DTO\CoachCreateDTO;
use App\Models\Coach;
use App\Repositories\CoachRepository;

class CoachService
{
    public function __construct(
        private readonly CoachRepository $coachRepository,
    )
    {
    }

    public function __invoke(CoachCreateDTO $coachCreateDTO): Coach
    {
        $coach = $this->coachRepository->findUnique($coachCreateDTO->fullName, $coachCreateDTO->competitionId);

        if ($coach !== null) {
            return $coach;
        }

        return $this->coachRepository->create($coachCreateDTO->fullName, $coachCreateDTO->competitionId);
    }
}
