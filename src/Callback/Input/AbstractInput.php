<?php

declare(strict_types=1);

namespace Astaroth\Callback\Input;


abstract class AbstractInput
{
    public const INPUT = null;

    /**
     * Get data from input
     * @return ?object
     */
    abstract public function getData(): ?object;
}