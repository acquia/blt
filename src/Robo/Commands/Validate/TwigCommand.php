<?php

namespace Acquia\Blt\Robo\Commands\Validate;

use Acquia\Blt\Robo\BltTasks;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Defines commands in the "validate:twig*" namespace.
 */
class TwigCommand extends BltTasks {

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
   * @command validate:twig
   */
  public function lint() {
    $this->say("Validating twig syntax for all custom modules and themes...");

    $filesets_to_lint = $this->getConfigValue('validate.twig.filesets');
    $bin = $this->getConfigValue('composer.bin');
    $command = "'$bin/twig-lint' lint --only-print-errors '%s'";
    $result = $this->executeCommandAgainstFilesets($filesets_to_lint, $command);

    return $result;
  }



}
