<?php

namespace Acquia\Blt\Robo\Tasks;

use Acquia\Blt\Robo\Exceptions\BltException;
use Robo\Task\Base\ExecStack;

/**
 * Overrides Robo's ExecStack to throw errors on failure.
 */
class BltExecStack extends ExecStack {

  /**
   * Throw errors on task failure.
   *
   * @inheritDoc
   *
   * @throws \Robo\Exception\TaskException
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
