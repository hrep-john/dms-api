<?php

namespace App\Traits;

trait Paginable
{
    public function paginate($items)
    {
        $items = collect($items);

        return [
            'data' => $items->get('data'),
            'meta' => $items->except('data')
        ];
    }
}