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
   * @aliases setup:all
   */
  public function setup() {
    $this->say("Setting up local environment...");
    $status_code = $this->invokeCommands([
      'setup:build',
      'setup:hash-salt',
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
   * @interactGenerateSettingsFiles
   *
   * @validateMySqlAvailable
   * @validateDocrootIsPresent
   *
   * @todo Add a @validateSettingsFilesArePresent
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
    $status_code = $this->invokeCommands([
      'setup:behat',
      // setup:composer:install must run prior to setup:settings to ensure that
      // scaffold files are present.
      'setup:composer:install',
      'setup:git-hooks',
      'setup:settings',
      // 'frontend'.
    ]);
    if ($status_code) {
      return $status_code;
    }

    if ($this->getConfig()->has('simplesamlphp') && $this->getConfigValue('simplesamlphp')) {
      $this->taskExec("blt simplesamlphp:build:config")
        ->interactive()
        ->dir($this->getConfigValue('repo.root'))
        ->run();
    }

    $this->invokeHook("post-setup-build");
  }

  /**
   * Installs Composer dependencies.
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

  /**
   * Checks the repo if there is modified files.
   *
   * @return string|null
   *   List of modified files.
   */
  protected $invokeDepth function checkCleanRepo() {
    $clean = $this->taskExec("git status --porcelain")
      ->dir($this->getConfigValue('repo.root'))
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
    return $clean;
  }

  /**
   * Adds a project dependency and ensures configuration management is in sync.
   *
   * @param string $dependency
   *   The composer dependency to be added to composer.json.
   *
   * @command add-dependency
   */
  public function addDependency($dependency) {
    $modified = $this->checkCleanRepo();
    if (!empty($modified)) {
      $this->output()->writeln("<comment>Please note the following files need to be committed or reverted:</comment>");
      $this->output()->writeln("<comment>$modified</comment>");
    }
    else {
      $this->taskExec("composer update $dependency --with-dependencies")
        ->dir($this->getConfigValue('repo.root'))
        ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
        ->run();
      $alias = $this->getConfigValue('drush.alias');
      $this->taskExec("drush @$alias updb")
        ->run();
      $this->taskExec("drush @$alias cex")
        ->run();
      $modified = $this->checkCleanRepo();
      if (!empty($modified)) {
        $this->output()->writeln("<comment>The following files have configuration changes after adding $dependency</comment>");
        $this->output()->writeln("<comment>$modified</comment>");
      }
    }
  }

}
