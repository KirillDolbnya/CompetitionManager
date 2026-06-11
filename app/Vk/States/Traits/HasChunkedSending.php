<?php

namespace App\Vk\States\Traits;

use App\Vk\Bot;
use App\Vk\Builders\OutputBuilder;
use Illuminate\Support\Collection;

trait HasChunkedSending
{
    protected function sendInChunks(
        Bot $bot,
        OutputBuilder $outputBuilder,
        int $vkId,
        Collection $collection,
        int $chunkSize,
        callable $callback,
        array $keyboard = []
    ): void
    {
        $chunks = $collection->chunk($chunkSize);
        $totalChunks = $chunks->count();

        foreach ($chunks as $index => $currentChunk) {

            $callback($outputBuilder, $currentChunk, $index);

            $message = $outputBuilder->build();

            $isLast = ($index === $totalChunks - 1);
            $keyboard = $isLast ? $keyboard : [];

            $bot->sendMessage($message, $vkId, $keyboard);
        }
    }
}
