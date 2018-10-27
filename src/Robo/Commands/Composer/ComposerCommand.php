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
   * @command internal:composer:require
   * @hidden
   *
   * @option dev Whether package should be added to require-dev.
   */
  public function requirePackage($package_name, $package_version, $options = ['dev' => FALSE]) {

    /** @var \Robo\Task\Composer\RequireDependency $task */
    $task = $this->taskComposerRequire()
      ->printOutput(TRUE)
      ->printMetadata(TRUE)
      ->dir($this->getConfigValue('repo.root'))
      ->interactive($this->input()->isInteractive())
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE);
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
          ->printMetadata(TRUE)
          ->dir($this->getConfigValue('repo.root'))
          ->interactive($this->input()->isInteractive())
          ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE);
        ;
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
   * Dumps composer autoloader.
   *
   * @command internal:composer:dumpautoload
   * @hidden
   *
   * @option dev Whether package should be added to require-dev.
   */
  public function dumpAutoload($options = ['optimize' => FALSE]) {

    /** @var \Robo\Task\Composer\DumpAutoload $task */
    $task = $this->taskComposerDumpAutoload()
      ->printOutput(TRUE)
      ->printMetadata(TRUE)
      ->dir($this->getConfigValue('repo.root'))
      ->interactive($this->input()->isInteractive())
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE);
    if ($options['optimize']) {
      $task->optimizeAutoloader(TRUE);
    }
    $result = $task->run();

    if (!$result->wasSuccessful()) {
      $this->logger->error("An error occurred while dumping the composer autoloader");
      $this->say("This is likely due to lock file or vendor directory conflicts .");
      $confirm = $this->confirm("Should BLT attempt to run the nuke script which whill forcibly delete all composer-managed files in order to regenerate the autoloader?");
      if ($confirm) {
        $command = "composer run-script nuke ";
        $command .= "&& composer install";
        $task = $this->taskExec($command)
          ->printOutput(TRUE)
          ->printMetadata(TRUE)
          ->dir($this->getConfigValue('repo.root'))
          ->interactive($this->input()->isInteractive())
          ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE);
        ;
        $result = $task->run();
        if (!$result->wasSuccessful()) {
          throw new BltException("Unable to re-install packages to re-generate autoloader.");
        }
      }
      else {
        // @todo Revert previous file changes.
        throw new BltException("Unable to install {$package_name} package.");
      }
    }

    return $result;
  }

}
