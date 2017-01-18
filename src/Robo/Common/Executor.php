<?php

namespace Acquia\Blt\Robo\Common;

use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Symfony\Component\Console\Output\OutputInterface;
use Robo\Contract\ConfigAwareInterface;
use Robo\Contract\IOAwareInterface;
use Symfony\Component\Process\Process;

class Executor implements ConfigAwareInterface, IOAwareInterface {

  use ConfigAwareTrait;
  use IO;

  /**
   * @param $command
   *
   * @return \Symfony\Component\Process\Process
   */
  public function executeDrush($command) {
    $bin = $this->getConfigValue('composer.bin');
    return $this->executeCommand("$bin/drush $command", $this->getConfigValue('docroot'), false);
  }

  /**
   * @param string $command
   *
   * @return Process
   */
  public function executeCommand($command, $cwd = null, $display_output = true, $interactive = false, $mustRun = true)
  {
    if ($this->output()->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
      $this->output()->writeln("<comment>Executing command: $command</comment>");
    }

    $timeout = 10800;
    $env = [
        'COMPOSER_PROCESS_TIMEOUT' => $timeout
      ] + $_ENV;
    $process = new Process($command, $cwd, $env, null, $timeout);
    $process->setTty($interactive);
    $method = $mustRun ? 'mustRun' : 'run';
    if ($display_output) {
      $process->$method(function ($type, $buffer) {
        print $buffer;
      });
    } else {
      $process->$method();
    }

    return $process;
  }

}
