<?php

namespace App\Console\Commands;

use Artisan;
use Illuminate\Console\Command;

class RunSeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:seeders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run all listed seeders';

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
        Artisan::call('db:seed --class=TenantSeeder --force');
        Artisan::call('db:seed --class=ProjectInitSeeder --force');
        Artisan::call('db:seed --class=AdminUserSeeder --force');
        Artisan::call('db:seed --class=FolderSeeder --force');
        Artisan::call('db:seed --class=TenantSettingsSeeder --force');
        Artisan::call('db:seed --class=UpdateAllowUserAccessExistingDocumentsSeeder --force');
        Artisan::call('db:seed --class=AddTenantDefaultDocumentUserAccess --force');
        Artisan::call('db:seed --class=InsertDocumentUploadedEventInAuditsSeeder --force');
        Artisan::call(sprintf('scout:flush App\\\Models\\\%s', 'Document'));
        Artisan::call(sprintf('scout:import App\\\Models\\\%s', 'Document'));

        return true;
    }
}
