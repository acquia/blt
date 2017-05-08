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
   *
   */
  protected function initialize() {

  }

  /**
   * Invokes an array of Symfony commands.
   *
   * @param array $commands
   *   An array of Symfony commands to invoke. E.g., 'tests:behat'.
   *
   * @return int
   *   The exit code of the command.
   */
  public function invokeCommands(array $commands) {
    foreach ($commands as $command) {
      $returnCode = $this->invokeCommand($command);
      // Return if this is non-zero exit code.
      if ($returnCode) {
        return $returnCode;
      }
    }
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
  public function invokeCommand($command_name) {

     // Skip invocation of disabled commands.
     if ($this->isCommandDisabled($command_name)) {
       return 0;
     }

    /** @var \Robo\Application $application */
    $application = $this->getContainer()->get('application');
    $command = $application->find($command_name);
    $args = [];
    $input = new ArrayInput($args);
    $this->output->writeln("<comment>$command_name ></comment>");
    $returnCode = $command->run($input, $this->output());
    $this->output->writeln("");

    return $returnCode;
  }

  /**
   * Gets an array of commands that have been configured to be disabled.
   *
   * @return array
   *   A flat array of disabled commands.
   */
  protected function getDisabledCommands() {
    $disabled_commands_config = $this->getConfigValue('disable-targets');
    if ($disabled_commands_config) {
      $disabled_commands = ArrayManipulator::flattenMultidimensionalArray($disabled_commands_config, ':');
      return $disabled_commands;
    }
    return [];
  }
  /**
   * Determines if a command has been disabled via disable-targets.
   *
   * @param string $command
   *   The command name.
   *
   * @return bool
   *   TRUE if the command is disabled.
   */
  protected function isCommandDisabled($command) {
    $disabled_commands = $this->getDisabledCommands();
    if (is_array($disabled_commands) && array_key_exists($command, $disabled_commands) && $disabled_commands[$command]) {
      $this->output()->writeln("The $command command is disabled.");
      return TRUE;
    }
    return FALSE;
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
