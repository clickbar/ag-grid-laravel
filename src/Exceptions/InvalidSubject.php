<?php

namespace Clickbar\AgGrid\Exceptions;

use InvalidArgumentException;

class InvalidSubject extends InvalidArgumentException
{
    public static function make($subject): InvalidSubject
    {
        return new InvalidSubject(
            sprintf(
                'Subject %s is invalid.',
                is_object($subject)
                    ? sprintf('class `%s`', get_class($subject))
                    : sprintf('type `%s`', gettype($subject))
            )
        );
    }
}
