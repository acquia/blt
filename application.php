#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

use Acquia\Blt\Console\Command\ComposerMungeCommand;
use Acquia\Blt\Console\Command\YamlMungeCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new ComposerMungeCommand());
$application->add(new YamlMungeCommand());
$application->run();
