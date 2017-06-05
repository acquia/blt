<?php

namespace Acquia\Blt\Robo\Commands\Fix;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "fix:phpcbf*" namespace.
 */
class PhpCbfCommand extends BltTasks {

  protected $standard;

  /**
   * This hook will fire for all commands in this command file.
   *
   * @hook init
   */
  public function initialize() {
    $this->standard = $this->getConfigValue('repo.root') . '/vendor/drupal/coder/coder_sniffer/Drupal/ruleset.xml';
  }

  /**
   * Fixes and beautifies custom code according to Drupal Coding standards.
   *
   * @command fix:phpcbf
   */
  public function phpcbfFileSet() {
    $this->say('Fixing and beautifying code...');

    /** @var \Acquia\Blt\Robo\Filesets\FilesetManager $fileset_manager */
    $fileset_manager = $this->getContainer()->get('filesetManager');
    $fileset_ids = $this->getConfigValue('phpcbf.filesets');
    $filesets = $fileset_manager->getFilesets($fileset_ids);

    $bin = $this->getConfigValue('composer.bin');
    $command = "'$bin/phpcbf' --standard='{$this->standard}' '%s'";
    $this->executeCommandAgainstFilesets($filesets, $command);
  }

}
