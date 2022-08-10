<?php

namespace App\Http\Services\Contracts;

use App\Models\Document;

/**
* Interface DocumentDetailMetadataServiceInterface
* @package App\Services\Contracts
*/
interface DocumentDetailMetadataServiceInterface extends BaseServiceInterface
{
    public function extract(Document $model, $user);
    public function getResults(Document $model, $user, $jobId);
}