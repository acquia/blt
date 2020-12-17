<?php

namespace Acquia\Blt\Robo\Commands\Source;

use Acquia\Blt\Robo\BltTasks;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Defines commands in the "source:build" namespace.
 */
class BuildCommand extends BltTasks {

  /**
   * Generates all required files for a full build.
   *
   * @command source:build
   *
   * @aliases sb setup:build
   *
   * @interactConfigIdentical
   */
  public function build() {
    $this->invokeCommands([
      // source:build:composer must run prior to blt:init:settings to ensure
      // that scaffold files are present.
      'source:build:composer',
      'blt:init:git-hooks',
      'blt:init:settings',
      'source:build:frontend',
    ]);

    $this->invokeHook("post-setup-build");
  }

  /**
   * Installs Composer dependencies.
   *
   * @command source:build:composer
   * @aliases sbc setup:composer:install
   */
  public function composerInstall() {
    $result = $this->taskExec(
      (DIRECTORY_SEPARATOR == "\\") ? 'set' : 'export' .
        " COMPOSER_EXIT_ON_PATCH_FAILURE=1 && composer install --ansi --no-interaction"
    )
      ->dir($this->getConfigValue('repo.root'))
      ->interactive($this->input()->isInteractive())
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    return $result;
  }

}
