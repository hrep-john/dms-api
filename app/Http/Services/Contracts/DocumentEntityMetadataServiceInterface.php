<?php

namespace App\Http\Services\Contracts;

use App\Models\Document;

/**
* Interface DocumentEntityMetadataServiceInterface
* @package App\Services\Contracts
*/
interface DocumentEntityMetadataServiceInterface extends BaseServiceInterface
{
    public function analyze(Document $model, $user);
}