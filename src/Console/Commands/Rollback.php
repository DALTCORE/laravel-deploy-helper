<?php

namespace DALTCORE\LaravelDeployHelper\Console\Commands;

use Illuminate\Console\Command;

class Rollback extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ldh:rollback {--stage= : Server that needs a deploy}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback the deployed application by one instance';


    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @throws \Exception on failure
     * @return mixed
     */
    public function handle()
    {
        //
    }
}
