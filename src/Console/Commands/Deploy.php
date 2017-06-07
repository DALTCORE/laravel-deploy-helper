<?php

namespace DALTCORE\LaravelDeployHelper\Console\Commands;

use DALTCORE\LaravelDeployHelper\Helpers\Deployer;
use DALTCORE\LaravelDeployHelper\Helpers\Git;
use DALTCORE\LaravelDeployHelper\Helpers\SSH;
use Illuminate\Console\Command;

class Deploy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ldh:deploy 
                            {--stage= : Server that needs a deploy} 
                            {--branch= : Branch that is going to be deployed to staging}';

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
     * @return mixed
     */
    public function handle()
    {
        cli_header();
        // Pre flight checking
        if ($this->option('stage') === null) {
            throw new \Exception('The argument "--stage=" is required!', 128);
        } else {
            if (!is_array(config('laravel-deploy-helper.stages.' . $this->option('stage')))) {
                throw new \Exception('The stage "' . $this->option('stage')
                    . '" does not exist!', 128);
            }
        }

        if ($this->option('branch') === null) {
            throw new \Exception('The argument "--branch=" is required!', 128);
        }

        if (in_array($this->option('branch'), Git::getBranches()) == false) {
            throw new \Exception('The branch "' . $this->option('branch')
                . '" does not exists locally? Please `git pull`!', 128);
        }

        // Connecting to remote server
        verbose('[' . $this->option('stage') . '] Trying to login into remote SSH');
        $ssh = SSH::instance()->into($this->option('stage'));


        // Trying to read file
        verbose('[' . $this->option('stage') . '] Reading config file from remote server');
        $config = $ssh->exists(SSH::home($this->option('stage')) . '/ldh.json');

        // Check if config exists
        if ($config == false) {
            error('[' . $this->option('stage') . '] ldh.json does not exists.');
            if ($this->confirm('Do you want to initialize LDH here?')) {
                Deployer::freshInit($ssh, $this->option('stage'));
            } else {
                return false;
            }
        } else {
            verbose('[' . $this->option('stage') . '] Found config. Checking directories.');
            $config = $ssh->getString(SSH::home($this->option('stage')) . '/ldh.json');
            if ($config == false) {
                error('[' . $this->option('stage') . '] Config file is empty... Something is wrong.');
                return false;
            }
            $this->ldh = json_decode($config, true);
        }

        // Do deploy
        $this->ldh = Deployer::doDeploy($ssh, $this->option('stage'), $this->option('branch'), $this->ldh);

        // Write to config
        $ssh->putString(SSH::home($this->option('stage')) . '/ldh.json', json_encode($this->ldh));

        // Done
        verbose('[' . $this->option('stage') . '] Deploy successfull!');
    }
}
