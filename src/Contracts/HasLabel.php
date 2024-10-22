<?php

declare(strict_types=1);

namespace IsapOu\EnumHelpers\Contracts;

interface HasLabel
{
    public function getLabel(): ?string;
}
