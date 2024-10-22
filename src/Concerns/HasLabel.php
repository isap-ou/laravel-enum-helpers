<?php

declare(strict_types=1);

namespace IsapOu\EnumHelpers\Concerns;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

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

        return trans(vsprintf('%s%s%s.%s', [$namespace, $prefix, \get_class($this), $this->name]));
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
