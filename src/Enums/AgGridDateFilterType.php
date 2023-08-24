<?php

namespace Clickbar\AgGrid\Enums;

enum AgGridDateFilterType: string
{
    case Equals = 'equals';
    case NotEqual = 'notEqual';
    case GreaterThan = 'greaterThan';
    case LessThan = 'lessThan';
    case InRange = 'inRange';
    case Blank = 'blank';
    case NotBlank = 'notBlank';
}
