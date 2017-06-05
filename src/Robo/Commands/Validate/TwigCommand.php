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
   *
   * @return int
   *   The exit code of the last executed command in
   *   $this->executeCommandAgainstFilesets().
   */
  public function lintFileSets() {
    $this->say("Validating twig syntax for all custom modules and themes...");

    /** @var \Acquia\Blt\Robo\Filesets\FilesetManager $fileset_manager */
    $fileset_manager = $this->getContainer()->get('filesetManager');
    $fileset_ids = $this->getConfigValue('validate.twig.filesets');
    $filesets = $fileset_manager->getFilesets($fileset_ids);
    $bin = $this->getConfigValue('composer.bin');
    $command = "'$bin/twig-lint' lint --only-print-errors '%s'";
    $this->executeCommandAgainstFilesets($filesets, $command, TRUE);
  }

  /**
   * Executes Twig validator against a list of files, if in twig.filesets.
   *
   * @command validate:twig:files
   *
   * @param string $file_list
   *   A list of files to scan, separated by \n.
   */
  public function lintFileList($file_list) {
    $this->say("Linting twig files...");

    $files = explode("\n", $file_list);

    /** @var \Acquia\Blt\Robo\Filesets\FilesetManager $fileset_manager */
    $fileset_manager = $this->getContainer()->get('filesetManager');
    $fileset_ids = $this->getConfigValue('validate.twig.filesets');
    $filesets = $fileset_manager->getFilesets($fileset_ids);

    $bin = $this->getConfigValue('composer.bin');
    $command = "'$bin/twig-lint' lint --only-print-errors '%s'";
    foreach ($filesets as $fileset_id => $fileset) {
      $filesets[$fileset_id] = $fileset_manager->filterFilesByFileset($files, $fileset);
    }

    $this->executeCommandAgainstFilesets($filesets, $command);
  }

}
