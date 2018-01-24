<?php

namespace Acquia\Blt\Robo\Commands\Validate;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "validate:*" namespace.
 */
class LintCommand extends BltTasks {

  /**
   * Runs a PHP Lint against all validate.lint.filesets files.
   *
   * @command tests:php:lint
   *
   * @aliases tpl lint validate:lint
   */
  public function lint() {
    $this->say("Linting PHP files...");

    /** @var \Acquia\Blt\Robo\Filesets\FilesetManager $fileset_manager */
    $fileset_manager = $this->getContainer()->get('filesetManager');
    $fileset_ids = $this->getConfigValue('validate.lint.filesets');
    $filesets = $fileset_manager->getFilesets($fileset_ids);

    $command = "php -l '%s'";
    $this->executeCommandAgainstFilesets($filesets, $command, TRUE);
  }

}
