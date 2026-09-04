<?php

namespace App\Vk\States;

use App\Enums\VkBotState;
use App\Repositories\CoachRepository;
use App\Vk\Bot;
use App\Vk\Builders\KeyboardBuilder;
use App\Vk\Builders\OutputBuilder;
use App\Vk\Context\DialogContext;
use App\Vk\StateManager;
use App\Vk\States\Interface\StateInterface;
use App\Vk\States\Traits\HasChunkedSending;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SelectCoachFromListState implements StateInterface
{
    use HasChunkedSending;

    public function __construct(
        private readonly Bot $bot,
        private readonly KeyboardBuilder $keyboardBuilder,
        private readonly OutputBuilder $outputBuilder,
        private readonly CoachRepository $coachRepository,
    )
    {
    }

    public function enter(DialogContext $context, StateManager $stateManager): void
    {
    }

    public function execute(DialogContext $context, StateManager $stateManager): void
    {
        $coachId = $context->getCoachId();

        if ($coachId === null) {
            $this->bot->sendMessage(
                '👥 Выберите тренера из списка',
                $context->getUserId(),
            );
            return;
        }

        try {
            $competition = $context->getCompetition();
            $coach = $this->coachRepository->getById((int)$coachId, $competition->id);
            $players = $this->coachRepository->getPlayersAndRelation($coach->id, $competition->id);
            $callback = $this->getChunkCallback($coach->full_name);

            $this->sendInChunks(
                $this->bot,
                $this->outputBuilder,
                $context->getUserId(),
                $players,
                10,
                $callback,
                $this->keyboardBuilder
                    ->textButton('🔍 Искать тренера', 'search_coach', 'secondary')
                    ->textButton('◀️ Назад', 'start', 'secondary')
                    ->build(),
            );

            $stateManager->changeState(VkBotState::MENU);
        }catch (ModelNotFoundException $e){
            $this->bot->sendMessage(
                '⚠️ Тренер не найден, попробуйте еще раз!',
                $context->getUserId(),
            );

            $stateManager->changeState(VkBotState::SEARCH_COACH);
            return;
        }
    }

    private function getChunkCallback(string $coachName): \Closure
    {
        return function (OutputBuilder $output, $chunkOfPlayers, $index) use ($coachName) {
            if ($index === 0) {
                $output->addHead("💼 Тренер: {$coachName}")->addIdent();
            }

            foreach ($chunkOfPlayers as $player) {
                $output->addHead("🏃‍♂️ Спортсмен: {$player->full_name}");
                $output->addList('🏋️‍♂ Категории:', $player->categories, fn($cat) => "{$cat->name}: 📍 доянг {$cat->number}");
                $output->addIdent();
            }
        };
    }
}
