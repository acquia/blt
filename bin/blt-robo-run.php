<?php

/**
 * @file
 * Execute BLT commands via Robo.
 */

use Acquia\Blt\Robo\Blt;
use Acquia\Blt\Robo\Config\ConfigInitializer;
use Robo\Common\TimeKeeper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

// Start Timer.
$timer = new TimeKeeper();
$timer->start();

// Initialize input and output.
$input = new ArgvInput($_SERVER['argv']);
$output = new ConsoleOutput();

// Write BLT version for debugging.
if ($output->isVerbose()) {
  $output->writeln("<comment>BLT version " . Blt::getVersion() . "</comment>");
}

// Initialize configuration.
// phpcs:ignore
$config_initializer = new ConfigInitializer($repo_root, $input);
$config = $config_initializer->initialize();

// Execute command.
// phpcs:ignore
$blt = new Blt($config, $input, $output, $classLoader);
$status_code = (int) $blt->run($input, $output);

if (!$input->getFirstArgument() || $input->getFirstArgument() == 'list') {
  $output->writeln("<comment>To create custom BLT commands, see https://docs.acquia.com/blt/extending-blt/#adding-a-custom-robo-hook-or-command.</comment>");
  $output->writeln("<comment>To add BLT commands via community plugins, see https://github.com/acquia/blt/blob/10.x/docs/plugins.md.</comment>");
}

// Stop timer.
$timer->stop();
if ($output->isVerbose()) {
  $output->writeln("<comment>" . $timer->formatDuration($timer->elapsed()) . "</comment> total time elapsed.");
}

exit($status_code);
