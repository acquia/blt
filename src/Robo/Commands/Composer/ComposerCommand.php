<?php

namespace Acquia\Blt\Robo\Commands\Composer;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Defines commands in the "composer:*" namespace.
 */
class ComposerCommand extends BltTasks {

  /**
   * Requires a composer package.
   *
   * @command composer:require
   *
   * @option dev Whether package should be added to require-dev.
   */
  public function requirePackage($package_name, $package_version, $options = ['dev' => FALSE]) {

    /** @var \Robo\Task\Composer\RequireDependency $task */
    $task = $this->taskComposerRequire()
      ->printOutput(TRUE)
      ->dir($this->getConfigValue('repo.root'));
    if ($options['dev']) {
      $task->dev(TRUE);
    }
    if ($package_version) {
      $task->dependency($package_name, $package_version);
    }
    else {
      $task->dependency($package_name);
    }
    $result = $task->run();

    if (!$result->wasSuccessful()) {
      $this->logger->error("An error occurred while requiring {$package_name}.");
      $this->say("This is likely due to an incompatibility with your existing packages.");
      $confirm = $this->confirm("Should BLT attempt to update all of your Composer packages in order to find a compatible version?");
      if ($confirm) {
        $command = "composer require '{$package_name}:{$package_version}' --no-update ";
        if ($options['dev']) {
          $command .= "--dev ";
        }
        $command .= "&& composer update";
        $task = $this->taskExec($command)
          ->printOutput(TRUE)
          ->dir($this->getConfigValue('repo.root'));
        $result = $task->run();
        if (!$result->wasSuccessful()) {
          throw new BltException("Unable to install {$package_name} package.");
        }
      }
      else {
        // @todo Revert previous file changes.
        throw new BltException("Unable to install {$package_name} package.");
      }
    }

    return $result;
  }

  /**
   * Performs a composer install.
   *
   * @param bool $dev
   *   Whether or no dev packages should be installed.
   *
   * @return bool
   *   If the command was successful.
   *
   * @command composer:install
   */
  public function install($dev = TRUE) {
    $tasks = $this->taskExecStack()
      ->stopOnFail()
      ->dir($this->getConfigValue('repo.root'))
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE);

    // Allow an optional cache clear configurable with project.yml.
    if ($this->getConfig()->has('composer.cache-clear') && $this->getConfigValue('composer.cache-clear')) {
      $tasks->exec('composer clear-cache --ansi --no-interaction');
    }

    $command = 'composer install --ansi --no-interaction';

    if (!$dev) {
      $command .= ' --no-dev --optimize-autoloader';
    }

    $tasks->exec($command);

    return $tasks->run()->wasSuccessful();
  }

}
