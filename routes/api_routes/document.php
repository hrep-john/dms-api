<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentDetailMetadataController;
use App\Http\Controllers\DocumentEntityMetadataController;

Route::post('documents/search', [DocumentController::class, 'search'])->name('documents.search');
Route::post('documents/download', [DocumentController::class, 'download'])->name('documents.download');
Route::post('documents/preview', [DocumentController::class, 'preview'])->name('documents.preview');
Route::post('documents/bulk-delete', [DocumentController::class, 'bulkDestroy'])->name('documents.bulk-destoy');
Route::delete('documents/revert', [DocumentController::class, 'revert'])->name('documents.revert');
Route::get('documents/{id}/audit-logs', [DocumentController::class, 'documentAuditLogs'])->name('documents.audit.logs');
Route::post('documents/{id}/extract', [DocumentDetailMetadataController::class, 'extract'])->name('document_detail_metadata.extract');
Route::post('documents/{id}/analyze', [DocumentEntityMetadataController::class, 'analyze'])->name('document_entity_metadata.analyze');

Route::apiResource('documents', DocumentController::class);
Route::apiResource('documents/{id}/metadata/details', DocumentDetailMetadataController::class);
Route::apiResource('documents/{id}/metadata/entities', DocumentEntityMetadataController::class);

// this api endpoint serves as a utility to set documents transferred from UAT to Live as searchable

Route::post('documents/set-searchable/{id}', [DocumentController::class, 'setDocumentSearchable'])->name('documents.set-searchable');