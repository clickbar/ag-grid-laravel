<?php

namespace Clickbar\AgGrid;

use Clickbar\AgGrid\Contracts\AgGridCustomFilterable;
use Clickbar\AgGrid\Enums\AgGridDateFilterType;
use Clickbar\AgGrid\Enums\AgGridExportFormat;
use Clickbar\AgGrid\Enums\AgGridFilterType;
use Clickbar\AgGrid\Enums\AgGridNumberFilterType;
use Clickbar\AgGrid\Enums\AgGridRowModel;
use Clickbar\AgGrid\Enums\AgGridTextFilterType;
use Clickbar\AgGrid\Requests\AgGridGetRowsRequest;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Str;
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
            /** @phpstan-ignore-next-line */
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
                $data = new $resourceClass($this->get());
            } else {
                // wrap in an anonymous collection
                $data = $resourceClass::collection($this->get());
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

        foreach ($filters as $column => $filter) {

            [$relation, $column] = $this->getRelation($column);

            if ($relation !== null) {
                $this->subject->whereHas($relation, function (EloquentBuilder $builder) use ($column, $filter) {
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
            $this->subject->orderBy($this->toJsonPath($sort['colId']), $sort['sort']);
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

    protected function addFilterToQuery(EloquentBuilder|Relation $subject, string $column, array $filter): void
    {
        $filterType = AgGridFilterType::from($filter['filterType']);
        match ($filterType) {
            AgGridFilterType::Set => $this->addSetFilterToQuery($subject, $column, $filter),
            AgGridFilterType::Text => $this->addTextFilterToQuery($subject, $column, $filter),
            AgGridFilterType::Number => $this->addNumberFilterToQuery($subject, $column, $filter),
            AgGridFilterType::Date => $this->addDateFilterToQuery($subject, $column, $filter),
        };
    }

    protected function addSetFilterToQuery(EloquentBuilder|Relation $subject, string $column, array $filter): void
    {
        $isJsonColumn = $this->isJsonColumn($column);
        $column = $this->toJsonPath($column);
        $values = $filter['values'];
        $all = $filter['all'] ?? false;
        $filteredValues = array_filter($values, fn ($value) => $value !== null);

        $subject->where(function (EloquentBuilder $query) use ($all, $column, $values, $filteredValues, $isJsonColumn) {
            if (count($filteredValues) !== count($values)) {
                // there was a null in there
                $query->whereNull($column);
            }

            if ($isJsonColumn) {
                // TODO: this does not work at the moment because laravel has no support for the ?& and ?| operators
                // TODO: find a workaround!
                $query->orWhere(
                    $column,
                    $all ? '?&' : '?|', '{'.implode(',', $filteredValues).'}',
                );
            } else {
                $query->orWhereIn($column, $filteredValues);
            }
        });
    }

    protected function addTextFilterToQuery(EloquentBuilder|Relation $subject, string $column, array $filter): void
    {
        $column = $this->toJsonPath($column);
        $value = $filter['filter'] ?? null;
        $type = AgGridTextFilterType::from($filter['type']);

        match ($type) {
            AgGridTextFilterType::Equals => $subject->where($column, '=', $value),
            AgGridTextFilterType::NotEqual => $subject->where($column, '!=', $value),
            AgGridTextFilterType::Contains => $subject->where($column, 'ilike', '%'.$value.'%'),
            AgGridTextFilterType::NotContains => $subject->where($column, 'not ilike', '%'.$value.'%'),
            AgGridTextFilterType::StartsWith => $subject->where($column, 'ilike', $value.'%'),
            AgGridTextFilterType::EndsWith => $subject->where($column, 'ilike', '%'.$value),
            AgGridTextFilterType::Blank => $subject->whereNull($column),
            AgGridTextFilterType::NotBlank => $subject->whereNotNull($column),
        };
    }

    protected function addNumberFilterToQuery(EloquentBuilder|Relation $subject, string $column, array $filter): void
    {
        $column = $this->toJsonPath($column);
        $value = $filter['filter'];
        $type = AgGridNumberFilterType::from($filter['type']);

        match ($type) {
            AgGridNumberFilterType::Equals => $subject->where($column, '=', $value),
            AgGridNumberFilterType::NotEqual => $subject->where($column, '!=', $value),
            AgGridNumberFilterType::GreaterThan => $subject->where($column, '>', $value),
            AgGridNumberFilterType::GreaterThanOrEqual => $subject->where($column, '>=', $value),
            AgGridNumberFilterType::LessThan => $subject->where($column, '<', $value),
            AgGridNumberFilterType::LessThanOrEqual => $subject->where($column, '<=', $value),
            AgGridNumberFilterType::InRange => $subject->where($column, '>=', $value)->where($column, '<=', $filter['filterTo']),
            AgGridNumberFilterType::Blank => $subject->whereNull($column),
            AgGridNumberFilterType::NotBlank => $subject->whereNotNull($column),
        };
    }

    protected function addDateFilterToQuery(EloquentBuilder|Relation $subject, string $column, array $filter): void
    {
        $column = $this->toJsonPath($column);
        $dateFrom = isset($filter['dateFrom']) ? new \DateTime($filter['dateFrom']) : null;
        $dateTo = isset($filter['dateTo']) ? new \DateTime($filter['dateTo']) : null;

        match (AgGridDateFilterType::from($filter['type'])) {
            AgGridDateFilterType::Equals => $subject->whereDate($column, '=', $dateFrom),
            AgGridDateFilterType::NotEqual => $subject->whereDate($column, '!=', $dateFrom),
            AgGridDateFilterType::GreaterThan => $subject->whereDate($column, '>=', $dateFrom),
            AgGridDateFilterType::LessThan => $subject->whereDate($column, '<=', $dateFrom),
            AgGridDateFilterType::InRange => $subject->whereDate($column, '>=', $dateFrom)->whereDate($column, '<=', $dateTo),
            AgGridDateFilterType::Blank => $subject->whereNull($column),
            AgGridDateFilterType::NotBlank => $subject->whereNotNull($column),
        };
    }

    protected function getRelation(string $column): array
    {
        $pos = strpos($column, '.');
        if ($pos === false) {
            return [null, $column];
        }
        // guess the name of the relation
        $relationName = Str::camel(substr($column, 0, $pos));
        if ($this->subject->getModel()->isRelation($relationName)) {
            return [$relationName, substr($column, $pos + 1)];
        }

        return [null, $column];
    }

    protected function isJsonColumn(string $column): bool
    {
        return str_contains($column, '.') || $this->subject->getModel()->hasCast($column, [
            'array',
            'json',
            'object',
            'collection',
            'encrypted:array',
            'encrypted:collection',
            'encrypted:json',
            'encrypted:object',
        ]);
    }

    protected function toJsonPath(string $key): string
    {
        return str_replace('.', '->', $key);
    }
}
