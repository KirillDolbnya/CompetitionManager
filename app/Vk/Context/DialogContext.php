<?php

namespace App\Vk\Context;

use App\Enums\VkBotState;
use App\Models\Competition;
use App\Models\VkUser;
use App\Services\VkEventService;

class DialogContext
{
    public function __construct(
        private readonly VkEventService $event,
        private readonly VkUser $user,
        private ?Competition $competition = null,
    )
    {
    }

    public function getEvent(): VkEventService
    {
        return $this->event;
    }

    public function getMessage(): string|null
    {
        return $this->event->getMessage() ?? null;
    }

    public function getPayload(): array
    {
        return $this->event->getPayload();
    }

    public function getCommand(): string|null
    {
        return $this->event->getPayload()['command'] ?? null;
    }

    public function getPlayerId(): string|null
    {
        return $this->event->getPayload()['player_id'] ?? null;
    }

    public function getCoachId(): string|null
    {
        return $this->event->getPayload()['coach_id'] ?? null;
    }

    public function getCompetition(): Competition
    {
        return $this->competition;
    }

    public function setCompetition(Competition $competition): void
    {
        $this->competition = $competition;
    }

    public function getUser(): VkUser
    {
        return $this->user;
    }


    public function getUserId(): int
    {
        return $this->user->vk_id;
    }


    public function getUserState(): ?string
    {
        return $this->user->state;
    }

    public function setUserState(VkBotState $state): void
    {
        $this->user->update([
            'state' => $state->value
        ]);

        $this->user->save();
    }

    public function cleanUserState(): void
    {
        $this->user->update([
           'state' => null
        ]);

        $this->user->save();
    }
}
