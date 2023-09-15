<?php

namespace Clickbar\AgGrid\Support;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

class ColumnMetadata
{
    public function __construct(
        protected Model $baseModel,
        /** @var RelationMetadata[] */
        protected array $relations,
        protected string $column,
    ) {
    }

    public static function fromString(EloquentBuilder|Relation $subject, string $column): self
    {

        $parts = Str::of($column)->explode('.');

        if ($parts->count() === 1) {
            // --> No nested information
            return new self($subject->getModel(), [], $column);
        }

        $relations = [];
        $model = $subject->getModel();

        foreach ($parts as $index => $part) {

            $relationName = Str::camel($part);
            $modelRelations = self::getRelations($model::class);

            if ($modelRelations->contains($relationName)) {
                $relation = $model->$relationName();
                $model = $relation->getModel();

                $relations[] = new RelationMetadata($relationName, $model);
            } else {
                // --> End of relation (further dots must be json nesting)
                $remaining = $parts->skip($index + 1)->implode('.');
                if (! empty($remaining)) {
                    $remaining = '.'.$remaining;
                }
                $column = $part.$remaining;
                break;
            }
        }

        return new self($subject->getModel(), $relations, $column);

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

    public function getColumn(): string
    {
        return $this->column;
    }

    public function isJsonColumn(): bool
    {
        $model = collect($this->relations)->last()?->model ?? $this->baseModel;
        $colum = Str::before($this->column, '.');

        return str_contains($this->column, '.') || $model->hasCast($colum, [
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

    public function getColumnAsJsonPath(): string
    {
        return str_replace('.', '->', $this->column);
    }
}
