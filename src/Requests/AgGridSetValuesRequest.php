<?php

namespace Clickbar\AgGrid\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgGridSetValuesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'column' => ['required', 'string'],
            'filterModel' => ['sometimes', 'array'],
        ];
    }
}
