<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\TenantSetting;
use Illuminate\Console\Command;

class FixDocumentSeries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:document-series';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix Document Series';

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
        $tenantSettings = TenantSetting::all();
        $counter = 1;

        foreach(Document::withTrashed()->cursor() as $document) {
            $tenantId = $document->folder->tenant_id;
            $prefix = $tenantSettings->where('tenant_id', $tenantId)->where('key', 'tenant.document.series.id.prefix')->first()->value;
            $length = $tenantSettings->where('tenant_id', $tenantId)->where('key', 'tenant.document.series.counter.length')->first()->value;

            $seriesId = sprintf('%s%s', $prefix, str_pad($counter, $length, "0", STR_PAD_LEFT));
            $document->update(['series_id' => $seriesId]);
            $counter++;

            TenantSetting::where('tenant_id', $tenantId)->where('key', 'tenant.document.series.current.counter')->update(['value' => $counter]);
        }

        return true;
    }
}
