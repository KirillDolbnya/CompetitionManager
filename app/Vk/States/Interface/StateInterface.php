<?php

namespace App\Vk\States\Interface;

use App\Vk\Context\DialogContext;
use App\Vk\StateManager;

interface StateInterface
{
    public function enter(DialogContext $context, StateManager $stateManager): void;

    public function execute(DialogContext $context, StateManager $stateManager): void;
}
