<?php

namespace App\Services;

use App\Models\Competition;
use App\Models\VkUser;
use App\Vk\Builders\KeyboardBuilder;
use App\Vk\VkApiClient;
use Illuminate\Support\Facades\Log;

class VkNotificationService
{
    public function __construct(
        private readonly VkApiClient $vkApiClient,
        private readonly KeyboardBuilder $keyboardBuilder,
    )
    {
    }

    public function sendCompetitionStartMessage(Competition $competition): void
    {
        $keyboard = $this->keyboardBuilder
            ->textButton('Начать', 'start', 'primary')
            ->build();

        VkUser::query()
            ->chunkById(100, function (VkUser $users) use ($competition, $keyboard) {
                foreach ($users as $user) {
                    try {
                        $this->vkApiClient->sendMessage(
                            "🏆 Сегодня стартует новое соревнование! {$competition->title}",
                            $user->vk_id,
                            $keyboard
                        );
                    }catch (\Throwable $exception){
                        Log::error($exception->getMessage());
                    }
                }
            });
    }
}
