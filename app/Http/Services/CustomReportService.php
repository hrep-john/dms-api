<?php

namespace App\Http\Services;

use App\Enums\Voided;
use App\Http\Services\Contracts\CustomReportServiceInterface;
use App\Models\ReportBuilder;
use App\Traits\FilterRebuild;
use Arr;
use DB;
use Exception;
use stdClass;
use Str;

class CustomReportService extends BaseService implements CustomReportServiceInterface
{
    use FilterRebuild;

    /**
    * RoleService constructor.
    *
    * @param Role $model
    */
    public function __construct(ReportBuilder $model)
    {
        parent::__construct($model);
    }

    public function report($attributes)
    {
        $template = $this->model->where('slug', $attributes['slug'])->first();
        $query = JSON_DECODE($template->format)->query;
        Logger(JSON_ENCODE($query));
        $result = $this->build($query, [])->get();
        Logger($result);

        // Logger($template);
        return $result;
    }

    public function build($querySet, $filters)
    {
        $builder = $this->query($querySet);

        return $this->applyFilters($builder, $filters);
    }

    public function buildSummary($template, $filters)
    {
        $query = $template->format->query;
        
        unset($query->orderBy);

        $builder = $this->build($query, $filters);

        return $this->applySummary($builder, $template->format->summaries);
    }

    private function query($querySet) 
    {
        $builder = DB::query();

        if(is_object($querySet->table)) {
            $subBuilder = $this->query($querySet->table);
            $builder = $this->createSubQuery($subBuilder);
        } else {
            $builder = $this->buildTable($querySet->table, $builder);
        }

        $builder = $this->buildSelect($querySet->select ?? [], $builder);
        $builder = $this->buildJoin($querySet->join ?? [], $builder);
        $builder = $this->buildWhere($querySet->where ?? [], $builder);
        $builder = $this->buildGroupBy($querySet->groupBy ?? [], $builder);
        $builder = $this->buildOrderBy($querySet->orderBy ?? [], $builder);

        return $builder;
    }

    private function buildSelect($params, $builder) 
    {
        $select = [];

        if(count($params) === 0) {
            $select = ['*'];
        } else {
            foreach($params as $param) {
                array_push($select, $this->getParamColumn($param));
            }
        }

        return $builder->select($select);
    }

    private function buildTable($params, $builder) 
    {
        return $builder->from($params);
    }

    private function buildJoin($params, $builder) 
    {
        foreach($params as $param) {
            //types - inner, left, right, cross
            $type = 'inner';

            if(property_exists($param, 'type')) {
                if(!in_array(strtolower($param->type), [
                    'inner', 'left', 'right', 'cross'
                ])) {
                    throw new Exception('Join type doesn\'t exists.');
                }

                $type = strtolower($param->type);
            }

            $that = $this;

            $builder = $builder->join(
                $param->table,
                function($query) use($that, $param) {
                    $query = $that->buildWhere($param->conditions, $query);
                },
                null, //operator
                null, //second comparison
                $type
            );
        }

        return $builder;
    }

    private function buildWhere($params, $builder) 
    {
        foreach($params as $param) {
            //type - and, or
            $type = 'and';

            if(property_exists($param, 'type')) {
                if(!in_array(strtolower($param->type), [
                    'and',
                    'or',
                    'raw'
                ])) {
                    throw new Exception('Where method doesn\'t exists.');
                }

                $type = strtolower($param->type);
            }

            if(!property_exists($param, 'operator') && $type == 'raw') {
                $builder = $builder->whereRaw($param->column);
            } else {
                if (Str::contains('null', $param->operator)) {
                    $not = Str::contains('!', $param->operator);
    
                    $builder = $builder->whereNull($param->column, $type, $not);
                } else {
                    $builder = $builder->where(
                        $param->column,
                        $param->operator,
                        $this->getParamColumn($param->value),
                        $type
                    );
                }
            }
        }

        return $builder;
    }

    private function buildGroupBy($params, $builder) 
    {
        if(count($params) === 0) {
            return $builder;
        }

        $groupBy = [];

        foreach($params as $param) {
            array_push($groupBy, $this->getParamColumn($param));
        }

        return $builder->groupBy($groupBy);
    }

    private function buildOrderBy($params, $builder)
    {
        if(!is_object($params) || count($params) === 0) {
            return $builder;
        }

        foreach($params->columns as $column) {
            $builder = $builder->orderBy($this->getParamColumn($column), $params->type);
        }

        return $builder;
    }

    private function getParamColumn($param)
    {
        return $this->isParamRaw($param)
            ? DB::raw($param->column)
            : $param;
    }

    private function isParamRaw($param)
    {
        return (is_object($param) && strtolower($param->type) == 'raw');
    }

    private function createSubQuery($builder)
    {
        return DB::table(DB::raw("(".$builder->toSql().") as inner_query"))
            ->mergeBindings($builder);
    }

    /**
     * Apply column filter conditions on the Query Builder instance
     *
     * @param $builder
     * @param $filterParams
     * @return Builder|mixed
     */
    private function applyFilters($builder, $filterParams)
    {   
        if(count($filterParams) > 0) {
            foreach ($filterParams as $key => $filterParams) {
                $filterParams = $this->rebuild((object) ($filterParams));
                $filters = $this->populateFiltersByColumn($filterParams);

                if (count($filters) > 0) {
                    foreach ($filters as $filter) {
                        $builder = $this->applyWhereCondition($builder, $filter);
                    }
                }
            }
        }

        return $builder;
    }

    private function applyWhereCondition($builder, $filter)
    {
        $method = '';

        if(Str::lower($filter->join) == 'and') {
            $method = 'where';
        } else if((Str::lower($filter->join) == 'or')) {
            $method = 'orWhere';
        }

        if (Str::contains($filter->column, 'deleted_at')) {
            $filter->operator = (int) $filter->value === Voided::Yes ? '<>' : '=';
            $filter->value = null;
        }

        return call_user_func_array(array($builder, $method), array(
            $filter->column,
            $filter->operator,
            $filter->value
        ));
    }

    private function applySummary($builder, $params)
    {   
        if (count($params) > 0) {
            $builder = $this->createSubQuery($builder)
                ->select($this->rebuildSummary($params));
        }

        return $builder;
    }

    private function rebuildSummary($params)
    {
        $select = [];

        foreach($params as $column => $param) {
            array_push($select, DB::raw("$param->operator($column) AS $column"));
        }

        return $select;
    }

    private function populateFiltersByColumn($params)
    {
        $filters = [];

        if (!is_array($params->column)) {
            $params->column = Arr::wrap($params->column);
        }

        if (count($params->column) > 0) {
            foreach ($params->column as $column) {
                $object = new stdClass();
                $object->column = $column;
                $object->operator = $params->operator;
                $object->value = $params->value;
                $object->join = $params->join;
                $object->type = $params->type;

                $filters[] = $object;
            }
        }

        return $filters;
    }
}