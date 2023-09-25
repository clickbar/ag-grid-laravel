<?php

namespace Clickbar\AgGrid\Requests;

use Clickbar\AgGrid\Enums\AgGridRowModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class AgGridGetRowsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'startRow' => ['integer'],
            'endRow' => ['integer'],
            'filterModel' => ['sometimes', 'array'],
            'sortModel' => ['sometimes', 'array'],
            'exportFormat' => ['string', 'in:excel,csv'],
            'exportColumns' => ['array'],
            'rowModel' => ['sometimes', new Enum(AgGridRowModel::class)],
            'selectAll' => ['sometimes', 'boolean'],
            'toggledNodes' => ['sometimes', 'array'],
            'customFilters' => ['sometimes', 'array'],
            // Row Grouping
            'rowGroupCols' => ['sometimes', 'array'],
            'groupKeys' => ['sometimes', 'array'],
        ];
    }
}
