<?php

/**
 * @file
 * Execute BLT commands via Robo.
 */

use Acquia\Blt\Robo\Config\YamlConfigProcessor;
use Robo\Config\YamlConfigLoader;
use Robo\Robo;
use Symfony\Component\Console\Input\ArgvInput;

// If the BLT binary was used to call Robo, everything is already autoloaded.
// @todo Remove this once Phing is completely removed from BLT.
if (!class_exists(Robo::class)) {
  $blt_root = realpath(__DIR__ . '/../');

  $possible_bin_dirs = [
    $_SERVER['PWD'] . '/vendor/autoload.php',
    $blt_root . '/vendor/autoload.php',
    __DIR__ . '/../../autoload.php',
  ];

  foreach ($possible_bin_dirs as $possible_bin_dir) {
    if (file_exists($possible_bin_dir)) {
      $autoload = require_once $possible_bin_dir;
      break;
    }
  }

  if (!isset($autoload)) {
    print "Unable to find autoloader for blt-robo\n";
  }
}

use Acquia\Blt\Robo\Blt;
use Acquia\Blt\Robo\Config\DefaultConfig;
use Symfony\Component\Console\Output\ConsoleOutput;

$input = new ArgvInput($_SERVER['argv']);
$output = new ConsoleOutput();

$config = new DefaultConfig();
$loader = new YamlConfigLoader();
$processor = new YamlConfigProcessor();
$processor->add($config->export(), 'default');
$processor->extend($loader->load($config->get('blt.root') . '/phing/build.yml'));
$processor->extend($loader->load($config->get('repo.root') . '/blt/project.yml'));
$processor->extend($loader->load($config->get('repo.root') . '/blt/project.local.yml'));
$config->import($processor->export());

// @todo realpath() repo.root, docroot, composer.bin, etc.

$blt = new Blt($config, $input, $output);
$status_code = $blt->run($input, $output);
exit($status_code);
