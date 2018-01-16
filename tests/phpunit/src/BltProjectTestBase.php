<?php

namespace Acquia\Blt\Tests;

use Acquia\Blt\Robo\Config\ConfigInitializer;
use function getenv;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Process\Process;

/**
 * Class BltProjectTestBase.
 *
 * Base class for all tests that are executed within a blt project.
 */
abstract class BltProjectTestBase extends \PHPUnit_Framework_TestCase {

  protected $sandboxInstance;
  protected $sites = [];
  /**
   * @var \Acquia\Blt\Robo\Config\DefaultConfig
   */
  protected $config = [];

  public function setUp() {
    parent::setUp();
    $bootstrapper = new BltPhpunitBootstrapper();
    $bootstrapper->createSandboxInstance();
    $this->sandboxInstance = $bootstrapper->getSandboxInstance();
    $config_initializer = new ConfigInitializer($this->sandboxInstance, new ArrayInput([]));
    $this->config = $config_initializer->initialize();
  }

  /**
   * @param $command
   *
   * @return mixed
   */
  protected function drush($command) {
    chdir($this->config->get('docroot'));
    $drush_bin = $this->sandboxInstance . '/vendor/bin/drush';
    $command_string = "$drush_bin $command --format=json --no-interaction --no-ansi";
    $process = new Process($command_string);
    $process->setTimeout(null);
    if (getenv('BLT_PRINT_COMMAND_OUTPUT')) {
      $process->run(function ($type, $buffer) {
        echo $buffer;
      });
    }
    else {
      $process->run();
    }
    $output = $process->getOutput();

    return json_decode($output, TRUE);
  }

  /**
   * @param $command
   *
   * @return Process
   */
  protected function blt($command) {
    chdir($this->config->get('repo.root'));
    $blt_bin = $this->sandboxInstance . '/vendor/bin/blt';
    $command_string = "$blt_bin $command --no-interaction --no-ansi";
    $process = new Process($command_string);
    $process->setTimeout(null);
    if (getenv('BLT_PRINT_COMMAND_OUTPUT')) {
      $process->run(function ($type, $buffer) {
        echo $buffer;
      });
    }
    else {
      $process->run();
    }

    return $process;
  }

}
