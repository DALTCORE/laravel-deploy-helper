<?php

namespace DALTCORE\LaravelDeployHelper\Console\Commands;

use DALTCORE\LaravelDeployHelper\Helpers\Git;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

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
     * @return mixed
     */
    public function handle()
    {
        // Pre flight checking
        if($this->option('stage') === null)
        {
            throw new \Exception('The argument "--stage=" is required!', 128);
        }

        if($this->option('branch') === null)
        {
            throw new \Exception('The argument "--branch=" is required!', 128);
        }

        dump(Git::getBranches());

    }
}
