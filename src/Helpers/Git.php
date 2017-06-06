<?php

namespace DALTCORE\LaravelDeployHelper\Helpers;

class Git extends Command {

    public static function getBranches()
    {
        $branches = [];

        $output = self::command('git', ['branch']);

        foreach(explode(PHP_EOL, trim($output)) as $branch) {
            $branch = str_replace('* ', '', $branch);
            $branches[] = trim($branch);
        }

        return $branches;
    }
}