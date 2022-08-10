<?php

namespace App\Console\Commands;

use Artisan;
use Illuminate\Console\Command;

class ProjectDeploy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:deploy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploy the project';

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
        Artisan::call('migrate --force');
        Artisan::call('run:seeders');

        return true;
    }
}
