<?php

namespace App\Vk;

use Illuminate\Http\Client\ConnectionException;
use Random\RandomException;

class Bot
{
    public function __construct(
        private readonly VkApiClient $vkApiClient,
    )
    {
    }

    public function sendMessage(string $message, int $peerId, array $keyboard = [], string $image = ''): void
    {
        $this->vkApiClient->sendMessage(
            $message,
            $peerId,
            $keyboard,
            $image,
        );
    }

    public function uploadImage(string $localPath, int $peerId): ?string
    {
        return $this->vkApiClient->uploadImage(
            $localPath,
            $peerId
        );
    }
}
