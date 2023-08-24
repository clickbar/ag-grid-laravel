<?php

namespace Clickbar\AgGrid\Enums;

enum AgGridNumberFilterType: string
{
    case Equals = 'equals';
    case NotEqual = 'notEqual';
    case GreaterThan = 'greaterThan';
    case GreaterThanOrEqual = 'greaterThanOrEqual';
    case LessThan = 'lessThan';
    case LessThanOrEqual = 'lessThanOrEqual';
    case InRange = 'inRange';
    case Blank = 'blank';
    case NotBlank = 'notBlank';
}
