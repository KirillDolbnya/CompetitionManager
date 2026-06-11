<?php

namespace App\Vk;

use App\Enums\VkBotState;
use App\Vk\Context\DialogContext;
use App\Vk\States\Interface\StateInterface;
use App\Vk\States\StartState;

class StateManager
{
    private StateInterface $state;

    public function __construct(
       private readonly DialogContext $context
    )
    {
        $state = VkBotState::tryFrom($context->getUserState());

        $this->state = app($state?->getClass() ?? StartState::class);
    }

    public function handle(): void
    {
        $command = $this->context->getCommand();

        if ($command !== null && $this->context->getUserState() !== $command) {
            $state = VkBotState::find($command);

            if ($state !== null) {
                $this->changeState($state);

                return;
            }else{
                $this->changeState(VkBotState::START);

                return;
            }
        }

        $this->state->execute($this->context, $this);
    }

    public function changeState(VkBotState $state): void
    {
        $this->context->setUserState($state);

        $this->state = app($state->getClass());

        $this->state->enter($this->context, $this);
    }
}
