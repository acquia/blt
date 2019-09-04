<?php

namespace Acquia\Blt\Robo\Tasks;

use Acquia\Blt\Robo\Exceptions\BltException;
use Robo\Task\Base\Exec;

/**
 * Overrides Robo's ExecStack to throw errors on failure.
 */
class BltExec extends Exec {

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
