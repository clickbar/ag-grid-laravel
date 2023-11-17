<?php

namespace App\Http\Controllers;

use App\Http\Resources\FlamingoResource;
use App\Models\Flamingo;
use Clickbar\AgGrid\AgGridQueryBuilder;
use Clickbar\AgGrid\Requests\AgGridGetRowsRequest;

class FlamingoGridController extends Controller
{
    public function rows(AgGridGetRowsRequest $request): AgGridQueryBuilder
    {
        $query = Flamingo::query()
            ->with(['keeper'])
            ->orderByDesc('id');

        return AgGridQueryBuilder::forRequest($request, $query)
            ->resource(FlamingoResource::class);
    }
}
