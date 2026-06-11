<?php

namespace App\Vk\States;

use App\Vk\Bot;
use App\Vk\Context\DialogContext;
use App\Vk\StateManager;
use App\Vk\States\Interface\StateInterface;

class MenuState implements StateInterface
{
    public function __construct(
        private readonly Bot $bot,
    )
    {
    }

    public function enter(DialogContext $context, StateManager $stateManager): void
    {
    }

    public function execute(DialogContext $context, StateManager $stateManager): void
    {
        $this->bot->sendMessage(
            '⚠️ Выберете действие',
            $context->getUserId()
        );
    }
}
