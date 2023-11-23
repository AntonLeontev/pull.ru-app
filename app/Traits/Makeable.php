<?php

namespace App\Traits;

trait Makeable
{
    public static function make(...$args): static
    {
        return new static(...$args);
    }
}
