<?php

namespace Clickbar\AgGrid\Rules;

use Clickbar\AgGrid\Enums\AgGridRowModel;
use Illuminate\Validation\Rules\Enum;

class AgGridSelection extends NestedRule
{
    public function rules(string $attribute, array $data): array
    {
        return [
            'rowModel' => ['required', new Enum(AgGridRowModel::class)],
            'toggledNodes' => ['present', 'array'],
            'filterModel' => ['sometimes', 'array'],
            'selectAll' => ['sometimes', 'boolean'],
            'customFilters' => ['sometimes', 'array'],
        ];
    }

    public function attributes(): array
    {
        return [];
    }
}
