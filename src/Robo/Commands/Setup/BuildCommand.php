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
   * Install dependencies, builds docroot, installs Drupal.
   *
   * @command setup
   *
   * @validateMySqlAvailable
   */
  public function setup() {
    $this->say("Setting up local environment");
    $status_code = $this->invokeCommands([
      'setup:build',
      'setup:drupal:install',
      'install-alias',
    ]);
    return $status_code;
  }

  /**
   * Installs Drupal and sets correct file/directory permissions.
   *
   * @command setup:drupal:install
   *
   * @validateMySqlAvailable
   */
  public function drupalInstall() {
    $status_code = $this->invokeCommands(['drupal:install']);
    if ($status_code) {
      return $status_code;
    }
    $this->setSitePermissions();

    return $status_code;
  }

  /**
   * Set correct permissions for files and folders in docroot/sites/*.
   */
  protected function setSitePermissions() {
    $taskFilesystemStack = $this->taskFilesystemStack();
    $multisite_dir = $this->getConfigValue('docroot') . '/sites/' . $this->getConfigValue('multisite.name');
    $finder = new Finder();
    $dirs = $finder
      ->in($multisite_dir)
      ->directories()
      ->depth('< 1')
      ->exclude('files');
    foreach ($dirs->getIterator() as $dir) {
      $taskFilesystemStack->chmod($dir->getRealPath(), 0755);
    }
    $files = $finder
      ->in($multisite_dir)
      ->files()
      ->depth('< 1')
      ->exclude('files');
    foreach ($files->getIterator() as $dir) {
      $taskFilesystemStack->chmod($dir->getRealPath(), 0644);
    }

    $taskFilesystemStack->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE);
    $taskFilesystemStack->run();
  }

  /**
   * Generates all required files for a full build.
   *
   * @command setup:build
   */
  public function build() {
    $this->invokeCommands([
      'setup:behat',
      // setup:composer:install must run prior to setup:settings to ensure that
      // scaffold files are present.
      'setup:composer:install',
      'setup:git-hooks',
      'setup:settings',
      // 'frontend'.
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
   * Installs composer dependencies.
   *
   * @command setup:composer:install
   */
  public function composerInstall() {
    $this->taskExec("export COMPOSER_EXIT_ON_PATCH_FAILURE=1; composer install --ansi --no-interaction")
      ->dir($this->getConfigValue('repo.root'))
      ->interactive()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
  }

  /**
   * Installs the 'blt' alias.
   *
   * @command install-alias
   */
  public function installAlias() {
    $this->taskExec("composer run-script blt-alias")
      ->dir($this->getConfigValue('repo.root'))
      ->interactive()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
  }

}
