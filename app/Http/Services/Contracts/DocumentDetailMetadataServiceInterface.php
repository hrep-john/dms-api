<?php

namespace App\Http\Services\Contracts;

use App\Models\Document;

/**
* Interface DocumentDetailMetadataServiceInterface
* @package App\Services\Contracts
*/
interface DocumentDetailMetadataServiceInterface extends BaseServiceInterface
{
    public function extract(string $filePath, int $userId);
    public function getResults(int $documentId, int $userId, string $jobId);
}