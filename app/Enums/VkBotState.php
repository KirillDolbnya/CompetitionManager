<?php

namespace App\Enums;

use App\Vk\States\EnterPasswordState;
use App\Vk\States\MenuState;
use App\Vk\States\SearchCoachState;
use App\Vk\States\SearchPlayerState;
use App\Vk\States\SelectPlayerFromListState;
use App\Vk\States\StartState;

enum VkBotState: string
{
    case START = 'start';

    case SEARCH_PLAYER = 'search_player';

    case ENTER_PASSWORD = 'enter_password';

    case SEARCH_COACH = 'search_coach';

    case SELECT_PLAYER = 'select_player';

    case MENU = 'menu';

    public static function find(string $command): self|null
    {
        $exactState = self::tryFrom($command);

        if ($exactState !== null) {
            return $exactState;
        }

        if (preg_match('/^player_\d+$/', $command)) {
            return self::SELECT_PLAYER;
        }

        return null;
    }

    public function getClass(): string
    {
        return match ($this) {
            self::START => StartState::class,
            self::SEARCH_PLAYER => SearchPlayerState::class,
            self::ENTER_PASSWORD => EnterPasswordState::class,
            self::SEARCH_COACH => SearchCoachState::class,
            self::SELECT_PLAYER => SelectPlayerFromListState::class,
            self::MENU => MenuState::class,
        };
    }
}
