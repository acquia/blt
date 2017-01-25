<?php

// If the BLT binary was used to call Robo, everything is already autoloaded.
// @todo Remove this once Phing is compoletely removed from BLT.
if (!class_exists(\Robo\Robo::class)) {
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
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Acquia\Blt\Robo\Config\YamlConfig;

$input = new ArgvInput($_SERVER['argv']);
$output = new ConsoleOutput();

$config = new DefaultConfig();
$config->extend(new YamlConfig($config->get('blt.root') . '/phing/build.yml', $config->toArray()));
$config->extend(new YamlConfig($config->get('repo.root') . '/blt/project.yml', $config->toArray()));
$config->extend(new YamlConfig($config->get('repo.root') . '/blt/project.local.yml', $config->toArray()));
// @todo realpath() repo.root, docroot, composer.bin, etc.

$blt = new Blt($config, $input, $output);
$status_code = $blt->run($input, $output);
exit($status_code);
