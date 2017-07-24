<?php

namespace DALTCORE\LaravelDeployHelper\Console;

use Illuminate\Console\Command as BaseCommand;

class Command extends BaseCommand
{
    /**
     * @var string
     */
    protected $stage;

    /**
     * @var string
     */
    protected $branch;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Setting stage and branch if they were given,
     * else set them from the config if the indexes are found.
     *
     * If notting was found, the values will be null as aspected.
     */
    public function boot()
    {
        $config = config('laravel-deploy-helper');

        $this->stage = $this->option('stage');
        if (is_null($this->stage) && isset($config['default']['stage'])) {
            $this->stage = $config['default']['stage'];
        }

        $this->branch = $this->option('branch');
        if (!is_null($this->stage) && is_null($this->branch) && isset($config['stages'][$this->stage]['branch'])) {
            $this->branch = $config['stages'][$this->stage]['branch'];
        }
    }
}
