<?php

namespace App\Models;

use App\Enums\VkBotState;
use Illuminate\Database\Eloquent\Model;

class VkUser extends Model
{
    protected $fillable = [
        'vk_id',
        'state',
    ];
}
