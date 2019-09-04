<?php

namespace Acquia\Blt\Robo\Tasks;

use Acquia\Blt\Robo\Exceptions\BltException;
use Robo\Task\File\Write;

/**
 * Overrides Robo's Write to throw errors on failure.
 */
class BltWrite extends Write {

  /**
   * Throw errors on task failure.
   *
   * @inheritDoc
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function run() {
    $result = parent::run();
    if (!$result->wasSuccessful()) {
      throw new BltException("Task failed with exit code {$result->getExitCode()}. See log output above for details.", $result->getExitCode());
    }
    return $result;
  }

}
