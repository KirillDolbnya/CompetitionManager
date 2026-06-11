<?php

namespace App\Vk\Builders;

class KeyboardBuilder
{
    private array $buttons = [];

    private int $currentRow = 0;

    public function build(bool $inline = false, bool $oneTime = false): array
    {
        $result =  [
            'inline' => $inline,
            'one_time' => $oneTime,
            'buttons' => $this->buttons,
        ];

        $this->buttons = [];
        $this->currentRow = 0;

        return $result;
    }

    public function textButton(string $text, string $command, string $color, array $customParameters = []): self
    {
        $payloadData = array_merge(['command' => $command], $customParameters);

        $this->buttons[$this->currentRow][] = [
            'action' => [
                'type' => 'text',
                'label' => $text,
                'payload' => $payloadData,
            ],
            'color' => $color,
        ];

        return $this;
    }

    public function row(): self
    {
        if (!empty($this->buttons[$this->currentRow])) {
            $this->currentRow++;
        }

        return $this;
    }
}
