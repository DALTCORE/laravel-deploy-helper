<?php

namespace DALTCORE\LaravelDeployHelper\Console\Commands;

use DALTCORE\LaravelDeployHelper\Console\Command;
use DALTCORE\LaravelDeployHelper\Helpers\Deployer;
use DALTCORE\LaravelDeployHelper\Helpers\SSH;

class Patch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ldh:patch 
                            {--stage= : Server that needs a deploy} 
                            {--branch= : Branch that is going to be patched to stage}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploy minor-change patches';

    /**
     * @var array
     */
    protected $ldh = [];

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
     * @throws \Exception on failure
     *
     * @return mixed
     */
    public function handle()
    {
        $this->boot();

        // Do some pre-deploy checks
        SSH::preFlight($this, $this->stage, $this->branch);

        // Get the band-aid, we're going to patch some shit
        Deployer::doPatch($this->stage, $this->branch);

        // Done
        SSH::performLanding($this->stage);
    }
}
