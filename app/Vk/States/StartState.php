<?php

namespace App\Vk\States;

use App\Vk\Bot;
use App\Vk\Builders\KeyboardBuilder;
use App\Vk\Context\DialogContext;
use App\Vk\StateManager;
use App\Vk\States\Interface\StateInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class StartState implements StateInterface
{
    public function __construct(
        private readonly Bot $bot,
        private readonly KeyboardBuilder $keyboardBuilder,
    )
    {
    }

    public function enter(DialogContext $context, StateManager $stateManager): void
    {
        $competition = $context->getCompetition();

        $attachment = Cache::get("attachment_{$competition->id}");

        if ($attachment === null) {
            $imageName = $competition->image_path;
            $localPath = storage_path('app/public/' . $imageName);

            $attachment = $this->bot->uploadImage($localPath, $context->getUserId());

            if ($attachment) {
                $endAt = Carbon::parse($competition->end_at)->endOfDay();

                Cache::put("attachment_{$competition->id}", $attachment, $endAt);
            }
        }

        $message = "🏆 Добро пожаловать на {$competition->name} соревнования";

        $this->bot->sendMessage(
            $message,
            $context->getUserId(),
            $this->keyboardBuilder
                ->textButton('🔍 Искать спортсмена', 'search_player', 'secondary')
                ->textButton('🔐️ Тренерский доступ', 'enter_password', 'secondary')
                ->build(),
            $attachment ?? '',
        );
    }

    public function execute(DialogContext $context, StateManager $stateManager): void
    {
        $this->bot->sendMessage(
            '⚠️ Выберете действие',
            $context->getUserId(),
        );
    }
}
