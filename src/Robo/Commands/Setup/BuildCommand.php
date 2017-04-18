<?php

namespace Acquia\Blt\Robo\Commands\Setup;

use Acquia\Blt\Robo\BltTasks;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Finder\Finder;

/**
 * Defines commands in the "setup:build" namespace.
 */
class BuildCommand extends BltTasks {

  /**
   * This hook will fire for all commands in this command file.
   *
   * @hook init
   */
  public function initialize() {
    parent::initialize();
  }

  /**
   * Install dependencies, builds docroot, installs Drupal.
   *
   * @command setup
   */
  public function setup() {
    $this->invokeCommands([
      'setup:build',
      'setup:drupal:install',
      'install-alias',
    ]);
  }

  /**
   * @command setup:drupal:install
   */
  public function drupalInstall() {
    $this->invokeCommands(['drupal:install']);
    $this->setSitePermissions();
  }

  /**
   *
   */
  protected function setSitePermissions() {
    $task = $this->taskFilesystemStack();
    $multisite_dir = $this->getConfigValue('docroot') . '/sites/' . $this->getConfigValue('multisite.name');
    $finder = new Finder();
    $dirs = $finder
      ->in($multisite_dir)
      ->directories()
      ->depth('< 1')
      ->exclude('files');
    foreach ($dirs->getIterator() as $dir) {
      $task->chmod($dir->getRealPath(), 0755);
    }
    $files = $finder
      ->in($multisite_dir)
      ->files()
      ->depth('< 1')
      ->exclude('files');
    foreach ($files->getIterator() as $dir) {
      $task->chmod($dir->getRealPath(), 0644);
    }
    $task->run();
  }

  /**
   * Generates all required files for a full build.
   *
   * @command setup:build
   */
  public function build() {
    $this->invokeCommands([
      'setup:behat',
      // setup:composer:install must run prior to setup:settings to ensure that scaffold files are present.
      'setup:composer:install',
      'setup:git-hooks',
      'setup:settings',
      //'frontend'
    ]);

    if ($this->getConfig()->has('simplesamlphp') && $this->getConfigValue('simplesamlphp')) {
      $this->taskExec("blt simplesamlphp:build:config")
        ->interactive()
        ->dir($this->getConfigValue('repo.root'))
        ->run();
    }

    $this->invokeHook("post-setup-build");
  }

  /**
   * @command setup:composer:install
   */
  public function composerInstall() {
    $this->taskExec("export COMPOSER_EXIT_ON_PATCH_FAILURE=1; composer install --ansi --no-interaction")
      ->interactive()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
  }

  /**
   * @command setup:install-alias
   */
  public function installAlias() {
    $this->taskExec("composer run-script install-alias")
      ->interactive()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
  }

}
