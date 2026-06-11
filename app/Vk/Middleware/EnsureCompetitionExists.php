<?php

namespace App\Vk\Middleware;

use App\Repositories\CompetitionRepository;
use App\Vk\Bot;
use App\Vk\Builders\KeyboardBuilder;
use App\Vk\Context\DialogContext;
use Illuminate\Support\Facades\Cache;

class EnsureCompetitionExists
{
    public function __construct(
        private readonly Bot $bot,
        private readonly CompetitionRepository $competitionRepository,
        private readonly KeyboardBuilder $keyboardBuilder,
    )
    {
    }

    public function handle(DialogContext $context, \Closure $next)
    {
        $currentCompetition = $this->competitionRepository->getCurrentCompetition();
        $keyboard = $this->keyboardBuilder
            ->textButton('Начать', 'start', 'primary')
            ->build();

        if (!$currentCompetition) {
            $this->bot->sendMessage(
                '⚠️ В данный момент нет активных соревнований, бот отдыхает!',
                $context->getUserId(),
                $keyboard
            );

            return;
        }

        $context->setCompetition($currentCompetition);

        return $next($context);
    }
}
