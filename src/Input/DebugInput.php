<?php

declare(strict_types=1);

namespace Astaroth\CallBack\Input;

class DebugInput extends AbstractInput
{
    public const INPUT = "debug";
    private object $data;

    public function getData(): ?object
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }
}