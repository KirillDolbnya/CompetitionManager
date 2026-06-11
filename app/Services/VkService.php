<?php

namespace App\Services;

use App\Exceptions\VkApiException;
use App\Repositories\VkUserRepository;
use App\Vk\Context\DialogContext;
use App\Vk\Middleware\EnsureCompetitionExists;
use App\Vk\StateManager;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Log;

class VkService
{
    public function __construct(
        private readonly VkUserRepository $vkUserRepository
    )
    {
    }

    public function handle(VkEventService $event): void
    {
        try {
            if (!$event->getPeerId()){
                return;
            }

            $vkUser = $this->vkUserRepository->firstOrCreate($event->getPeerId());

            $context = new DialogContext(
                event: $event,
                user: $vkUser,
            );

            app(Pipeline::class)
                ->send($context)
                ->through([EnsureCompetitionExists::class])
                ->then(function () use ($context) {
                    $stateManager = new StateManager($context);
                    $stateManager->handle();
                });

        }catch (VKApiException $e){
            Log::error('VK API error', [
                'user_id' => $event->getPeerId(),
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'exception' => $e,
            ]);
        }
    }
}
