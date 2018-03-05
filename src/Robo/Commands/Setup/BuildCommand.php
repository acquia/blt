<?php

namespace Acquia\Blt\Robo\Commands\Setup;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\RandomString;
use Acquia\Blt\Robo\Exceptions\BltException;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Finder\Finder;

/**
 * Defines commands in the "source:build" namespace.
 */
class BuildCommand extends BltTasks {

  /**
   * Installs Drupal and sets correct file/directory permissions.
   *
   * @command drupal:install
   *
   * @aliases di setup:drupal:install
   *
   * @interactGenerateSettingsFiles
   *
   * @validateDrushConfig
   * @validateMySqlAvailable
   * @validateDocrootIsPresent
   * @executeInVm
   *
   * @todo Add a @validateSettingsFilesArePresent
   */
  public function drupalInstall() {
    $commands = ['internal:drupal:install'];
    $strategy = $this->getConfigValue('cm.strategy');
    if (in_array($strategy, ['config-split', 'features'])) {
      $commands[] = 'drupal:config:import';
    }
    $this->invokeCommands($commands);
    $this->setSitePermissions();
    $this->createDeployId(RandomString::string(8, FALSE, NULL, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!#%^&*()_?/.,+=><'));
  }

  /**
   * Creates deployment_identifier file.
   */
  protected function createDeployId($id) {
    $this->taskExecStack()->exec("echo '$id' > deployment_identifier")
      ->dir($this->getConfigValue('repo.root'))
      ->stopOnFail()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
  }

  /**
   * Set correct permissions for files and folders in docroot/sites/*.
   */
  protected function setSitePermissions() {
    $taskFilesystemStack = $this->taskFilesystemStack();
    $multisite_dir = $this->getConfigValue('docroot') . '/sites/' . $this->getConfigValue('site');
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
    $result = $taskFilesystemStack->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to set permissions for site directories.");
    }
  }

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
      'tests:behat:init:config',
      // source:build:composer must run prior to blt:init:settings to ensure
      // that scaffold files are present.
      'source:build:composer',
      'blt:init:git-hooks',
      'blt:init:settings',
      'source:build:frontend',
    ]);

    if ($this->getConfig()->has('simplesamlphp') && $this->getConfigValue('simplesamlphp')) {
      $this->invokeCommand('source:build:simplesamlphp-config');
    }

    $this->invokeHook("post-setup-build");
  }

  /**
   * Installs Composer dependencies.
   *
   * @command source:build:composer
   * @aliases sbc setup:composer:install
   */
  public function composerInstall() {
    $result = $this->taskExec("export COMPOSER_EXIT_ON_PATCH_FAILURE=1; composer install --ansi --no-interaction")
      ->dir($this->getConfigValue('repo.root'))
      ->interactive($this->input()->isInteractive())
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    return $result;
  }

}
