<?php

namespace Clickbar\AgGrid\Exceptions;

class ViewManipulationNotAllowedException extends \Exception
{
    /**
     * @param  string  $modelClassName
     * {@inheritDoc}
     */
    public function __construct(string $functionName, string $modelClassName, int $code = 0, \Throwable $previous = null)
    {
        $message = sprintf('Calling [%s] method on the [%s] AG Grid model representing a database view is not allowed.', $functionName, $modelClassName);
        parent::__construct($message, $code, $previous);
    }
}
