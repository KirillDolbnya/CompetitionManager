<?php

namespace App\Repositories;

use App\Models\CompetitionNotification;

class CompetitionNotificationRepository
{
    public function firstOrCreate(int $competitionId, int $vkUserId): CompetitionNotification
    {
        return CompetitionNotification::firstOrCreate([
            'competition_id' => $competitionId,
            'vk_user_id' => $vkUserId
        ],
        [
            'status' => 'pending',
        ]);
    }
}
