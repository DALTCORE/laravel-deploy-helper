<?php

namespace DALTCORE\LaravelDeployHelper\Helpers;

class Git extends Command
{

    /**
     * Get branches from current git repository
     *
     * @return array
     */
    public static function getBranches()
    {
        $branches = [];

        $output = self::command('git', ['branch']);

        foreach (explode("\n", trim($output)) as $branch) {
            $branch = str_replace('* ', '', $branch);
            $branches[] = trim($branch);
        }

        return $branches;
    }
}
