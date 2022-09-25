<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\DocumentEntityMetadata;
use Illuminate\Console\Command;
use MeiliSearch;

class ConfigureMeiliSearchFilterableAttributes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meilisearch:configure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure MeiliSearch Fitlerable Attributes';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $host = env('MEILISEARCH_HOST', null);
        $masterKey = env('MEILISEARCH_KEY', null);

        $client = new MeiliSearch\Client($host, $masterKey);

        $rankingRules = [
            "proximity",
            "words",
            "typo",
            "attribute",
            "sort",
            "exactness",
        ];

        foreach ($this->indexes() as $index => $attributes) {
            $client->index($index)->updateRankingRules($rankingRules);
            $client->index($index)->updateFilterableAttributes($attributes['filter']);
            $client->index($index)->updateSortableAttributes($attributes['sort']);
        }

        $client->index('documents')->updateSettings([
            'distinctAttribute' => 'filename'
        ]);

        $client->index('document_entity_metadata')->updateSettings([
            'distinctAttribute' => 'text'
        ]);
    }

    private function indexes() 
    {
        return [
            'documents' => [
                'filter' => [
                    'id',
                    'tenant_id',
                    'file_name',
                    'formatted_udfs',
                    'file_extension',
                    'file_size',
                    'formatted_updated_at',
                    'formatted_detail_metadata',
                    'user_access'
                ],
                'sort' => [
                    'formatted_updated_at', 
                    'file_size', 
                    'file_name'
                ]
            ],
            'document_entity_metadata' => [
                'filter' => [
                    'id',
                    'document_id',
                    'text',
                    'score',
                    'type'
                ],
                'sort' => [
                    'formatted_updated_at'
                ]
            ]
        ];
    }
}
