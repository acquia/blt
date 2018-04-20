<?php

namespace Acquia\Blt\Robo\Commands\Blt;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Defines commands in "blt:init:drush:*" namespace.
 */
class DrushCliCommand extends BltTasks {

  /**
   * Installs the Drush CLI aliases and dependencies for Drush 8 & 9.
   *
   * @command blt:init:drush:shell-config
   *
   * @aliases bidsc
   */
  public function installDrushCliTools() {
    if (!$this->getInspector()->isDrushCliAliasInstalled()) {
      $config_file = $this->getInspector()->getCliConfigFile();
      if (is_null($config_file)) {
        $this->logger->warning("Could not find your CLI configuration file.");
        $this->logger->warning("Looked in ~/.zsh, ~/.bash_profile, ~/.bashrc, ~/.profile, and ~/.functions.");
        $this->logger->warning("Please create one of the aforementioned files, or create the Drush CLI aliases manually.");
        throw new BltException("Could not install Drush shell aliases.");
      }

      $source = $this->getConfigValue('repo.root');
      $this->removeDrush9();
      $this->installDrush8AndDrush9();
      $this->createDrushShellAliases($source);

    }
    else {
      $this->say("<info>The Drush CLI alias and dependencies are already installed.</info>");
    }
  }

  /**
   * Installs and configures both Drush 8 and Drush 9.
   *
   * @command blt:init:drush:binaries
   *
   * @aliases bidb
   */
  public function installDrush8AndDrush9() {
    $this->say('Adding composer vendor bin packages and config...');
    $this->say('Adding drush 9 binaries and dependencies');
    $result = $this->taskExec("composer bin drush-9 require drush/drush:^9.2.1")
      ->printOutput(TRUE)
      ->dir($this->getConfigValue('repo.root'))
      ->run();
    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to install Drush 9");
    }

    $this->say('Adding drush 8 binaries');
    $result = $this->taskExec("composer bin drush-8 require drush/drush:^8.1.16")
      ->printOutput(TRUE)
      ->dir($this->getConfigValue('repo.root'))
      ->run();
    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to install Drush 8");
    }
    $this->copyDrushWrapperIntoProject();
    $this->say("<info>The Drush CLI alias and dependencies were installed.</info>");
  }

  /**
   * Remove drush 9 from vendor and vendor/bin.
   *
   * This prevents re-dispatch to site local drush bin in favor of vendor-bin to
   * support running legacy Drush 8 commands.
   *
   * @command blt:init:drush:remove
   *
   * @aliases bidr
   */
  public function removeDrush9() {
    // Remove vendor/bin/drush to prevent re-dispatch to site local drush bin.
    if (file_exists("vendor/bin/drush")) {
      $this->_remove('vendor/bin/drush');
    }
    if (file_exists("vendor/bin/drush.launcher")) {
      $this->_remove('vendor/bin/drush.launcher');
    }
    // Remove drush 9 package if it exists in vendor dir.
    if (file_exists("vendor/drush")) {
      $this->_remove('vendor/drush');
    }
  }

  /**
   * Creates a new Drush CLI alias in appropriate CLI config file.
   *
   * @param string $repo_root
   *   The repo root on Acquia and local.
   *
   * @command blt:init:drush:shell-alias
   *
   * @aliases bidsa
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function createDrushShellAliases($repo_root) {
    $this->say("Installing <comment>drush8</comment> and <comment>drush9</comment> shell aliases...");
    $command = "bash {$this->getConfigValue('blt.root')}/scripts/blt/drush-config.sh $repo_root";
    $result = $this->taskExec($command)
      ->printMetadata(FALSE)
      ->printOutput(FALSE)
      ->interactive(FALSE)
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to install Drush CLI aliases.");
    }

    $config_file = $this->getInspector()->getCliConfigFile();
    $this->say("<info>Added Drush CLI aliases to $config_file.</info>");
  }

  /**
   * Copies drush.wrapper from BLT template to project.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function copyDrushWrapperIntoProject() {
    $this->say('Adding drush 8 wrapper...');
    $source_path = $this->getConfigValue('blt.root') . '/templates/drush/drush';
    $target_path = $this->getConfigValue('repo.root') . "/vendor-bin/drush-8/drush";

    $result = $this->taskFilesystemStack()
      ->copy($source_path, $target_path, TRUE)
      ->stopOnFail()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Could not copy drush wrapper.");
    }
  }

}
