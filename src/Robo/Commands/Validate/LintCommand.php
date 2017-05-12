<?php

namespace Acquia\Blt\Robo\Commands\Validate;

use Acquia\Blt\Robo\BltTasks;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Defines commands in the "validate:*" namespace.
 */
class LintCommand extends BltTasks {

  /**
   * @var \Acquia\Blt\Robo\Filesets\FilesetManager
   */
  protected $filesetManager;

  /**
   * This hook will fire for all commands in this command file.
   *
   * @hook init
   */
  public function initialize() {
    $this->filesetManager = $this->container->get('filesetManager');
  }

  /**
   * Runs a PHP Lint against all code.
   *
   * @command validate:lint
   *
   * @return \Robo\Result
   */
  public function lint() {
    $this->say("Linting PHP files...");
    $task = $this->taskParallelExec()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE);

    $filesets = [
      'files.php.custom.modules',
      'files.php.custom.themes',
      'files.php.tests',
    ];
    foreach ($filesets as $key) {
      $fileset = $this->filesetManager->getFileset($key);
      foreach ($fileset as $file) {
        $task->process("php -l {$file->getRealPath()}");
      }
    }
    $result = $task->run();

    return $result;
  }
}
