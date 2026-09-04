<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionNotification extends Model
{
    protected $table = 'competition_notifications';

    protected $fillable = [
        'competition_id',
        'vk_user_id',
        'status',
        'sent_at'
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function vkUser(): BelongsTo
    {
        return $this->belongsTo(VkUser::class);
    }
}
