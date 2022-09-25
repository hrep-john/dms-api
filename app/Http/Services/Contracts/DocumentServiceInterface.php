<?php

namespace App\Http\Services\Contracts;

use App\Models\Document;

/**
* Interface DocumentServiceInterface
* @package App\Services\Contracts
*/
interface DocumentServiceInterface extends BaseServiceInterface
{
    public function upload($attributes): Document;
    public function download(int $id);
    public function search();
    public function findDocumentByMediaId(int $mediaId);
    public function recentlyAssignedDocuments();
}