<?php

namespace Acquia\Blt\Robo;

use Acquia\Blt\Robo\Common\ArrayManipulator;
use Acquia\Blt\Robo\Common\IO;
use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Acquia\Blt\Robo\Inspector\InspectorAwareInterface;
use Acquia\Blt\Robo\Inspector\InspectorAwareTrait;
use Acquia\Blt\Robo\Tasks\LoadTasks;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Robo\Contract\BuilderAwareInterface;
use Robo\Contract\ConfigAwareInterface;
use Robo\Contract\IOAwareInterface;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\LoadAllTasks;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base class for BLT Robo commands.
 */
class BltTasks implements ConfigAwareInterface, InspectorAwareInterface, LoggerAwareInterface, BuilderAwareInterface, IOAwareInterface, ContainerAwareInterface {

  use ContainerAwareTrait;
  use LoadAllTasks;
  use ConfigAwareTrait;
  use InspectorAwareTrait;
  use IO;
  use LoggerAwareTrait;
  use LoadTasks;

  /**
   * The depth of command invocations, used by invokeCommands().
   *
   * E.g., this would be 1 if invokeCommands() called a method that itself
   * called invokeCommands().
   *
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
    $returnCode = 0;
    foreach ($commands as $key => $value) {
      if (is_numeric($key)) {
        $command = $value;
        $args = [];
      }
      else {
        $command = $key;
        $args = $value;
      }
      $returnCode = $this->invokeCommand($command, $args);
      // Return if this is non-zero exit code.
      if ($returnCode) {
        return $returnCode;
      }
    }
    return $returnCode;
  }

  /**
   * Invokes a single Symfony command.
   *
   * @param string $command_name
   *   The name of the command. E.g., 'tests:behat'.
   * @param array $args
   *   An array of arguments to pass to the command.
   *
   * @return int
   *   The exit code of the command.
   */
  protected function invokeCommand($command_name, array $args = []) {
    $this->invokeDepth++;

    // Skip invocation of disabled commands.
    if ($this->isCommandDisabled($command_name)) {
      return 0;
    }

    /** @var \Robo\Application $application */
    $application = $this->getContainer()->get('application');
    $command = $application->find($command_name);
    $input = new ArrayInput($args);
    $prefix = str_repeat(">", $this->invokeDepth);
    $this->output->writeln("<comment>$prefix $command_name</comment>");
    $returnCode = $command->run($input, $this->output());
    $this->invokeDepth--;

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
   * Invokes a given 'target-hooks' hook, typically defined in project.yml.
   *
   * @param string $hook
   *   The hook name.
   *
   * @return \Robo\Result|int
   */
  protected function invokeHook($hook) {
    if ($this->getConfig()->has("target-hooks.$hook.command")
      && $this->getConfigValue("target-hooks.$hook.command")) {
      $this->say("Executing $hook target hook...");
      $result = $this->taskExecStack()
        ->exec($this->getConfigValue("target-hooks.$hook.command"))
        ->dir($this->getConfigValue("target-hooks.$hook.dir"))
        ->detectInteractive()
        ->printOutput(TRUE)
        ->printMetadata(FALSE)
        ->stopOnFail()
        ->run();

      return $result;
    }
    else {
      $this->say("Skipped $hook target hook. No hook is defined.");

      return 0;
    }
  }

  /**
   * Installs a vagrant plugin if it is not already installed.
   *
   * @param string $plugin
   *   The vagrant plugin name.
   */
  protected function installVagrantPlugin($plugin) {
    if (!$this->getInspector()->isVagrantPluginInstalled($plugin)) {
      $this->logger->warning("The $plugin plugin is not installed! Attempting to install it...");
      $this->taskExec("vagrant plugin install $plugin")->run();
    }
  }

  /**
   * Executes a command inside of Drupal VM.
   *
   * @param string $command
   *   The command to execute.
   *
   * @return \Robo\Result
   *   The command result.
   */
  protected function executeCommandInDrupalVm($command) {
    $this->say("Executing command <comment>$command</comment> inside of Drupal VM...");
    $this->installVagrantPlugin('vagrant-exec');
    $result = $this->taskExecStack()
      ->exec("vagrant exec --tty '$command'")
      ->dir($this->getConfigValue('repo.root'))
      ->detectInteractive()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    return $result;
  }

  /**
   * Executes a given command against multiple filesets.
   *
   * @param \Symfony\Component\Finder\Finder[] $filesets
   *
   * @param string $command
   *   The command to execute. The command should contain '%s', which will be
   *   replaced with the file path of each file in the filesets.
   *
   * @return int
   *   The exit code of the last executed command.
   */
  protected function executeCommandAgainstFilesets(array $filesets, $command) {
    $result = 0;
    foreach ($filesets as $fileset_id => $fileset) {
      if (!is_null($fileset) && iterator_count($fileset)) {
        $this->say("Iterating over fileset $fileset_id...");
        $files = iterator_to_array($fileset);
        $result = $this->executeCommandAgainstFiles($files, $command);
        if (!$result->wasSuccessful()) {
          return $result->getExitCode();
        }
      }
      else {
        $this->logger->info("No files were found in fileset $fileset_id. Skipped.");
      }
    }

    return $result->getExitCode();
  }

  /**
   * Executes a given command against an array of files.
   *
   * @param array $files
   *   A flat array of absolute file paths.
   *
   * @param string $command
   *   The command to execute. The command should contain '%s', which will be
   *   replaced with the file path of each file in the fileset.
   *
   * @return \Robo\Result
   *   The result of the command execution.
   */
  protected function executeCommandAgainstFiles($files, $command) {
    // @todo Add option to permit parallel execution.
    $task = $this->taskExecStack()
      ->printMetadata(FALSE);

    foreach ($files as $file) {
      $full_command = sprintf($command, $file);
      $task->exec($full_command);
    }

    $task->printMetadata(FALSE);
    $result = $task->run();

    return $result;
  }

  /**
   * Writes a particular configuration key's value to the log.
   *
   * @param array $array
   *   The configuration.
   * @param string $prefix
   *   A prefix to add to each row in the configuration.
   * @param int $verbosity
   *   The verbosity level at which to display the logged message.
   */
  protected function logConfig(array $array, $prefix = '', $verbosity = OutputInterface::VERBOSITY_VERY_VERBOSE) {
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
   * Writes an array to the screen as a formatted table.
   *
   * @param array $array
   *   The unformatted array.
   * @param array $headers
   *   The headers for the array. Defaults to ['Property','Value'].
   */
  protected function printArrayAsTable(
    array $array,
    array $headers = ['Property', 'Value']
  ) {
    $table = new Table($this->output);
    $table->setHeaders($headers)
      ->setRows(ArrayManipulator::convertArrayToFlatTextArray($array))
      ->render();
  }

}
