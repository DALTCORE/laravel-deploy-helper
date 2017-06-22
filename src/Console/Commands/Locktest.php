<?php

namespace DALTCORE\LaravelDeployHelper\Console\Commands;

use DALTCORE\LaravelDeployHelper\Helpers\Locker;
use DALTCORE\LaravelDeployHelper\Helpers\SSH;
use Illuminate\Console\Command;

class Locktest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ldh:locktest
                            {--stage= : Server that needs a check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test if the system can lock and unlock the lockfile';

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
        cli_header();

        // Pre flight checking
        if ($this->option('stage') === null) {
            throw new \Exception('The argument "--stage=" is required!', 128);
        } else {
            if (!is_array(config('laravel-deploy-helper.stages.'.$this->option('stage')))) {
                throw new \Exception('The stage "'.$this->option('stage')
                    .'" does not exist!', 128);
            }
        }

        $stage = $this->option('stage');

        // Connecting to remote server
        verbose('['.$this->option('stage').'] Trying to login into remote SSH');
        $ssh = SSH::instance()->into($this->option('stage'));

        verbose('Setting lock: '.(Locker::lock($ssh, $stage) ? 'OK' : 'Error'));
        verbose('Getting lock: '.(Locker::verify($ssh, $stage) ? 'OK' : 'Error'));
        verbose('Path to lock: '.Locker::getLockPath($ssh, $stage));
        verbose('Destroy lock: '.(Locker::unlock($ssh, $stage) ? 'OK' : 'Error'));
    }
}
