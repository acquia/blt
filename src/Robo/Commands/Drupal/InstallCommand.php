<?php

namespace Acquia\Blt\Robo\Commands\Drupal;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\RandomString;
use Acquia\Blt\Robo\Exceptions\BltException;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Finder\Finder;

/**
 * Defines commands in the "drupal:*" namespace.
 */
class InstallCommand extends BltTasks {

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
   * @validateDocrootIsPresent
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   *
   * @todo Add a @validateSettingsFilesArePresent
   */
  public function drupalInstall() {
    $commands = ['internal:drupal:install'];
    $strategy = $this->getConfigValue('cm.strategy');
    if (in_array($strategy, ['core-only', 'config-split'])) {
      $commands[] = 'drupal:config:import';
    }
    $this->invokeCommands($commands);
    $this->setSitePermissions();
  }

  /**
   * Set correct permissions.
   *
   * For directories (755) and files (644) in docroot/sites/[site] (excluding
   * docroot/sites/[site]/files).
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
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
    foreach ($files->getIterator() as $file) {
      $taskFilesystemStack->chmod($file->getRealPath(), 0644);
    }

    $taskFilesystemStack->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE);
    $result = $taskFilesystemStack->run();

    if (!$result->wasSuccessful()) {
      $this->logger->warning('Unable to set permissions for site directories and files.');
    }
  }

  /**
   * Installs Drupal and imports configuration.
   *
   * @command internal:drupal:install
   *
   * @validateDrushConfig
   * @hidden
   *
   * @return \Robo\Result
   *   The `drush site-install` command result.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   * @throws \Robo\Exception\TaskException
   */
  public function install() {

    // Allows for installs to define custom user 0 name.
    if ($this->getConfigValue('drupal.account.name') !== NULL) {
      $username = $this->getConfigValue('drupal.account.name');
    }
    else {
      // Generate a random, valid username.
      // @see \Drupal\user\Plugin\Validation\Constraint\UserNameConstraintValidator
      $username = RandomString::string(10, FALSE,
        function ($string) {
          return !preg_match('/[^\x{80}-\x{F7} a-z0-9@+_.\'-]/i', $string);
        },
        'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!#%^&*()_?/.,+=><'
      );
    }
    /** @var \Acquia\Blt\Robo\Tasks\DrushTask $task */
    $task = $this->taskDrush()
      ->drush("site-install")
      ->arg($this->getConfigValue('project.profile.name'))
      ->rawArg($this->getConfigValue('setup.install-args'))
      ->option('sites-subdir', $this->getConfigValue('site'))
      ->option('site-name', $this->getConfigValue('project.human_name'))
      ->option('site-mail', $this->getConfigValue('drupal.site.mail'))
      ->option('account-name', $username, '=')
      ->option('account-mail', $this->getConfigValue('drupal.account.mail'))
      ->option('locale', $this->getConfigValue('drupal.locale'))
      ->verbose(TRUE)
      ->printOutput(TRUE);

    // Install site from existing config if supported.
    $strategy = $this->getConfigValue('cm.strategy');
    $cm_core_key = 'sync';
    $install_from_config = $this->getConfigValue('cm.core.install_from_config');
    if (in_array($strategy, ['core-only', 'config-split']) && $cm_core_key == 'sync' && $install_from_config) {
      $core_config_file = $this->getConfigValue('docroot') . '/' . $this->getConfigValue("cm.core.dirs.$cm_core_key.path") . '/core.extension.yml';
      if (file_exists($core_config_file)) {
        $task->option('existing-config');
      }
    }

    $result = $task->interactive($this->input()->isInteractive())->run();
    if (!$result->wasSuccessful()) {
      throw new BltException("Failed to install Drupal!");
    }

    return $result;
  }

}
