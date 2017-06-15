<?php

namespace DALTCORE\LaravelDeployHelper\Console\Commands;

use DALTCORE\LaravelDeployHelper\Helpers\Deployer;
use DALTCORE\LaravelDeployHelper\Helpers\Locker;
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

        // Connecting to remote server
        verbose('[' . $this->option('stage') . '] Trying to login into remote SSH');
        $ssh = SSH::instance()->into($this->option('stage'));

        // Check for lockfile
        if (Locker::lock($ssh, $this->option('stage')) === false) {
            // Cannot create lock file, stop the process!
            exit(1);
        }

        // Trying to read file
        verbose('[' . $this->option('stage') . '] Reading config file from remote server');
        $config = $ssh->exists(SSH::home($this->option('stage')) . '/ldh.json');

        // Check if config exists
        if ($config == false) {
            error('[' . $this->option('stage') . '] ldh.json does not exists.');
            exit(128);
        } else {
            verbose('[' . $this->option('stage') . '] Found config. Checking directories.');
            $config = $ssh->getString(SSH::home($this->option('stage')) . '/ldh.json');
            if ($config == false) {
                error('[' . $this->option('stage') . '] Config file is empty... Something is wrong.');
                exit(0);
            }
            $this->ldh = json_decode($config, true);
        }

        krsort($this->ldh);
        $dirs = [];
        foreach ($this->ldh as $dir => $beh) {
            $dirs[] = $dir;
        }

        if (isset($dirs[1]) == false) {
            throw new \Exception('Cannot rollback, no more entries!');
        }

        $this->ldh = Deployer::doRollback($ssh, $this->option('stage'), $this->ldh, $dirs);
        $ssh->putString(SSH::home($this->option('stage')) . '/ldh.json', json_encode($this->ldh));
        verbose('[' . $this->option('stage') . '] Rolled back!');
        Locker::unlock($ssh, $this->option('stage'));
    }
}
