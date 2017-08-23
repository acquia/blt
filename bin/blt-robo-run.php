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
$config = init_config($repo_root, $input);

// Execute command.
$blt = new Blt($config, $input, $output);
$status_code = (int) $blt->run($input, $output);

// Stop timer.
$timer->stop();
if ($output->isVerbose()) {
  $output->writeln("<comment>" . $timer->formatDuration($timer->elapsed()) . "</comment> total time elapsed.");
}

exit($status_code);

/**
 * @param $repo_root
 * @param $input
 *
 * @return \Acquia\Blt\Robo\Config\DefaultConfig
 */
function init_config($repo_root, $input) {
  $config = new DefaultConfig($repo_root);
  $processor = init_config_processor($config, $input);
  $config->import($processor->export());
  $config->populateHelperConfig();

  return $config;
}

/**
 * @param $config
 * @param $input
 *
 * @return \Acquia\Blt\Robo\Config\YamlConfigProcessor
 */
function init_config_processor($config, $input) {
  $loader = new YamlConfigLoader();
  $processor = new YamlConfigProcessor();
  $processor->add($config->export());
  $processor->extend($loader->load($config->get('blt.root') . '/config/build.yml'));
  $processor->extend($loader->load($config->get('repo.root') . '/blt/project.yml'));
  $processor->extend($loader->load($config->get('repo.root') . '/blt/project.local.yml'));

  if ($input->hasArgument('environment')) {
    $processor->extend($loader->load($config->get('repo.root') . '/blt/' . $input->getArgument('environment') . '.yml'));
  }

  return $processor;
}