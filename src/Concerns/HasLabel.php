<?php

declare(strict_types=1);

namespace IsapOu\EnumHelpers\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use ReflectionClass;

use function rtrim;
use function trans;

trait HasLabel
{
    public function getLabel(?string $prefix = null, ?string $namespace = null): string
    {
        if (empty($prefix)) {
            $prefix = $this->getPrefix();
        }
        if (empty($namespace)) {
            $namespace = $this->getNamespace();
        }

        if (! empty($prefix)) {
            $prefix = rtrim($prefix, '.') . '.';
        }
        if (! empty($namespace) && ! Str::endsWith($namespace, '::')) {
            $namespace .= '::';
        }

        $reflect = new ReflectionClass($this);

        return trans(vsprintf('%s%s%s.%s', [$namespace, $prefix, $reflect->getShortName(), $this->name]));
    }

    public function getLabels(?string $prefix = null, ?string $namespace = null): Collection
    {
        return Collection::make(static::cases())->mapWithKeys(fn ($enum) => [$enum->name => $enum->getLabel($prefix, $namespace)]);
    }

    protected function getPrefix(): ?string
    {
        return Config::get('enum-helpers.label.prefix');
    }

    protected function getNamespace(): ?string
    {
        return Config::get('enum-helpers.label.namespace');
    }
}
