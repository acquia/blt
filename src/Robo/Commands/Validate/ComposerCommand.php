<?php

namespace Acquia\Blt\Robo\Commands\Validate;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * Defines commands in the "validate:composer" namespace.
 */
class ComposerCommand extends BltTasks {

  /**
   * Validates root composer.json and composer.lock files.
   *
   * @command validate:composer
   */
  public function validate() {
    $this->say("Validating composer.json and composer.lock...");
    $result = $this->taskExecStack()
      ->dir($this->getConfigValue('repo.root'))
      ->exec('composer validate --no-check-all --ansi')
      ->run();
    if (!$result->wasSuccessful()) {
      $this->say($result->getMessage());
      $this->logger->error("composer.lock is invalid.");
      $this->say("If this is simply a matter of the lock file being out of date, you may attempt to use `composer update --lock` to quickly generate a new hash in your lock file.");
      $this->say("Otherwise, `composer update` is likely necessary.");
      throw new BltException("composer.lock is invalid!");
    }
  }

}
