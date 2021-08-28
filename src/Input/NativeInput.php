<?php

declare(strict_types=1);

namespace Astaroth\CallBack\Input;


final class NativeInput extends AbstractInput
{
    public const INPUT = "php://input";

    public function getData()
    {
        $raw = file_get_contents(self::INPUT);
        return @json_decode($raw, false);
    }
}