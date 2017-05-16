<?php

/**
 * @file
 * Execute BLT commands via Robo.
 */

use Acquia\Blt\Robo\Config\YamlConfigProcessor;
use Robo\Config\YamlConfigLoader;
use Symfony\Component\Console\Input\ArgvInput;
use Acquia\Blt\Robo\Blt;
use Acquia\Blt\Robo\Config\DefaultConfig;
use Symfony\Component\Console\Output\ConsoleOutput;

$input = new ArgvInput($_SERVER['argv']);
$output = new ConsoleOutput();

$config = new DefaultConfig();
$loader = new YamlConfigLoader();
$processor = new YamlConfigProcessor();
$processor->add($config->export());
$processor->extend($loader->load($config->get('blt.root') . '/config/build.yml'));
$processor->extend($loader->load($config->get('repo.root') . '/blt/project.yml'));
$processor->extend($loader->load($config->get('repo.root') . '/blt/project.local.yml'));
// @todo Load multisite-specific config if multisite is specified.
$config->import($processor->export());
$config->populateHelperConfig();

$blt = new Blt($config, $input, $output);
$status_code = (int) $blt->run($input, $output);
exit($status_code);
