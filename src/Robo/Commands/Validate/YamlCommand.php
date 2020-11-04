<?php

namespace Acquia\Blt\Robo\Commands\Validate;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "validate:yaml:lint:all*" namespace.
 */
class YamlCommand extends BltTasks {

  /**
   * Executes YAML validator against all validate.yaml.filesets files.
   *
   * @command validate:yaml
   */
  public function lintFileSets() {
    $this->say("Validating yaml syntax for all custom modules and exported config...");

    /** @var \Acquia\Blt\Robo\Filesets\FilesetManager $fileset_manager */
    $fileset_manager = $this->getContainer()->get('filesetManager');
    $fileset_ids = $this->getConfigValue('validate.yaml.filesets');
    $filesets = $fileset_manager->getFilesets($fileset_ids, TRUE);
    $bin = $this->getConfigValue('composer.bin');
    $command = "'$bin/yaml-cli' lint '%s'";
    $this->executeCommandAgainstFilesets($filesets, $command, TRUE);
  }

  /**
   * Executes YAML validator against files, if in validate.yaml.filesets.
   *
   * @param string $file_list
   *   A list of files to scan, separated by \n.
   *
   * @command validate:yaml:lint:files
   */
  public function lintFileList($file_list) {
    $this->say("Linting YAML files...");

    $files = explode("\n", $file_list);

    /** @var \Acquia\Blt\Robo\Filesets\FilesetManager $fileset_manager */
    $fileset_manager = $this->getContainer()->get('filesetManager');
    $fileset_ids = $this->getConfigValue('validate.yaml.filesets');
    $filesets = $fileset_manager->getFilesets($fileset_ids, TRUE);

    $bin = $this->getConfigValue('composer.bin');
    $command = "'$bin/yaml-cli' lint '%s'";
    foreach ($filesets as $fileset_id => $fileset) {
      $filesets[$fileset_id] = $fileset_manager->filterFilesByFileset($files, $fileset);
    }

    $this->executeCommandAgainstFilesets($filesets, $command, TRUE);
  }

}
