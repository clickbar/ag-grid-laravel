<?php

namespace Clickbar\AgGrid\Exceptions;

use InvalidArgumentException;

class InvalidSetValueOperation extends InvalidArgumentException
{
    public static function make(): self
    {
        return new self("toSetValues can only be called from AgGridSetValueRequest or when params contains a 'column' key (with an actual value)");
    }
}
