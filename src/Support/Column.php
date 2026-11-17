<?php

namespace Clickbar\AgGrid\Support;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

class Column
{
    protected bool $isJsonColumn;

    protected bool $isNestedJsonColumn;

    public function __construct(
        protected Model $baseModel,
        /** @var RelationMetadata[] */
        protected array $relations,
        protected string $colId,
        protected string $name,
    ) {
        // check for json columns

        $hasPathAccessor = str_contains($this->name, '.');
        if ($hasPathAccessor) {
            $this->isJsonColumn = true;
        } else {
            $this->isJsonColumn = $this->getModel()->hasCast($this->getAttribute(), [
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

        $this->isNestedJsonColumn = $this->isJsonColumn && $hasPathAccessor;
    }

    public static function fromColId(EloquentBuilder|Relation $subject, string $colId): self
    {
        $parts = Str::of($colId)->explode('.');

        if ($parts->count() === 1) {
            // --> No nested information
            return new self($subject->getModel(), [], $colId, $colId);
        }

        $relations = [];
        $name = $colId;
        $currentModel = $subject->getModel();

        foreach ($parts as $index => $part) {
            $relationName = Str::camel($part);
            $modelRelations = self::getRelations($currentModel::class);

            if ($modelRelations->contains($relationName)) {
                $relation = $currentModel->$relationName();
                $currentModel = $relation->getModel();

                $relations[] = new RelationMetadata($relationName, $currentModel);
            } else {
                // --> End of relation (further dots must be json nesting)
                $remaining = $parts->skip($index + 1)->implode('.');
                if (! empty($remaining)) {
                    $remaining = '.'.$remaining;
                }
                $name = $part.$remaining;
                break;
            }
        }

        return new self($subject->getModel(), $relations, $colId, $name);
    }

    protected static function getRelations(string $modelClass): Collection
    {
        return collect((new ReflectionClass($modelClass))->getMethods(ReflectionMethod::IS_PUBLIC))
            ->filter(function (ReflectionMethod $reflectionMethod) {
                $returnType = (string) $reflectionMethod->getReturnType();

                return $returnType != null && is_subclass_of($returnType, Relation::class);
            })
            ->map(fn (ReflectionMethod $reflectionMethod) => $reflectionMethod->getName());
    }

    public function hasRelations(): bool
    {
        return ! empty($this->relations);
    }

    public function getDottedRelation(): string
    {
        return collect($this->relations)
            ->implode('name', '.');
    }

    /**
     * Returns the model of the innermost relation, or the base model when the column has no relations.
     */
    public function getModel(): Model
    {
        return collect($this->relations)->last()->model ?? $this->baseModel;
    }

    /**
     * Returns the attribute of the innermost model the column reads, without any json path.
     */
    public function getAttribute(): string
    {
        return Str::before($this->name, '.');
    }

    /**
     * Returns true if the column or any relation on its path is hidden from the serialized model,
     * through $hidden or a $visible list that does not name it.
     */
    public function isHidden(): bool
    {
        $model = $this->baseModel;

        foreach ($this->relations as $relation) {
            if (self::isHiddenOn($model, $relation->name) || self::isHiddenOn($model, Str::snake($relation->name))) {
                return true;
            }

            $model = $relation->model;
        }

        return self::isHiddenOn($model, $this->getAttribute());
    }

    protected static function isHiddenOn(Model $model, string $key): bool
    {
        $visible = $model->getVisible();

        return in_array($key, $model->getHidden(), true) || ($visible !== [] && ! in_array($key, $visible, true));
    }

    /**
     * Returns the full colId, including all parent relations.
     */
    public function getColId(): string
    {
        return $this->colId;
    }

    /**
     * Returns the column name of the innermost relation, including possible json accessors.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns true if the column refers to a json column.
     */
    public function isJsonColumn(): bool
    {
        return $this->isJsonColumn;
    }

    /**
     * Returns true if the column refers to a field within a json column.
     */
    public function isNestedJsonColumn(): bool
    {
        return $this->isNestedJsonColumn;
    }

    /**
     * Returns the name of the column with any json path segments (.) replaced by an arrow (->)
     */
    public function getNameAsJsonPath(): string
    {
        return str_replace('.', '->', $this->name);
    }

    /**
     * Returns the name of the column with any json path segments replaces by a call to jsonb_extract_path
     */
    public function getNameAsJsonAccessor(): Expression|string
    {
        if (! $this->isNestedJsonColumn) {
            return $this->name;
        }

        // The colId comes from the client, so the column and every path segment must be quoted.
        $grammar = $this->getModel()->getConnection()->getQueryGrammar();
        $column = $grammar->wrap($this->getAttribute());
        $pathValues = Str::of($this->name)
            ->after('.')
            ->explode('.')
            ->implode(fn (string $part) => $grammar->escape($part), ', ');

        return DB::raw("jsonb_extract_path($column, $pathValues)");
    }
}
