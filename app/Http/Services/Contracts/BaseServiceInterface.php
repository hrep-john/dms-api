<?php

namespace App\Http\Services\Contracts;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
* Interface BaseServiceInterface
* @package App\Services\Contracts
*/
interface BaseServiceInterface
{
    /**
    * @param array $attributes
    * @return Model
    */
    public function store($attributes): Model;

    /**
    * @param array $attributes
    * @param Model $model
    * @return Model
    */
    public function update(array $attributes, Model $model): Model;

    /**
    * @param Model $model
    * @return Model
    */
    public function delete(Model $model): Model;

    /**
    * @param Array $ids
    * @return Collection
    */
    public function findMany(Array $ids): ?Collection;

    /**
    * @param $id
    * @return Model
    */
    public function find($id): ?Model;

    /**
    * @param $field
    * @param $value
    * @return Model
    */
    public function findBy($field, $value): ?Model;

    /**
    * @return Collection
    */
    public function all();
    public function totalCount();
    public function paginate();
}