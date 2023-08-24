<?php

namespace Clickbar\AgGrid\Enums;

enum AgGridTextFilterType: string
{
    case Equals = 'equals';
    case NotEqual = 'notEqual';
    case Contains = 'contains';
    case NotContains = 'notContains';
    case StartsWith = 'startsWith';
    case EndsWith = 'endsWith';
    case Blank = 'blank';
    case NotBlank = 'notBlank';
}
