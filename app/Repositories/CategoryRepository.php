<?php

namespace App\Repositories;

use App\Models\Category;

class CategoryRepository
{
    public function create(string $name, int $number, int $competition_id, int $discipline_id): Category
    {
        return Category::create([
           'name' => $name,
           'number' => $number,
           'competition_id' => $competition_id,
           'discipline_id' => $discipline_id,
        ]);
    }
}
