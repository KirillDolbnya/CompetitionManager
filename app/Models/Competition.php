<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Competition extends Model
{
    protected $fillable = [
        'name',
        'description',
        'image_path',
        'file_path',
        'start_at',
        'end_at',
        'cleanup_at'
    ];

    public function coaches(): HasMany
    {
        return $this->hasMany(Coach::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function disciplines(): hasMany
    {
        return $this->hasMany(Discipline::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }
}
