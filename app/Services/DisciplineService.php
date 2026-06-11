<?php

namespace App\Services;

use App\DTO\DisciplineCreateDTO;
use App\Models\Discipline;
use App\Repositories\DisciplineRepository;

class DisciplineService
{
    public function __construct(
        private readonly DisciplineRepository $disciplineRepository,
    )
    {
    }

    public function __invoke(DisciplineCreateDTO $disciplineCreateDTO): Discipline
    {
        $discipline = $this->disciplineRepository->getByName($disciplineCreateDTO->name, $disciplineCreateDTO->competitionId);

        if ($discipline !== null) {
            return $discipline;
        }

        return $this->disciplineRepository->create($disciplineCreateDTO->name, $disciplineCreateDTO->competitionId);
    }
}
