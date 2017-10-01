<?php

namespace Acquia\Blt\Robo\Commands\Validate;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "validate:twig*" namespace.
 */
class TwigCommand extends BltTasks {

  /**
   * Executes Twig validator against all validate.twig.filesets files.
   *
   * @command validate:twig
   */
  public function lintFileSets() {
    $this->say("Validating twig syntax for all custom modules and themes...");

    /** @var \Acquia\Blt\Robo\Filesets\FilesetManager $fileset_manager */
    $fileset_manager = $this->getContainer()->get('filesetManager');
    $fileset_ids = $this->getConfigValue('validate.twig.filesets');
    $filesets = $fileset_manager->getFilesets($fileset_ids);
    $bin = $this->getConfigValue('composer.bin');
    $command = "'$bin/blt' 'validate:twig:files' '%s'";
    $this->executeCommandAgainstFilesets($filesets, $command);

    // If exception wasn't thrown, checks were successful.
    $this->say("All Twig files contain valid syntax.");
  }

}
