<?php

namespace App\Vk\States;

use App\Enums\VkBotState;
use App\Vk\Bot;
use App\Vk\Builders\KeyboardBuilder;
use App\Vk\Context\DialogContext;
use App\Vk\StateManager;
use App\Vk\States\Interface\StateInterface;
use Illuminate\Support\Facades\Cache;

class EnterPasswordState implements StateInterface
{

    public function __construct(
        private readonly Bot $bot,
        private readonly KeyboardBuilder $keyboardBuilder,
    )
    {
    }

    public function enter(DialogContext $context, StateManager $stateManager): void
    {
        if (Cache::has("coach_auth:{$context->getUserId()}")) {
            $stateManager->changeState(VkBotState::SEARCH_COACH);
            return;
        }

        $message = '🔏 Введите пароль';

        $this->bot->sendMessage(
            $message,
            $context->getUserId(),
            $this->keyboardBuilder
                ->textButton('◀️ Назад', 'start', 'secondary')
                ->build(),
        );
    }

    public function execute(DialogContext $context, StateManager $stateManager): void
    {
        $messageEvent = trim($context->getMessage());

        if ($messageEvent === config('services.vk.admin')){

            Cache::put("coach_auth:{$context->getUserId()}", true, now()->addHours(12));

            $message = '✅ Вход успешно выполнен';

            $this->bot->sendMessage(
                $message,
                $context->getUserId(),
            );

            $stateManager->changeState(VkBotState::SEARCH_COACH);
            return;
        }else{
            $message = '⛔ Пароль неверный, попробуйте еще раз';

            $this->bot->sendMessage(
                $message,
                $context->getUserId(),
            );
        }
    }
}
