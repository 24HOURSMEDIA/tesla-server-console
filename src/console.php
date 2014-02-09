<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;


$console = new Application('Tesla AWS Console', '1.0-dev');
$console->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'dev'));

// required because some command use routes to controllers
$app = require __DIR__ . '/../src/app.php';
require __DIR__ . '/../config/prod.php';
require __DIR__ . '/../src/controllers.php';
$app->boot();

$command = $app['tesla_aws_console_logmanager_logmove.command'];
!$command ? : $command->registerWithConsole($console);

$command = $app['tesla_server_console_collect_stats.command'];
!$command ?: $command->registerWithConsole($console);



return $console;
