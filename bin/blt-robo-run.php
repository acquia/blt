<?php

/**
 * @file
 * Execute BLT commands via Robo.
 */

use Acquia\Blt\Robo\Blt;
use Acquia\Blt\Robo\Config\DefaultConfig;
use Acquia\Blt\Robo\Config\YamlConfigProcessor;
use Robo\Common\TimeKeeper;
use Consolidation\Config\Loader\YamlConfigLoader;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

// Start Timer.
$timer = new TimeKeeper();
$timer->start();

// Initialize input and output.
$input = new ArgvInput($_SERVER['argv']);
$output = new ConsoleOutput();

// Initialize configuration.
$config = new DefaultConfig($repo_root);
$loader = new YamlConfigLoader();
$processor = new YamlConfigProcessor();
$processor->add($config->export());
$processor->extend($loader->load($config->get('blt.root') . '/config/build.yml'));
$processor->extend($loader->load($config->get('repo.root') . '/blt/project.yml'));
$processor->extend($loader->load($config->get('repo.root') . '/blt/project.local.yml'));

if ($input->hasArgument('environment')) {
  $processor->extend($loader->load($config->get('repo.root') . '/blt/' . $input->getArgument('environment') . '.yml'));
}

$config->import($processor->export());
$config->populateHelperConfig();

// Execute command.
$blt = new Blt($config, $input, $output);
$status_code = (int) $blt->run($input, $output);

// Stop timer.
$timer->stop();
if ($output->isVerbose()) {
  $output->writeln("<comment>" . $timer->formatDuration($timer->elapsed()) . "</comment> total time elapsed.");
}

exit($status_code);
