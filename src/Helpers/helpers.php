<?php

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;

if (!function_exists('verbose')) {
    /**
     * Output to termial.
     *
     * @param $message
     *
     * @return string formatted for CLI
     */
    function verbose($message)
    {
        $console = new ConsoleOutput();
        $console->getFormatter()->setStyle('info', new OutputFormatterStyle('blue', null));
        $console->writeln('<info>' . $message . '</info>');
    }
}

if (!function_exists('error')) {
    /**
     * Output to termial.
     *
     * @param $message
     *
     * @return string formatted for CLI
     */
    function error($message)
    {
        $console = new ConsoleOutput();
        $console->getFormatter()->setStyle('error', new OutputFormatterStyle('white', 'red'));
        $console->writeln('<error>' . $message . '</error>');
    }
}

if (!function_exists('cli_header')) {
    /**
     * Output to termial.
     *
     * @param $message
     *
     * @return string formatted for CLI
     */
    function cli_header()
    {
        echo "\t$$\       $$$$$$$\  $$\   $$\ 
\t$$ |      $$  __$$\ $$ |  $$ |
\t$$ |      $$ |  $$ |$$ |  $$ |
\t$$ |      $$ |  $$ |$$$$$$$$ |
\t$$ |      $$ |  $$ |$$  __$$ |
\t$$ |      $$ |  $$ |$$ |  $$ |
\t$$$$$$$$\ $$$$$$$  |$$ |  $$ |
\t\________|\_______/ \__|  \__|
\t    Laravel Deploy Helper
\tRamon Smit <rsmit@daltcore.com>
\t\t    1.1.6\n";
    }
}
