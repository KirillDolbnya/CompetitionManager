<?php

namespace App\Vk\Builders;

use Illuminate\Support\Collection;

class OutputBuilder
{
    private array $lines = [];

    public function build(): string
    {
        $result = implode("\n", $this->lines);

        $this->lines = [];

        return $result;
    }

    public function addHead(string $title): self
    {
        $this->lines[] = $title;

        return $this;
    }

    public function addList(string $title, Collection $collection, callable $callback): self
    {
        if ($collection->isEmpty()) {
            return $this;
        }

        $this->lines[] = "{$title}";

        foreach ($collection as $item) {
            $formatedText = $callback($item);

            $this->lines[] = "• {$formatedText}";
        }

        return $this;
    }

    public function addIdent(): self
    {
        $this->lines[] = '';

        return $this;
    }
}
