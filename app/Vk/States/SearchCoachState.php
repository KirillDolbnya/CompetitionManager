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

class SearchCoachState implements StateInterface
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
        $message = "📝 Введите фио тренера";

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
        $competition = $context->getCompetition();
        $message = trim($context->getMessage());

        $words = preg_split('/\s+/', trim($message));
        if(count($words) < 2){
            $this->bot->sendMessage('⚠️ Пожалуйста, введите имя и фамилию тренера', $context->getUserId());
            return;
        }

        $coach = $this->coachRepository->getLikeName($message, $competition->id);

        if ($coach->isEmpty()){
            $this->bot->sendMessage('⚠️ Тренер не найден, введите ФИО еще раз', $context->getUserId());
            return;
        }else{
            $coach = $coach->first();
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
