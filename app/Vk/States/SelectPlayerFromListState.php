<?php

namespace App\Vk\States;

use App\Enums\VkBotState;
use App\Models\Player;
use App\Repositories\PlayerRepository;
use App\Vk\Bot;
use App\Vk\Builders\KeyboardBuilder;
use App\Vk\Builders\OutputBuilder;
use App\Vk\Context\DialogContext;
use App\Vk\StateManager;
use App\Vk\States\Interface\StateInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SelectPlayerFromListState implements StateInterface
{
    public function __construct(
        private readonly Bot $bot,
        private readonly KeyboardBuilder $keyboardBuilder,
        private readonly OutputBuilder $outputBuilder,
        private readonly PlayerRepository $playerRepository,
    )
    {
    }

    public function enter(DialogContext $context, StateManager $stateManager): void
    {
    }

    public function execute(DialogContext $context, StateManager $stateManager): void
    {
        $playerId = $context->getPlayerId();

        if ($playerId === null) {
            $this->bot->sendMessage(
                '👥 Выберите спортсмена из списка',
                $context->getUserId(),
            );
            return;
        }

        try {
            $competition = $context->getCompetition();
            $player = $this->playerRepository->getById((int) $playerId, $competition->id);
            $message = $this->buildPlayerCard($player);

            $this->bot->sendMessage(
                $message,
                $context->getUserId(),
                $this->keyboardBuilder
                    ->textButton('🔍 Искать спортсмена', 'search_player', 'secondary')
                    ->textButton('◀️ Назад', 'start', 'secondary')
                    ->build(),
            );

            $stateManager->changeState(VkBotState::MENU);
            return;
        } catch (ModelNotFoundException $e) {
            $this->bot->sendMessage(
                '⚠️ Спортсмен не найден, попробуйте еще раз!',
                $context->getUserId(),
            );

            $stateManager->changeState(VkBotState::SEARCH_PLAYER);
            return;
        }
    }

    private function buildPlayerCard(Player $player): string
    {
        $coach = $this->playerRepository->getCoach($player->id);
        $categories = $this->playerRepository->getCategories($player->id);

        return $this->outputBuilder
            ->addHead("🏃 Спортсмен: {$player->full_name}")
            ->addIdent()
            ->addHead("💼 Тренер: {$coach->full_name}")
            ->addIdent()
            ->addList(
                '🏋️‍♂️ Категории:',
                $categories,
                fn($cat) => "{$cat->name}: 📍 доянг {$cat->number}"
            )
            ->build();
    }
}
