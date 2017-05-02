<?php

namespace Acquia\Blt\Robo\Tasks;

use Robo\Exception\TaskException;
use Robo\Result;
use Robo\Task\CommandStack;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\Common\CommandArguments;

/**
 * Runs Drush commands in stack. You can use `stopOnFail()` to point that stack
 * should be terminated on first fail.
 *
 * ``` php
 * $this->taskDrush()
 *     ->drush('updb')
 *     ->drush('cr')
 *     ->run();
 * ```
 */
class DrushTask extends CommandStack {
  use CommandArguments {
    option as traitOption;
  }

  /**
   * Site alias to prepend to each command.
   *
   * @var string
   */
  protected $alias;

  /**
   * Directory to execute the command from.
   *
   * @var string
   *
   * @see ExecTrait::$workingDirectory
   */
  protected $dir;

  /**
   * Site uri to append uri option to each command.
   *
   * @var string
   */
  protected $uri;

  /**
   * Assume 'yes' or 'no' to all prompts.
   *
   * @var string|bool
   */
  protected $assume;

  /**
   * Indicates if the command output should be verbose.
   *
   * @var bool
   */
  protected $verbose;

  /**
   * @var bool
   */
  protected $defaultsInitialized;

  /**
   * Additional directory paths to search for drush commands.
   *
   * @var string
   */
  protected $include;

  /**
   * Drush commands to execute when task is run.
   *
   * @var array
   */
  protected $commands;

  /**
   * Options for each drush command.
   *
   * @var array
   */
  protected $options;

  /**
   * Adds the given drush command to a stack.
   *
   * @param string $command
   *   The drush command to execute. Do NOT include "drush" prefix.
   *
   * @return $this
   */
  public function drush($command) {
    // Clear out options associated with previous drush command.
    $this->setOptionsForLastCommand();

    if (!$this->defaultsInitialized) {
      $this->init();
    }

    if (is_array($command)) {
      $command = implode(' ', array_filter($command));
    }

    $this->commands[] = trim($command);
    return $this;
  }

  /**
   * Sets the site alias to be used for each command.
   *
   * @param string $alias
   *   The drush alias to use. Do NOT include "@" prefix.
   *
   * @return $this
   */
  public function alias($alias) {
    $this->alias = $alias;
    return $this;
  }

  /**
   * Sets the site uri to be used for each command.
   *
   * @param string $uri
   *   The URI to pass to drush's --uri option.
   *
   * @return $this
   */
  public function uri($uri) {
    $this->uri = $uri;

    return $this;
  }

  /**
   * Sets the working directory for each command.
   *
   * @param string $dir
   *
   * @return $this
   *
   * @see ExecTrait::$workingDirectory
   */
  public function dir($dir) {
    $this->dir = $dir;
    parent::dir($dir);

    return $this;
  }

  /**
   * Assume 'yes' or 'no' to all prompts.
   *
   * @param string|bool $assume
   *
   * @return $this
   */
  public function assume($assume) {
    if ($assume === "") {
      $this->assume = $assume;
    }
    else {
      $this->assume = $this->mixedToBool($assume);
    }
    return $this;
  }

  /**
   * Indicates if the command output should be verbose.
   *
   * @param string|bool $verbose
   *
   * @return $this
   */
  public function verbose($verbose) {
    $this->verbose = $this->mixedToBool($verbose);
    return $this;
  }

  /**
   * Include additional directory paths to search for drush commands.
   *
   * @param string $path
   *   The filepath for the --include option.
   *
   * @return $this
   */
  public function includePath($path) {
    $this->include = $path;
    return $this;
  }

  /**
   * Sets up drush defaults using config.
   */
  protected function init() {
    $this->executable = $this->getConfig()->get('drush.bin') ?: 'drush';
    if (!isset($this->dir)) {
      $this->dir($this->getConfig()->get('drush.dir'));
    }
    if (!isset($this->uri)) {
      $this->uri = $this->getConfig()->get('drush.uri');
    }
    if (!isset($this->assume)) {
      $this->assume($this->getConfig()->get('drush.assume'));
    }
    if (!isset($this->verbose)) {
      $this->verbose($this->getConfig()->get('drush.verbose'));
    }
    if (!isset($this->alias)) {
      $this->alias($this->getConfig()->get('drush.alias'));
    }

    $this->defaultsInitialized = TRUE;
  }

  /**
   * Helper function to get the boolean equivalent of a variable.
   *
   * @param mixed $mixedVar
   *
   * @return bool
   *   TRUE/FALSE as per PHP's cast to boolean ruleset, with the exception that
   *   a string value not equal to 'yes' or 'true' will evaluate to FALSE.
   */
  protected function mixedToBool($mixedVar) {
    if (is_string($mixedVar)) {
      $boolVar = ($mixedVar === 'yes' || $mixedVar === 'true');
    }
    else {
      $boolVar = (bool) $mixedVar;
    }
    return $boolVar;
  }

  /**
   * Associates arguments with their corresponding drush command.
   */
  protected function setOptionsForLastCommand() {
    if (isset($this->commands)) {
      $numberOfCommands = count($this->commands);
      $correspondingCommand = $numberOfCommands - 1;
      $this->options[$correspondingCommand] = $this->arguments;
      $this->arguments = '';
    }
  }

  /**
   * Set the options to be used for each drush command in the stack.
   */
  protected function setGlobalOptions() {
    if (isset($this->uri) && !empty($this->uri)) {
      $this->option('uri', $this->uri);
    }

    if (isset($this->assume) && is_bool($this->assume)) {
      $assumption = $this->assume ? 'yes' : 'no';
      $this->option($assumption);
    }

    if ($this->verbosityThreshold() >= VerbosityThresholdInterface::VERBOSITY_VERBOSE) {
      $this->verbose(TRUE);
    }

    if ($this->verbose) {
      $this->option('verbose');
    }

    if ($this->include) {
      $this->option('include', $this->include);
    }
  }

  /**
   * Overriding CommandArguments::option to default option separator to '='.
   */
  public function option($option, $value = NULL, $separator = '=') {
    $this->traitOption($option, $value, $separator);
  }

  /**
   * Overriding parent::run() method to remove printTaskInfo() calls.
   */
  public function run() {
    $this->setupExecution();

    if (empty($this->exec)) {
      throw new TaskException($this, 'You must add at least one command');
    }
    if (!$this->stopOnFail) {
      return $this->executeCommand($this->getCommand());
    }

    foreach ($this->exec as $command) {
      $result = $this->executeCommand($command);
      if (!$result->wasSuccessful()) {
        return $result;
      }
    }

    return Result::success($this);
  }

  /**
   * Adds drush comands with their corresponding options to stack.
   */
  protected function setupExecution() {
    $this->setOptionsForLastCommand();
    $this->setGlobalOptions();

    $globalOptions = $this->arguments;

    foreach ($this->commands as $commandNumber => $command) {
      if ($this->alias) {
        $command = "@{$this->alias} {$command}";
      }

      $options = isset($this->options[$commandNumber]) ? $this->options[$commandNumber] : '';

      // Add in global options, as well as those set via option method.
      $command = $command . $options . $globalOptions;

      $this->exec($command)
        ->dir($this->dir);
    }
  }

}
