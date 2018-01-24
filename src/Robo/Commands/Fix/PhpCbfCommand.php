<?php

namespace Acquia\Blt\Robo\Commands\Fix;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * Defines commands in the "source:fix:php-standards*" namespace.
 */
class PhpCbfCommand extends BltTasks {

  /**
   * Fixes and beautifies custom code according to Drupal Coding standards.
   *
   * @command source:fix:php-standards
   *
   * @aliases sfps fix phpcbf fix:phpcbf
   */
  public function phpcbfFileSet() {
    $this->say('Fixing and beautifying code...');

    $bin = $this->getConfigValue('composer.bin');
    $result = $this->taskExec("$bin/phpcbf")
      ->dir($this->getConfigValue('repo.root'))
      ->run();

    $exit_code = $result->getExitCode();
    // - 0 indicates that no fixable errors were found.
    // - 1 indicates that all fixable errors were fixed correctly.
    // - 2 indicates that PHPCBF failed to fix some of the fixable errors.
    // - 3 is used for general script execution errors.
    switch ($exit_code) {
      case 0:
        $this->say('<info>No fixable errors were found, and so nothing was fixed.</info>');
        return 0;

      case 1:
        $this->say('<comment>Please note that exit code 1 does not indicate an error for PHPCBF.</comment>');
        $this->say('<info>All fixable errors were fixed correctly. There may still be errors that could not be fixed automatically.</info>');
        return 0;

      case 2:
        $this->logger->warning('PHPCBF failed to fix some of the fixable errors it found.');
        return $exit_code;

      default:
        throw new BltException("PHPCBF failed.");
    }
  }

}
