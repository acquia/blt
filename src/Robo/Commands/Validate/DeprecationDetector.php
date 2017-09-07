<?php

namespace Acquia\Blt\Robo\Commands\Validate;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "validate:deprecation*" namespace.
 */
class DeprecationDetector extends BltTasks {

  /**
   * Detects usage of deprecated custom code.
   *
   * @command validate:deprecation
   */
  public function detect() {
    $this->say("Checking for deprecated code...");

    /** @var \Acquia\Blt\Robo\Filesets\FilesetManager $fileset_manager */
    $fileset_manager = $this->getContainer()->get('filesetManager');
    $fileset_ids = $this->getConfigValue('validate.deprecation.filesets');
    $filesets = $fileset_manager->getFilesets($fileset_ids);
    $bin = $this->getConfigValue('composer.bin');
    $command = "'$bin/deprecation-detector' check '%s'";
    $this->executeCommandAgainstFilesets($filesets, $command);
  }

}
