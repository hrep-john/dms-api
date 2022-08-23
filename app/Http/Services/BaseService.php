<?php   

namespace App\Http\Services;

use App\Http\Services\Contracts\BaseServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\DatabaseTransaction;
use Str;

class BaseService implements BaseServiceInterface
{     
    use DatabaseTransaction;

    /**      
     * @var Model      
     */     
    protected $model;       

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

        $results = $this->model;

        foreach($filters as $filter) {
            $filter = JSON_DECODE($filter);
            $method = Str::lower($filter->join) === 'or' ? 'orWhere' : 'where';
            $results = $results->{$method}($filter->column, $filter->operator, $filter->value);
        }

        return $results->paginate($perPage);
    }

    /**
    * @param array $attributes
    *
    * @return Model
    */
    public function store($attributes): Model
    {
        $that = $this;

        $attributes = array_merge($this->formatAttributes($attributes), [
            'created_by' => auth()->user()->id,
            'updated_by' => auth()->user()->id
        ]);

        return $this->transaction(function() use ($attributes, $that) {
            $model = $that->model->create($attributes);
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

        $attributes = array_merge($this->formatAttributes($attributes), [
            'updated_by' => auth()->user()->id,
        ]);

        return $this->transaction(function() use ($attributes, $model, $that) {
            $model->update($attributes);
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
        return $this->model->find($id);
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

    // Custom Hooks

    protected function formatAttributes($attributes): array
    {
        return $attributes;
    }

    protected function afterStore($model, $attributes): void
    {
        
    }

    protected function afterUpdated($model, $attributes): void
    {
        
    }

    protected function afterDelete($model): void
    {
        
    }
}