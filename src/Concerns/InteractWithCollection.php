<?php

namespace IsapOu\EnumHelpers\Concerns;

use Illuminate\Support\Collection;

trait InteractWithCollection
{
    public static function collection(): Collection
    {
        return Collection::make(static::cases());
    }

    public static function values(): Collection
    {
        return static::keyValuePairs()->values();
    }

    public static function keys(): Collection
    {
        return static::keyValuePairs()->keys();
    }

    public static function keyValuePairs(): Collection
    {
        return static::collection()->mapWithKeys(fn ($enum) => [$enum->name => $enum->value]);
    }
}