<?php

namespace Acquia\Blt\Robo\Commands\Validate;

use Acquia\Blt\Robo\BltTasks;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Defines commands in the "validate:*" namespace.
 */
class LintCommand extends BltTasks {

  /**
   * Runs a PHP Lint against all code.
   *
   * @command validate:lint
   *
   * @return \Robo\Result
   */
  public function lint() {
    $this->say("Linting PHP files...");
    // @todo Compare performance of taskParallelExec() to using non-parallel
    // execution, and other alternatives. Can we limit concurrency?
    $task = $this->taskParallelExec()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE);

    $filesets = [
      'files.php.custom.modules',
      'files.php.custom.themes',
      'files.php.tests',
    ];
    $fileset_manager = $this->getContainer()->get('filesetManager');
    foreach ($filesets as $key) {
      $fileset = $fileset_manager->getFileset($key);
      foreach ($fileset as $file) {
        $task->process("php -l '{$file->getRealPath()}'");
      }
    }
    $result = $task->run();

    return $result;
  }

}
