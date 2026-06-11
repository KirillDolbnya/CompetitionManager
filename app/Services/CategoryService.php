<?php

namespace App\Services;

use App\DTO\CategoryCreateDTO;
use App\Models\Category;
use App\Repositories\CategoryRepository;

class CategoryService
{

    public function __construct(
        private readonly CategoryRepository $categoryRepository,
    )
    {
    }

    public function __invoke(CategoryCreateDTO $categoryCreateDTO): Category
    {
        return $this->categoryRepository->create($categoryCreateDTO->name, $categoryCreateDTO->number, $categoryCreateDTO->competitionId, $categoryCreateDTO->disciplineId);
    }

}
