<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

$console = new Application('Tesla AWS Console', '1.0-dev');
$console->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'dev'));
ini_set('display_errors',1);



$command = $app['tesla_aws_console_logmanager_logmove.command'];
!$command ?: $command->registerWithConsole($console);



return $console;
