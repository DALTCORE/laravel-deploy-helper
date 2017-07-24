<?php

namespace DALTCORE\LaravelDeployHelper\Console\Commands;

use DALTCORE\LaravelDeployHelper\Console\Command;
use DALTCORE\LaravelDeployHelper\Helpers\Deployer;
use DALTCORE\LaravelDeployHelper\Helpers\SSH;

class Deploy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ldh:deploy 
                            {--stage= : Server that needs a deploy} 
                            {--branch= : Branch that is going to be deployed to stage}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploy this project to a sever';

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
        $this->ldh = SSH::preFlight($this, $this->stage, $this->branch);

        // Do deploy
        $this->ldh = Deployer::doDeploy($this->stage, $this->branch, $this->ldh);

        // Write to config
        SSH::instance()->into($this->stage)->putString(SSH::home($this->stage).'/ldh.json', json_encode($this->ldh));

        // Done
        SSH::performLanding($this->stage);
    }
}
