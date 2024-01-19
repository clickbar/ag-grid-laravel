<?php

namespace Clickbar\AgGrid\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class UnauthorizedSetFilterColumn extends UnauthorizedHttpException
{
    public static function make(string $column): self
    {
        return new self(
            sprintf(
                'Set value for column %s is not available or cannot be accessed',
                $column
            )
        );
    }
}
