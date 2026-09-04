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
use Illuminate\Support\Collection;

class SearchPlayerState implements StateInterface
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
        $message = '📝 Введите ФИО спортсмена';

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
        $words = preg_split('/\s+/', $message);
        if (count($words) < 2 || empty($message)) {
            $this->bot->sendMessage('⚠️ Пожалуйста, введите ❗ИМЯ и ФАМИЛИЮ❗ спортсмена', $context->getUserId());
            return;
        }

        $players = $this->playerRepository->getLikeName($message, $competition->id);
        if ($players->isEmpty()){
            $this->bot->sendMessage('⚠️ Спортсмен не найден, введите ❗ИМЯ и ФАМИЛИЮ❗ еще раз', $context->getUserId());
            return;
        }

        if($players->count() === 1) {
            $player = $players->first();

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
        }else{
            $message = '👥 Выберите спортсмена';
            $keyboard = $this->buildMultiplePlayersKeyboard($players);

            $this->bot->sendMessage(
                $message,
                $context->getUserId(),
                $keyboard,
            );

            $stateManager->changeState(vkBotState::SELECT_PLAYER);
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

    private function buildMultiplePlayersKeyboard(Collection $players): array
    {
        foreach ($players as $player){
            $this->keyboardBuilder
                ->textButton("👤 {$player->full_name}", "select_player", 'secondary', ["player_id" => $player->id])
                ->row();
        }

        return $this->keyboardBuilder->build(true);
    }
}
