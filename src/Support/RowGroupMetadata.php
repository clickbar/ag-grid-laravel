<?php

namespace Clickbar\AgGrid\Support;

use Illuminate\Database\Eloquent\Builder;

class RowGroupMetadata
{

    public function __construct(
        protected ?array $rowGroupCols,
        protected ?array $groupKeys,
    )
    {
    }


    public static function fromParams(array $params): self
    {

        return new self(
            $params['rowGroupCols'] ?? null,
            $params['groupKeys'] ?? null,
        );

    }


    public function appendQueryBuilderMethods(Builder &$builder): Builder
    {

        // Append the where conditions for all equipped group cols
        $lastEquippedRowGroupColIndex = count($this->groupKeys ?? []) - 1;
        for ($index = 0; $index <= $lastEquippedRowGroupColIndex; $index++) {
            $builder->where($this->rowGroupCols[$index]['field'], $this->groupKeys[$index]);
        }

        // Add the group by column
        $currentRowGroupCol = $this->getCurrentRowGroupCol();
        if ($currentRowGroupCol){
            $builder->cleanBindings(['select']);
            $builder->select($currentRowGroupCol['field']);
            $builder->groupBy($currentRowGroupCol['field']);
        }

        return $builder;

    }

    public function getCurrentRowGroupCol(): ?array {

        if (!$this->isGrouped()){
            return null;
        }

        return $this->rowGroupCols[count($this->groupKeys)];
    }

    public function isColumnAvailable(string $column): bool {

        if (!$this->isGrouped()){
            return true;
        }

        return $this->getCurrentRowGroupCol()['field'] === $column;
    }

    public function isGrouped(): bool
    {
        if (empty($this->rowGroupCols)) {
            return false;
        }

        // --> rowGroupCols available and not empty

        if (empty($this->groupKeys)) {
            return true;
        }

        // --> groupKeys available and not empty

        // Check if the all rowGroupCols are equipped with a key => no grouping anymore
        return count($this->rowGroupCols) !== count($this->groupKeys);
    }

}
