<?php

namespace Acquia\Blt\Robo;

use Acquia\Blt\Robo\Common\ArrayManipulator;
use Acquia\Blt\Robo\Common\IO;
use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Acquia\Blt\Robo\Inspector\InspectorAwareInterface;
use Acquia\Blt\Robo\Inspector\InspectorAwareTrait;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Robo\Contract\BuilderAwareInterface;
use Robo\Contract\ConfigAwareInterface;
use Robo\Contract\IOAwareInterface;
use Robo\LoadAllTasks;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 */
class BltTasks implements ConfigAwareInterface, InspectorAwareInterface, LoggerAwareInterface, BuilderAwareInterface, IOAwareInterface, ContainerAwareInterface {

  use ContainerAwareTrait;
  use LoadAllTasks;
  use ConfigAwareTrait;
  use InspectorAwareTrait;
  use IO;
  use LoggerAwareTrait;

  /**
   * @var int
   */
  protected $invokeDepth = 0;

  /**
   * Invokes an array of Symfony commands.
   *
   * @param array $commands
   *   An array of Symfony commands to invoke. E.g., 'tests:behat'.
   *
   * @return int
   *   The exit code of the command.
   */
  protected function invokeCommands(array $commands) {
    $this->invokeDepth++;
    foreach ($commands as $command) {
      $returnCode = $this->invokeCommand($command);
      // Return if this is non-zero exit code.
      if ($returnCode) {
        return $returnCode;
      }
    }
    $this->invokeDepth--;
    return $returnCode;
  }

  /**
   * Invokes a single Symfony command.
   *
   * @param string $command_name
   *   The name of the command. E.g., 'tests:behat'.
   *
   * @return int
   *   The exit code of the command.
   */
  protected function invokeCommand($command_name) {
    /** @var \Robo\Application $application */
    $application = $this->getContainer()->get('application');
    $command = $application->find($command_name);
    $args = [];
    $input = new ArrayInput($args);
    $prefix = str_repeat(">", $this->invokeDepth);
    $this->output->writeln("<comment>$prefix $command_name</comment>");
    $returnCode = $command->run($input, $this->output());
    // $this->output->writeln("");.
    return $returnCode;
  }

  /**
   * @param $hook
   */
  protected function invokeHook($hook) {
    if ($this->getConfig()->has("target-hooks.$hook.command")) {
      $this->taskExec($this->getConfigValue("target-hooks.$hook.command"))
        ->dir($this->getConfigValue("target-hooks.$hook.dir"))
        ->interactive()
        ->printOutput(TRUE)
        ->printMetadata(FALSE)
        ->run();
    }
    else {
      $this->say("No commands are defined for $hook. Skipping.");
    }
  }

  /**
   * @param $array
   * @param string $prefix
   * @param int $verbosity
   */
  protected function logConfig($array, $prefix = '', $verbosity = OutputInterface::VERBOSITY_VERY_VERBOSE) {
    if ($this->output()->getVerbosity() >= $verbosity) {
      if ($prefix) {
        $this->output()->writeln("<comment>Configuration for $prefix:</comment>");
        foreach ($array as $key => $value) {
          $array["$prefix.$key"] = $value;
          unset($array[$key]);
        }
      }
      $this->printArrayAsTable($array);
    }
  }

  /**
   * @param $array
   * @param array $headers
   */
  protected function printArrayAsTable(
    $array,
    $headers = array('Property', 'Value')
  ) {
    $table = new Table($this->output);
    $table->setHeaders($headers)
      ->setRows(ArrayManipulator::convertArrayToFlatTextArray($array))
      ->render();
  }

}
