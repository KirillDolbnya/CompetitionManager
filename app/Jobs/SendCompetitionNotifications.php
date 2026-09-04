<?php

namespace App\Jobs;

use App\Models\Competition;
use App\Models\CompetitionNotification;
use App\Vk\Builders\KeyboardBuilder;
use App\Vk\VkApiClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCompetitionNotifications implements ShouldQueue
{
    use Queueable, Dispatchable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly Competition $competition,
    )
    {
    }

    /**
     * Execute the job.
     */
    public function handle(
        VkApiClient $vkApiClient,
        KeyboardBuilder $keyboardBuilder,
    ): void
    {
        $competitionId = $this->competition->id;
        $competitionName = $this->competition->name;

        $keyboard = $keyboardBuilder
            ->textButton('Начать', 'start', 'primary')
            ->build();

        CompetitionNotification::query()
            ->where('competition_id', $competitionId)
            ->where('status', 'pending')
            ->with('vkUser')
            ->chunkById(100, function ($notifications) use ($competitionName, $keyboard, $vkApiClient) {
                foreach ($notifications as $notification) {
                    $user = $notification->vkUser;

                    try {
                        $vkApiClient->sendMessage(
                            "🏆 Соревнование «{$competitionName}» завершено! Спасибо за участие.",
                            $user->vk_id,
                            $keyboard
                        );

                        $notification->update([
                            'status' => 'sent',
                            'sent_at' => now(),
                        ]);

                        $user->update([
                            'state' => null
                        ]);
                    }catch (\Throwable $exception){
                        $notification->update([
                            'status' => 'failed',
                        ]);

                        Log::error($exception->getMessage(),[
                            'competition_id' => $notification->competition_id,
                            'vk_user_id' => $user->vk_id,
                        ]);
                    }
                }
            });
    }
}
