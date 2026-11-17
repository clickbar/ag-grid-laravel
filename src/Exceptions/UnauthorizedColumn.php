<?php

namespace Clickbar\AgGrid\Exceptions;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UnauthorizedColumn extends AccessDeniedHttpException
{
    public static function make(string $column): self
    {
        return new self(
            sprintf(
                'Column %s is not available or cannot be accessed',
                $column
            )
        );
    }
}
