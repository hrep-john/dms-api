<?php   

namespace App\Http\Services;

use App\Http\Services\Contracts\BaseServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\DatabaseTransaction;
use Illuminate\Database\Eloquent\Builder;
use Str;

class BaseService implements BaseServiceInterface
{     
    use DatabaseTransaction;

    /**      
     * @var model      
     * @var joins     
     */     
    protected $model;       
    protected $joins = [];       

    /**      
     * BaseService constructor.      
     *      
     * @param Model $model      
     */     
    public function __construct($model)     
    {         
        $this->model = $model;
    }

    /**
    * @return Collection
    */
    public function all($withFiltering = true)
    {
        $builder = $this->model;
        $builder = $this->buildJoins($builder);

        if ($withFiltering) {
            $filters = request()->get('filters', []);

            foreach($filters as $filter) {
                $filter = JSON_DECODE($filter);
                $builder = $builder->where($filter->column, $filter->operator, $filter->value);
            }
        }

        return $builder->get();    
    }

    /**
    * @return Collection
    */
    public function totalCount()
    {
        return $this->model->count();    
    }

    public function paginate() 
    {
        $perPage = request()->get('per_page', 10);
        $filters = request()->get('filters', []);

        $model = $this->model;

        $model = $this->beforeFiltering($model);

        foreach($filters as $filter) {
            $filter = JSON_DECODE($filter);
            $method = Str::lower($filter->join) === 'or' ? 'orWhere' : 'where';
            $model = $model->{$method}($filter->column, $filter->operator, $filter->value);
        }

        return $model->paginate($perPage);
    }

    /**
    * @param array $attributes
    *
    * @return Model
    */
    public function store($attributes): Model
    {
        $that = $this;

        $newAttributes = array_merge($this->formatAttributes($attributes, 'store'), [
            'created_by' => auth()->user()->id,
            'updated_by' => auth()->user()->id
        ]);

        return $this->transaction(function() use ($newAttributes, $attributes, $that) {
            $model = $that->model->create($newAttributes);
            $that->afterStore($model, $attributes);

            return $this->model->withoutGlobalScopes()->find($model->id);
        });
    }

    /**
    * @param array $attributes
    * @param int $id
    *
    * @return Model
    */
    public function update(array $attributes, Model $model): Model
    {
        $that = $this;

        $newAttributes = array_merge($this->formatAttributes($attributes, 'update'), [
            'updated_by' => auth()->user()->id,
        ]);

        return $this->transaction(function() use ($newAttributes, $attributes, $model, $that) {
            $model->update($newAttributes);
            $model->touch();
            $that->afterUpdated($model, $attributes);

            return $model;
        });
    }

    /**
    * @param int $id
    *
    * @return Model
    */
    public function delete(Model $model): Model
    {
        $that = $this;

        return $this->transaction(function() use ($model, $that) {
            $model->delete();
            $that->afterDelete($model);

            return $model;
        });
    }

    /**
    * @param $id
    * @return Model
    */
    public function find($id): ?Model
    {
        $model = $this->model->find($id);

        return $model;
    }

    /**
    * @param $ids
    * @return Model
    */
    public function findMany(Array $ids): ?Collection
    {
        return $this->model->findMany($ids);
    }

    /**
    * @param $field
    * @param $value
    * @return Model
    */
    public function findBy($field, $value): ?Model
    {
        return $this->model->where($field, $value)->first();
    }

    protected function buildJoins($builder)
    {
        foreach ($this->joins as $join) {
            $builder = $builder->join($join['table'], $join['column_x'], $join['operator'], $join['column_y']);
        }

        return $builder;
    }

    // Custom Hooks

    protected function formatAttributes($attributes, $method): array
    {
        return $attributes;
    }

    protected function afterStore($model, $attributes): void
    {
        
    }

    protected function afterShown($model): void
    {
        
    }

    protected function afterUpdated($model, $attributes): void
    {
        
    }

    protected function afterDelete($model): void
    {
        
    }

    protected function beforeFiltering($model)
    {
        return $model;
    }
}