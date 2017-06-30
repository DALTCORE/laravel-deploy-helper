<?php

namespace DALTCORE\LaravelDeployHelper\Console\Commands;

use DALTCORE\LaravelDeployHelper\Helpers\Deployer;
use DALTCORE\LaravelDeployHelper\Helpers\SSH;
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
     * @var array
     */
    protected $ldh = [];

    /**
     * Create a new command instance.
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
        $this->ldh = SSH::preFlight($this, $this->option('stage'));

        krsort($this->ldh);
        $dirs = [];
        foreach ($this->ldh as $dir => $beh) {
            $dirs[] = $dir;
        }

        if (isset($dirs[1]) == false) {
            throw new \Exception('Cannot rollback, no more entries!');
        }

        $ssh = SSH::instance()->into($this->option('stage'));
        $this->ldh = Deployer::doRollback($ssh, $this->option('stage'), $this->ldh, $dirs);

        // Write to config
        SSH::instance()
            ->into($this->option('stage'))
            ->putString(SSH::home($this->option('stage')) . '/ldh.json', json_encode($this->ldh));

        SSH::performLanding($this->option('stage'));
    }
}
