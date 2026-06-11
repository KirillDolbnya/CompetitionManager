<?php

namespace App\Services;

use Illuminate\Http\Request;

class VkEventService
{
    private ?int $vkPeerId = null;
    private ?int $vkChatId = null;
    private string $eventType;
    private ?string $message = null;
    private array $payload = [];

    public function __construct(
        private readonly Request $request,
    )
    {
        $data = $request->all();

        $this->eventType = $data['type'] ?? '';

        if ($this->eventType === 'message_new') {
            $messageData = $data['object']['message'] ?? [];

            $this->vkPeerId = $messageData['from_id'] ?? null;
            $this->vkChatId = $messageData['peer_id'] ?? null;
            $this->message = $messageData['text'] ?? null;

            if (!empty($messageData['payload'])) {
                $this->payload = json_decode($messageData['payload'], true) ?? [];
            }
        }
    }

    public static function fromRequest(Request $request): self
    {
        return new self($request);
    }

    public function getPeerId(): ?int
    {
        return $this->vkPeerId;
    }

    public function getVkChatId(): ?int
    {
        return $this->vkChatId;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }
}
