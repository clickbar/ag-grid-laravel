<?php

namespace Clickbar\AgGrid\Tests\TestClasses\Enums;

use Clickbar\AgGrid\Data\AgGridSetValue;

enum FlamingoSpecies: string
{
    case Greater = 'greater';
    case Lesser = 'lesser';
    case Chilean = 'chilean';
    case James = 'james';
    case Andean = 'andean';
    case American = 'american';

    /**
     * @return AgGridSetValue[]
     */
    public static function setValues(): array
    {
        return array_map(fn(FlamingoSpecies $enum) => AgGridSetValue::fromValue($enum->value), self::cases());
    }
}
