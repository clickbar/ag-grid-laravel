<?php

namespace Clickbar\AgGrid\Data;

class AgGridSetValue
{
    public function __construct(
        public readonly string|int $value,
        public readonly ?string $label = null,
    ) {}

    public static function fromValue(string|int|null $value): ?self
    {
        return $value === null ? null : new self($value);
    }
}
