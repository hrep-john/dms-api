<?php 

namespace App\Traits;

trait FilterRebuild
{
    /**
     * Rebuild current filter operator and values
     *
     * @param object $filter
     * @return object
     */
    public function rebuild($filter)
    {
        switch ($filter->operator) {
            case 'notContains':
                $filter->value = "%".$filter->value."%";
                $filter->operator = 'NOT LIKE';
                break;

            case 'contains':
                $filter->value = "%".$filter->value."%";
                $filter->operator = 'LIKE';
                break;

            case 'startsWith':
                $filter->value = $filter->value."%";
                $filter->operator = 'LIKE';
                break;

            case 'endsWith':
                $filter->value = "%".$filter->value;
                $filter->operator = 'LIKE';
                break;
        }

        return $filter;
    }
}