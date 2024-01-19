<?php

namespace App\Http\Controllers;

use App\Models\FlamingoAgGridView;
use Clickbar\AgGrid\AgGridQueryBuilder;
use Clickbar\AgGrid\Requests\AgGridGetRowsRequest;
use Clickbar\AgGrid\Requests\AgGridSetValuesRequest;

class FlamingoViewGridController extends Controller
{
    public function rows(AgGridGetRowsRequest $request): AgGridQueryBuilder
    {
        return AgGridQueryBuilder::forRequest($request, FlamingoAgGridView::class);
    }

    public function setValues(AgGridSetValuesRequest $request)
    {
        return AgGridQueryBuilder::forSetValuesRequest($request, FlamingoAgGridView::class)
            ->toSetValues(['*']);
    }
}
