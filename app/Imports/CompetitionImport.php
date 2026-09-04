<?php

namespace App\Imports;

use App\DTO\CategoryCreateDTO;
use App\DTO\CoachCreateDTO;
use App\DTO\DisciplineCreateDTO;
use App\DTO\PlayerCreateDTO;
use App\Enums\DisciplineType;
use App\Exceptions\CompetitionImportException;
use App\Models\Category;
use App\Models\Discipline;
use App\Services\CategoryService;
use App\Services\CoachService;
use App\Services\DisciplineService;
use App\Services\PlayerService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;

class CompetitionImport implements ToCollection
{
    /**
    * @param Collection $collection
    */

    private const COL_TYPE = 0;
    private const COL_PLAYER_NAME = 1;
    private const COL_COACH_NAME = 9;

    private array $errors = [];
    private Discipline|null $currentDiscipline = null;
    private Category|null $currentCategory = null;

    public function __construct(
        private readonly int $competition_id,
        private readonly DisciplineService $disciplineService,
        private readonly CategoryService $categoryService,
        private readonly CoachService $coachService,
        private readonly PlayerService $playerService,
    )
    {
    }

    public function collection(Collection $collection)
    {
        DB::transaction(function () use ($collection) {
            foreach ($collection as $rowNumber => $item) {
                if ($this->isCategoryRow($item)) {
                    $this->processCategory($item, $rowNumber);
                } elseif ($this->isPlayerRow($item)) {
                    $this->processPlayer($item, $rowNumber);
                }
            }

            if (!empty($this->errors)) {
                throw new CompetitionImportException($this->errors);
            }
        });
    }

    private function isCategoryRow(mixed $item): bool
    {
        return !is_int($item[self::COL_TYPE]) && !empty($item[self::COL_TYPE]);
    }

    private function isPlayerRow(mixed $item): bool
    {
        return !empty($item[self::COL_PLAYER_NAME]) && is_int($item[self::COL_TYPE]);
    }

    private function isValidFullName(mixed $fullName): bool
    {
        if (!is_string($fullName) || empty(trim($fullName))) {
            return false;
        }

        $cleanName = preg_replace('/\s+/', ' ', trim($fullName));

        $wordPattern = '[А-ЯЁа-яё]+(?:-[А-ЯЁа-яё]+)?';
        $pattern = "/^{$wordPattern} {$wordPattern}(?: {$wordPattern})?$/u";

        return (bool) preg_match($pattern, $cleanName);
    }

    private function processCategory(mixed $item, int $rowNumber): void
    {
        $rowErrors = [];
        $rowNumber += 1;

        $this->currentDiscipline = null;
        $this->currentCategory = null;

        $categoryFullName = trim($item[self::COL_TYPE]);

        preg_match('/^\d+/', $categoryFullName, $match);
        $code = $match[0] ?? null;

        if (!$code) return;

        $disciplineName = null;
        foreach (DisciplineType::values() as $label) {
            if (stripos($categoryFullName, $label) !== false) {
                $disciplineName = $label;
                break;
            }
        }

        $categoryNumber = $code[1] ?? null;
        $categoryName = trim(str_ireplace($code, '', $categoryFullName));

        if (!$disciplineName) {
            $rowErrors[] = "Строка {$rowNumber}: ошибка в названии дисциплины";
        }
        if (empty($categoryNumber)) {
            $rowErrors[] = "Строка {$rowNumber}: не найден код категории";
        }
        if (empty($categoryName)) {
            $rowErrors[] = "Строка {$rowNumber}: не найдено название категории";
        }

        if (!empty($rowErrors)) {
            $this->errors = array_merge($this->errors, $rowErrors);
            return;
        }

        $disciplineDTO = new DisciplineCreateDTO();
        $disciplineDTO->competitionId = $this->competition_id;
        $disciplineDTO->name = $disciplineName;
        $this->currentDiscipline = ($this->disciplineService)($disciplineDTO);

        $categoryDTO = new CategoryCreateDTO();
        $categoryDTO->name = $categoryName;
        $categoryDTO->number = $categoryNumber;
        $categoryDTO->competitionId = $this->competition_id;
        $categoryDTO->disciplineId = $this->currentDiscipline->id;
        $this->currentCategory = ($this->categoryService)($categoryDTO);
    }

    private function processPlayer(mixed $item, int $rowNumber): void
    {
        $rowErrors = [];
        $rowNumber += 1;

        $coachName = $item[self::COL_COACH_NAME] ?? '';
        $playerName = $item[self::COL_PLAYER_NAME] ?? '';

        if (!$this->isValidFullName($coachName)) {
            $rowErrors[] = "Строка {$rowNumber}: некорректное ФИО тренера";
        }
        if (!$this->isValidFullName($playerName)) {
            $rowErrors[] = "Строка {$rowNumber}: некорректное ФИО спортсмена";
        }

//        if(!empty($rowErrors) || !$this->currentDiscipline || !$this->currentCategory) {
//            $this->errors = array_merge($this->errors, $rowErrors);
//            return;
//        }

        if (!empty($rowErrors)) {
            $this->errors = array_merge($this->errors, $rowErrors);
            return;
        }

        if (!$this->currentDiscipline || !$this->currentCategory) {
            return;
        }

        $coachDTO = new CoachCreateDTO();
        $coachDTO->competitionId = $this->competition_id;
        $coachDTO->fullName = Str::title(trim($coachName));
        $coach = ($this->coachService)($coachDTO);

        $playerDTO = new PlayerCreateDTO();
        $playerDTO->fullName = Str::title(trim($playerName));
        $playerDTO->competitionId = $this->competition_id;
        $playerDTO->coachId = $coach->id;
        $player = ($this->playerService)($playerDTO);

        if ($coach->id !== $player->coach->id) {
            $this->errors[] = "Строка {$rowNumber}: спортсмен не может быть связан с разными тренерами";

            return;
        }

        $this->playerService->attachToCategoryAndDiscipline($player, $this->currentDiscipline, $this->currentCategory);
    }
}
