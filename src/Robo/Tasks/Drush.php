<?php

namespace Acquia\Blt\Robo\Tasks;

use Robo\Task\CommandStack;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\Common\CommandArguments;

/**
 * Runs Drush commands in stack. You can use `stopOnFail()` to point that stack should be terminated on first fail.
 *
 * ``` php
 * $this->taskDrush()
 *     ->drush('updb')
 *     ->drush('cr')
 *     ->run();
 * ```
 */
class Drush extends CommandStack
{
  use CommandArguments;

  /**
   * Site alias to prepend to each command.
   *
   * @var string
   */
  private $alias;

  /**
   * Directory to execute the command from.
   *
   * @var string
   *
   * @see ExecTrait::$workingDirectory
   */
  private $dir;

  /**
   * Site uri to append uri option to each command.
   *
   * @var string
   */
  private $uri;

  /**
   * Assume 'yes' or 'no' to all prompts.
   *
   * @var string|bool
   */
  private $assume;

  /**
   * Attach tty to process for interactive input.
   *
   * @var bool
   *
   * @see ExecTrait::$interactive
   */
  private $passthru;

  /**
   * Indicates if command output should be printed.
   *
   * @var bool
   *
   * @see ExecTrait::isPrinted
   */
  private $logOutput;

  /**
   * Indicates if the command output should be verbose.
   *
   * @var bool
   */
  private $verbose;

  /**
   * @todo Figure out how to fetch config from constructor to avoid this.
   *
   * @var bool
   */
  private $defaultsInitialized;

  /**
   * Runs the given drush command.
   *
   * @param string $command
   *
   * @return $this
   */
  public function drush($command) {
    // @todo Figure out how to fetch config from constructor to avoid this.
    if (!$this->defaultsInitialized) {
      $this->init();
    }

    if ($this->alias) {
      $command = "@{$this->alias} {$command}";
    }

    if (!empty($this->uri)) {
      $this->option('-l', $this->uri);
    }

    if (isset($this->assume) && is_bool($this->assume)) {
      $assumption = $this->assume ? 'yes' : 'no';
      $this->option("--{$assumption}");
    }

    if ($this->verbosityThreshold() >= VerbosityThresholdInterface::VERBOSITY_VERBOSE) {
      $this->verbose(TRUE);
    }

    if ($this->verbose) {
      $this->option('-v');
    }

    // Add in arguments set via option method and clear for next invocation.
    $command = $command . $this->arguments;
    $this->arguments = '';

    return $this->exec($command)
      ->dir($this->dir)
      ->interactive($this->passthru)
      ->printOutput($this->logOutput)
      ->printMetadata(false);
  }

  /**
   * Sets the site alias to be used for each command.
   *
   * @param string $alias
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
      $this->assume = '';
    }
    elseif (is_string($assume)) {
      $this->assume = ($assume === 'yes' || $assume === 'true');
    } else {
      $this->assume = !!$assume;
    }
    return $this;
  }

  /**
   * Attach tty to process for interactive input.
   *
   * @param string|bool $passthru
   *
   * @return $this
   *
   * @see ExecTrait::$interactive
   */
  public function passThru($passthru) {
    if (is_string($passthru)) {
      $this->passthru = ($passthru === 'yes' || $passthru === 'true');
    } else {
      $this->passthru = !!$passthru;
    }
    return $this;
  }

  /**
   * Indicates if command output should be printed.
   *
   * @param string|bool $logOutput
   *
   * @return $this
   *
   * @see ExecTrait::$isPrinted
   */
  public function logOutput($logOutput) {
    if (is_string($logOutput)) {
      $this->logOutput = ($logOutput === 'yes' || $logOutput === 'true');
    } else {
      $this->logOutput = !!$logOutput;
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
    if (is_string($verbose)) {
      $this->verbose = ($verbose === 'yes' || $verbose === 'true');
    } else {
      $this->verbose = !!$verbose;
    }
    return $this;
  }

  /**
   * Sets up drush defaults using config.
   */
  protected function init() {
    $this->executable = $this->getConfig()->get('drush.bin') ?: 'drush';
    if (!$this->dir) {
      $this->dir($this->getConfig()->get('drush.dir'));
    }
    if (!$this->uri) {
      $this->uri = $this->getConfig()->get('drush.uri');
    }
    if (!isset($this->assume)) {
      $this->assume($this->getConfig()->get('drush.assume'));
    }
    if (!isset($this->passthru)) {
      $this->passThru($this->getConfig()->get('drush.passthru'));
    }
    if (!isset($this->logOutput)) {
      $this->logOutput($this->getConfig()->get('drush.logoutput'));
    }
    if (!isset($this->verbose)) {
      $this->verbose($this->getConfig()->get('drush.verbose'));
    }

    $this->defaultsInitialized = TRUE;
  }

}
