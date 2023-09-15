<?php

namespace Clickbar\AgGrid\Model;

use Clickbar\AgGrid\Exceptions\ViewManipulationNotAllowedException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AgGridViewModel extends Model
{
    public static function create(array $attributes = [])
    {
        throw new ViewManipulationNotAllowedException(__FUNCTION__, get_called_class());
    }

    public static function forceCreate(array $attributes)
    {
        throw new ViewManipulationNotAllowedException(__FUNCTION__, get_called_class());
    }

    public function save(array $options = [])
    {
        throw new ViewManipulationNotAllowedException(__FUNCTION__, get_called_class());
    }

    public function update(array $attributes = [], array $options = [])
    {
        throw new ViewManipulationNotAllowedException(__FUNCTION__, get_called_class());
    }

    public static function firstOrCreate(array $attributes, array $values = [])
    {
        throw new ViewManipulationNotAllowedException(__FUNCTION__, get_called_class());
    }

    public static function firstOrNew(array $attributes, array $values = [])
    {
        throw new ViewManipulationNotAllowedException(__FUNCTION__, get_called_class());
    }

    public static function updateOrCreate(array $attributes, array $values = [])
    {
        throw new ViewManipulationNotAllowedException(__FUNCTION__, get_called_class());
    }

    public function delete()
    {
        throw new ViewManipulationNotAllowedException(__FUNCTION__, get_called_class());
    }

    public static function destroy($ids)
    {
        throw new ViewManipulationNotAllowedException(__FUNCTION__, get_called_class());
    }

    public function restore()
    {
        throw new ViewManipulationNotAllowedException(__FUNCTION__, get_called_class());
    }

    public function forceDelete()
    {
        throw new ViewManipulationNotAllowedException(__FUNCTION__, get_called_class());
    }

    public function performDeleteOnModel()
    {
        throw new ViewManipulationNotAllowedException(__FUNCTION__, get_called_class());
    }

    public function push()
    {
        throw new ViewManipulationNotAllowedException(__FUNCTION__, get_called_class());
    }

    public function finishSave(array $options)
    {
        throw new ViewManipulationNotAllowedException(__FUNCTION__, get_called_class());
    }

    public function performUpdate(Builder $query, array $options = []): bool
    {
        throw new ViewManipulationNotAllowedException(__FUNCTION__, get_called_class());
    }

    public function touch($attribute = null)
    {
        throw new ViewManipulationNotAllowedException(__FUNCTION__, get_called_class());
    }

    public function insert()
    {
        throw new ViewManipulationNotAllowedException(__FUNCTION__, get_called_class());
    }

    public function truncate()
    {
        throw new ViewManipulationNotAllowedException(__FUNCTION__, get_called_class());
    }
}
