<?php

namespace Acquia\Blt\Robo\Commands\Composer;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * Defines commands in the "composer:*" namespace.
 */
class ComposerCommand extends BltTasks {

  /**
   * Requires a composer package.
   *
   * @command composer:require
   */
  public function requirePackage($package_name, $package_version) {

    $task = "composer require '{$package_name}''";
    if ($package_version) {
      $task = "composer require '{$package_name}:{$package_version}'";
    }

    $result = $this->taskExec($task)
      ->printOutput(TRUE)
      ->dir($this->getConfigValue('repo.root'))
      ->run();

    if (!$result->wasSuccessful()) {
      $this->logger->error("An error occurred while requiring {$package_name}.");
      $this->say("This is likely due to an incompatibility with your existing packages.");
      $confirm = $this->confirm("Should BLT attempt to update all of your Composer packages in order to find a compatible version?");
      if ($confirm) {
        $result = $this->taskExec("composer require '{$package_name}:{$package_version}' --no-update && composer update")
          ->printOutput(TRUE)
          ->dir($this->getConfigValue('repo.root'))
          ->run();
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

}
