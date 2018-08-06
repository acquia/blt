<?php

namespace Acquia\Blt\Robo;

use Acquia\Blt\Robo\Common\ArrayManipulator;
use Acquia\Blt\Robo\Common\IO;
use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Acquia\Blt\Robo\Config\ConfigInitializer;
use Acquia\Blt\Robo\Exceptions\BltException;
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
use Symfony\Component\Console\Input\ArrayInput;

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
   *   An array of Symfony commands to invoke, e.g., 'tests:behat:run'.
   */
  protected function invokeCommands(array $commands) {
    foreach ($commands as $key => $value) {
      if (is_numeric($key)) {
        $command = $value;
        $args = [];
      }
      else {
        $command = $key;
        $args = $value;
      }
      $this->invokeCommand($command, $args);
    }
  }

  /**
   * Invokes a single Symfony command.
   *
   * @param string $command_name
   *   The name of the command, e.g., 'tests:behat:run'.
   * @param array $args
   *   An array of arguments to pass to the command.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function invokeCommand($command_name, array $args = []) {
    $this->invokeDepth++;

    if (!$this->isCommandDisabled($command_name)) {
      /** @var \Acquia\Blt\Robo\Application $application */
      $application = $this->getContainer()->get('application');
      $command = $application->find($command_name);

      $input = new ArrayInput($args);
      $input->setInteractive($this->input()->isInteractive());
      $prefix = str_repeat(">", $this->invokeDepth);
      $this->output->writeln("<comment>$prefix $command_name</comment>");
      $exit_code = $application->runCommand($command, $input, $this->output());
      $this->invokeDepth--;

      // The application will catch any exceptions thrown in the executed
      // command. We must check the exit code and throw our own exception. This
      // obviates the need to check the exit code of every invoked command.
      if ($exit_code) {
        throw new BltException("Command `$command_name {$input->__toString()}` exited with code $exit_code.");
      }
    }
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
      $this->logger->warning("The $command command is disabled.");
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Invokes a given 'command-hooks' hook, typically defined in blt.yml.
   *
   * @param string $hook
   *   The hook name.
   *
   * @return int
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function invokeHook($hook) {
    if ($this->getConfig()->has("command-hooks.$hook.command")
      && $this->getConfigValue("command-hooks.$hook.command")) {
      $this->say("Executing $hook target hook...");
      $result = $this->taskExecStack()
        ->exec($this->getConfigValue("command-hooks.$hook.command"))
        ->dir($this->getConfigValue("command-hooks.$hook.dir"))
        ->interactive($this->input()->isInteractive())
        ->printOutput(TRUE)
        ->printMetadata(TRUE)
        ->stopOnFail()
        ->run();

      if (!$result->wasSuccessful()) {
        throw new BltException("Executing target-hook $hook failed.");
      }
    }
    else {
      $this->logger->info("Skipped $hook target hook. No hook is defined.");

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
   * Executes a given command against multiple filesets.
   *
   * @param \Symfony\Component\Finder\Finder[] $filesets
   *
   * @param string $command
   *   The command to execute. The command should contain '%s', which will be
   *   replaced with the file path of each file in the filesets.
   * @param bool $parallel
   *   Indicates whether commands should be run in parallel or sequentially.
   *   Defaults to FALSE.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function executeCommandAgainstFilesets(array $filesets, $command, $parallel = FALSE) {
    $passed = TRUE;
    $failed_filesets = [];
    foreach ($filesets as $fileset_id => $fileset) {
      if (!is_null($fileset) && iterator_count($fileset)) {
        $this->say("Iterating over fileset $fileset_id...");
        $files = iterator_to_array($fileset);
        $result = $this->executeCommandAgainstFiles($files, $command, $parallel);
        if (!$result->wasSuccessful()) {
          // We iterate over all filesets before throwing an exception. This
          // will, for instance, allow a user to see all PHPCS violations in
          // output before the command exits.
          $passed = FALSE;
          $failed_filesets[] = $fileset_id;
        }
      }
      else {
        $this->logger->info("No files were found in fileset $fileset_id. Skipped.");
      }
    }

    if (!$passed) {
      throw new BltException("Executing `$command` against fileset(s) " . implode(', ', $failed_filesets) . " returned a non-zero exit code.`");
    }
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
   * @param bool $parallel
   *   Indicates whether commands should be run in parallel or sequentially.
   *   Defaults to FALSE.
   *
   * @return \Robo\Result
   *   The result of the command execution.
   */
  protected function executeCommandAgainstFiles($files, $command, $parallel = FALSE) {
    if ($parallel) {
      return $this->executeCommandAgainstFilesInParallel($files, $command);
    }
    else {
      return $this->executeCommandAgainstFilesProcedurally($files, $command);
    }
  }

  /**
   * @param $files
   * @param $command
   *
   * @return \Robo\Result
   */
  protected function executeCommandAgainstFilesInParallel($files, $command) {
    $task = $this->taskParallelExec()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE);

    $chunk_size = 20;
    $chunks = array_chunk((array) $files, $chunk_size);
    foreach ($chunks as $chunk) {
      foreach ($chunk as $file) {
        $full_command = sprintf($command, $file);
        $task->process($full_command);
      }

      $result = $task->run();

      if (!$result->wasSuccessful()) {
        $this->say($result->getMessage());
        return $result;
      }
    }
    return $result;
  }

  /**
   * @param $files
   * @param $command
   *
   * @return null|\Robo\Result
   */
  protected function executeCommandAgainstFilesProcedurally($files, $command) {
    $task = $this->taskExecStack()
      ->printMetadata(FALSE)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE);

    foreach ($files as $file) {
      $full_command = sprintf($command, $file);
      $task->exec($full_command);
    }

    $result = $task->run();

    if (!$result->wasSuccessful()) {
      $this->say($result->getMessage());
    }

    return $result;
  }

  /**
   * Sets multisite context by settings site-specific config values.
   *
   * @param string $site_name
   *   The name of a multisite, e.g., if docroot/sites/example.com is the site,
   *   $site_name would be example.com.
   */
  public function switchSiteContext($site_name) {
    $this->logger->debug("Switching site context to <comment>$site_name</comment>.");
    $config_initializer = new ConfigInitializer($this->getConfigValue('repo.root'), $this->input());
    $config_initializer->setSite($site_name);
    $new_config = $config_initializer->initialize();

    // Replaces config.
    $this->getConfig()->import($new_config->export());
  }

}
