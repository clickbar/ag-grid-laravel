<?php

namespace Clickbar\AgGrid;

use Clickbar\AgGrid\Contracts\AgGridCustomFilterable;
use Clickbar\AgGrid\Enums\AgGridDateFilterType;
use Clickbar\AgGrid\Enums\AgGridExportFormat;
use Clickbar\AgGrid\Enums\AgGridFilterType;
use Clickbar\AgGrid\Enums\AgGridNumberFilterType;
use Clickbar\AgGrid\Enums\AgGridRowModel;
use Clickbar\AgGrid\Enums\AgGridTextFilterType;
use Clickbar\AgGrid\Exceptions\InvalidSetValueOperation;
use Clickbar\AgGrid\Exceptions\UnauthorizedSetFilterColumn;
use Clickbar\AgGrid\Requests\AgGridGetRowsRequest;
use Clickbar\AgGrid\Requests\AgGridSetValuesRequest;
use Clickbar\AgGrid\Support\Column;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\ForwardsCalls;
use Maatwebsite\Excel\Facades\Excel;

/**
 * @mixin EloquentBuilder
 */
class AgGridQueryBuilder implements Responsable
{
    use ForwardsCalls;

    protected array $params;

    protected EloquentBuilder|Relation $subject;

    /** @var class-string<JsonResource> | null */
    protected ?string $resourceClass = null;

    /**
     * @param  EloquentBuilder|Relation|Model|class-string<Model>  $subject
     */
    public function __construct(array $params, EloquentBuilder|Relation|Model|string $subject)
    {
        if (is_a($subject, Model::class, true)) {
            $subject = $subject::query();
        }

        $this->params = $params;
        $this->subject = $subject;

        $model = $subject->getModel();
        if ($model instanceof AgGridCustomFilterable) {
            $model->applyAgGridCustomFilters($this->subject, $this->params['customFilters'] ?? []);
        }

        $this->addFiltersToQuery();
        $this->addToggledFilterToQuery();
        $this->addSortsToQuery();
        $this->addLimitAndOffsetToQuery();
    }

    /**
     * Returns a new AgGridQueryBuilder for an AgGridGetRowsRequest.
     *
     * @param  EloquentBuilder|Relation|Model|class-string<Model>  $subject
     */
    public static function forRequest(AgGridGetRowsRequest $request, EloquentBuilder|Relation|Model|string $subject): AgGridQueryBuilder
    {
        return new AgGridQueryBuilder($request->validated(), $subject);
    }

    /**
     * Returns a new AgGridQueryBuilder for an AgGridGetRowsRequest.
     *
     * @param  EloquentBuilder|Relation|Model|class-string<Model>  $subject
     */
    public static function forSetValuesRequest(AgGridSetValuesRequest $request, EloquentBuilder|Relation|Model|string $subject): AgGridQueryBuilder
    {
        return new AgGridQueryBuilder($request->validated(), $subject);
    }

    /**
     * Returns a new AgGridQueryBuilder for a selection.
     *
     * @param  EloquentBuilder|Relation|Model|class-string<Model>  $subject
     */
    public static function forSelection(array $selection, EloquentBuilder|Relation|Model|string $subject): AgGridQueryBuilder
    {
        return new AgGridQueryBuilder($selection, $subject);
    }

    public function getQuery(): QueryBuilder
    {
        if ($this->subject instanceof EloquentBuilder) {
            return $this->subject->getQuery();
        }

        return $this->subject->getBaseQuery();
    }

    public function getSubject(): Relation|EloquentBuilder
    {
        return $this->subject;
    }

    /**
     * @param  class-string<JsonResource>  $resourceClass
     */
    public function resource(string $resourceClass): self
    {
        $this->resourceClass = $resourceClass;

        return $this;
    }

    public function toSetValues(array $allowedColumns = []): Collection
    {
        $colId = Arr::get($this->params, 'column');
        if (empty($colId)) {
            throw InvalidSetValueOperation::make();
        }

        if (collect($allowedColumns)->first() !== '*' && ! in_array($colId, $allowedColumns)) {
            throw UnauthorizedSetFilterColumn::make($colId);
        }

        $column = Column::fromColId($this->subject, $colId);

        if ($column->hasRelations()) {

            $dottedRelation = $column->getDottedRelation();

            return $this->subject->with($dottedRelation)
                ->get()
                ->map(fn (Model $model) => Arr::get($this->traverse($model, $dottedRelation)->toArray(), $column->getName()))
                ->unique()
                ->sort()
                ->values();
        }

        $columnName = $column->isNestedJsonColumn() ? $column->getNameAsJsonPath() : $column->getName();

        // When getting from json, postgres uses ?column? as columns name instead the 'A->B'
        $pluckColumn = $column->isNestedJsonColumn() ? '?column?' : $column->getName();

        $values = $this->subject
            ->select($columnName)
            ->distinct()
            ->orderBy($columnName)
            ->pluck($pluckColumn);

        if ($column->isJsonColumn() && ! $column->isNestedJsonColumn()) {
            // --> We need to flat the data, because we have a flat json array
            return $values->flatten(1)->unique()->sort()->values();
        }

        return $values;
    }

    public function __call($name, $arguments)
    {
        $result = $this->forwardCallTo($this->subject, $name, $arguments);

        /*
         * If the forwarded method call is part of a chain we can return $this
         * instead of the actual $result to keep the chain going.
         */
        if ($result === $this->subject) {
            return $this;
        }

        return $result;
    }

    public function toResponse($request): mixed
    {
        $exportFormat = $this->params['exportFormat'] ?? null;
        if ($exportFormat !== null) {
            // this is an export
            $writerType = match (AgGridExportFormat::from($exportFormat)) {
                AgGridExportFormat::Excel => \Maatwebsite\Excel\Excel::XLSX,
                AgGridExportFormat::Csv => \Maatwebsite\Excel\Excel::CSV,
                AgGridExportFormat::Tsv => \Maatwebsite\Excel\Excel::TSV,
            };

            return Excel::download(
                new AgGridExport($this->subject, $this->params['exportColumns'] ?? null),
                'export.'.strtolower($writerType),
                $writerType
            );
        }

        $clone = $this->clone();
        tap($clone->getQuery(), function (QueryBuilder $query) {
            $query->limit = $query->offset = $query->orders = null;
            $query->cleanBindings(['order']);
        });
        $total = $clone->count();

        $data = $this->get();

        // wrap in a resource
        if ($this->resourceClass !== null) {
            /** @var class-string<JsonResource> $resourceClass */
            $resourceClass = $this->resourceClass;
            if (is_a($resourceClass, ResourceCollection::class, true)) {
                // the resource is already a collection
                $data = new $resourceClass($data);
            } else {
                // wrap in an anonymous collection
                $data = $resourceClass::collection($data);
            }
        }

        return response()->json([
            'total' => $total,
            'data' => $data,
        ]);
    }

    protected function addToggledFilterToQuery(): void
    {
        if (! isset($this->params['rowModel'])) {
            return;
        }
        match (AgGridRowModel::from($this->params['rowModel'])) {
            AgGridRowModel::ServerSide => $this->addServerSideToggledFilterToQuery(),
            AgGridRowModel::ClientSide => $this->addClientSideToggledFilterToQuery(),
        };
    }

    protected function addClientSideToggledFilterToQuery(): void
    {
        $this->subject->whereIn($this->subject->getModel()->getKeyName(), $this->params['toggledNodes']);
    }

    protected function addServerSideToggledFilterToQuery(): void
    {
        if ($this->params['selectAll']) {
            // the toggled nodes are deselected
            $this->subject->whereNotIn($this->subject->getModel()->getKeyName(), $this->params['toggledNodes']);
        } else {
            // the toggled nodes are selected
            $this->subject->whereIn($this->subject->getModel()->getKeyName(), $this->params['toggledNodes'])->get();
        }
    }

    protected function addFiltersToQuery(): void
    {
        if (! isset($this->params['filterModel'])) {
            return;
        }

        $filters = collect($this->params['filterModel']);

        // Check if we are in set values mode and exclude the filter for the given set value column
        $colId = Arr::get($this->params, 'column');
        if ($colId) {
            $filters = $filters->filter(fn ($value, $key) => $key !== $colId);
        }

        foreach ($filters as $colId => $filter) {

            $column = Column::fromColId($this->subject, $colId);

            if ($column->hasRelations()) {
                $this->subject->whereHas($column->getDottedRelation(), function (EloquentBuilder $builder) use ($column, $filter) {
                    $this->addFilterToQuery($builder, $column, $filter);
                });
            } else {
                $this->addFilterToQuery($this->subject, $column, $filter);
            }
        }
    }

    protected function addSortsToQuery(): void
    {
        if (! isset($this->params['sortModel'])) {
            return;
        }

        $sorts = collect($this->params['sortModel']);

        if ($sorts->isNotEmpty()) {
            // clear all existing sorts
            $this->subject->reorder();
        }

        foreach ($sorts as $sort) {
            $column = Column::fromColId($this->subject, $sort['colId']);
            $this->subject->orderBy($column->getNameAsJsonPath(), $sort['sort']);
        }

        // we need an additional sort condition so that the order is stable in all cases
        $this->subject->orderBy($this->subject->getModel()->getKeyName());
    }

    protected function addLimitAndOffsetToQuery(): void
    {
        $startRow = $this->params['startRow'] ?? null;
        $endRow = $this->params['endRow'] ?? null;

        if ($startRow === null || $endRow === null) {
            return;
        }

        $this->subject->offset($startRow)->limit($endRow - $startRow);
    }

    protected function addFilterToQuery(EloquentBuilder|Relation $subject, Column $column, array $filter): void
    {
        $filterType = AgGridFilterType::from($filter['filterType']);
        match ($filterType) {
            AgGridFilterType::Set => $this->addSetFilterToQuery($subject, $column, $filter),
            AgGridFilterType::Text => $this->addTextFilterToQuery($subject, $column, $filter),
            AgGridFilterType::Number => $this->addNumberFilterToQuery($subject, $column, $filter),
            AgGridFilterType::Date => $this->addDateFilterToQuery($subject, $column, $filter),
        };
    }

    protected function addSetFilterToQuery(EloquentBuilder|Relation $subject, Column $column, array $filter): void
    {
        $isJsonColumn = $column->isJsonColumn();
        $columnName = $column->getNameAsJsonPath();
        $values = $filter['values'];
        $all = $filter['all'] ?? false;
        $filteredValues = array_filter($values, fn ($value) => $value !== null);

        $subject->where(function (EloquentBuilder $query) use ($column, $all, $columnName, $values, $filteredValues, $isJsonColumn) {
            if (count($filteredValues) !== count($values)) {
                // there was a null in there
                $query->whereNull($columnName);
            }

            if ($isJsonColumn) {
                // TODO: this does not work at the moment because laravel has no support for the ?& and ?| operators
                // TODO: find a workaround!
                $query->orWhere(
                    $column->getNameAsJsonAccessor(),
                    $all ? '?&' : '?|', '{'.implode(',', $filteredValues).'}',
                );
            } else {
                $query->orWhereIn($columnName, $filteredValues);
            }
        });
    }

    protected function addTextFilterToQuery(EloquentBuilder|Relation $subject, Column $column, array $filter): void
    {
        $columnName = $column->getNameAsJsonPath();
        $value = $filter['filter'] ?? null;
        $type = AgGridTextFilterType::from($filter['type']);

        match ($type) {
            AgGridTextFilterType::Equals => $subject->where($columnName, '=', $value),
            AgGridTextFilterType::NotEqual => $subject->where($columnName, '!=', $value),
            AgGridTextFilterType::Contains => $subject->where($columnName, 'ilike', '%'.$value.'%'),
            AgGridTextFilterType::NotContains => $subject->where($columnName, 'not ilike', '%'.$value.'%'),
            AgGridTextFilterType::StartsWith => $subject->where($columnName, 'ilike', $value.'%'),
            AgGridTextFilterType::EndsWith => $subject->where($columnName, 'ilike', '%'.$value),
            AgGridTextFilterType::Blank => $subject->whereNull($columnName),
            AgGridTextFilterType::NotBlank => $subject->whereNotNull($columnName),
        };
    }

    protected function addNumberFilterToQuery(EloquentBuilder|Relation $subject, Column $column, array $filter): void
    {
        $columnName = $column->getNameAsJsonPath();
        $value = $filter['filter'];
        $type = AgGridNumberFilterType::from($filter['type']);

        match ($type) {
            AgGridNumberFilterType::Equals => $subject->where($columnName, '=', $value),
            AgGridNumberFilterType::NotEqual => $subject->where($columnName, '!=', $value),
            AgGridNumberFilterType::GreaterThan => $subject->where($columnName, '>', $value),
            AgGridNumberFilterType::GreaterThanOrEqual => $subject->where($columnName, '>=', $value),
            AgGridNumberFilterType::LessThan => $subject->where($columnName, '<', $value),
            AgGridNumberFilterType::LessThanOrEqual => $subject->where($columnName, '<=', $value),
            AgGridNumberFilterType::InRange => $subject->where($columnName, '>=', $value)->where($columnName, '<=', $filter['filterTo']),
            AgGridNumberFilterType::Blank => $subject->whereNull($columnName),
            AgGridNumberFilterType::NotBlank => $subject->whereNotNull($columnName),
        };
    }

    protected function addDateFilterToQuery(EloquentBuilder|Relation $subject, Column $column, array $filter): void
    {
        $columnName = $column->getNameAsJsonPath();
        $dateFrom = isset($filter['dateFrom']) ? new \DateTime($filter['dateFrom']) : null;
        $dateTo = isset($filter['dateTo']) ? new \DateTime($filter['dateTo']) : null;

        match (AgGridDateFilterType::from($filter['type'])) {
            AgGridDateFilterType::Equals => $subject->whereDate($columnName, '=', $dateFrom),
            AgGridDateFilterType::NotEqual => $subject->whereDate($columnName, '!=', $dateFrom),
            AgGridDateFilterType::GreaterThan => $subject->whereDate($columnName, '>=', $dateFrom),
            AgGridDateFilterType::LessThan => $subject->whereDate($columnName, '<=', $dateFrom),
            AgGridDateFilterType::InRange => $subject->whereDate($columnName, '>=', $dateFrom)->whereDate($columnName, '<=', $dateTo),
            AgGridDateFilterType::Blank => $subject->whereNull($columnName),
            AgGridDateFilterType::NotBlank => $subject->whereNotNull($columnName),
        };
    }

    protected function traverse($model, $key): Model
    {
        if (is_array($model)) {
            return Arr::get($model, $key);
        }

        if (is_null($key)) {
            return $model;
        }

        if (isset($model[$key])) {
            return $model[$key];
        }

        foreach (explode('.', $key) as $segment) {
            $model = $model->$segment;
        }

        return $model;
    }
}
