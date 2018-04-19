<?php

namespace Acquia\Blt\Robo\Commands\Blt;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * Defines commands for installing the Drush shell alias and respective executables.
 */
class DrushCliCommand extends BltTasks {

  /**
   * Installs the Drush CLI aliases and depedencies for Drush8/9 command line usage.
   *
   * @command blt:init:drush:cli-tools
   *
   * @aliases drushtools install-drush-cli-tools
   */
  public function installDrushCliTools() {
    if (!$this->getInspector()->isDrushCliAliasInstalled()) {
      $config_file = $this->getInspector()->getCliConfigFile();
      if (is_null($config_file)) {
        $this->logger->warning("Could not find your CLI configuration file.");
        $this->logger->warning("Looked in ~/.zsh, ~/.bash_profile, ~/.bashrc, ~/.profile, and ~/.functions.");
        if (!$created) {
          $this->logger->warning("Please create one of the aforementioned files, or create the Drush CLI aliases manually.");
        }
      }
      else {
          $source = $this->getConfigValue('repo.root');
          $this->redispatchToVendorBin();
          $this->setupComposerVendorBin();
          $this->createNewDrushCliAlias($source);
      }
    }

    else {
      $this->say("<info>The Drush CLI alias and dependencies are already installed.</info>");
    }
  }


  /**
   * Install and configure composer bin plugin. 
   *
   * @command setup:composer:bin-plugin
   *
   * @aliases composer-bin-plugin
   */
  public function setupComposerBinPlugin() {

      $this->say('Adding composer vendor bin packages and config...');
      $this->say('Adding drush 9 binaries and dependencies');
       $result = $this->taskExec("composer bin drush-9 require drush/drush theofidry/composer-inheritance-plugin")
          ->printOutput(TRUE)
          ->dir($this->getConfigValue('repo.root'))
          ->run();
      $this->say('Adding drush 8 binaries');
       $result = $this->taskExec("composer bin drush-8 require drush/drush:8.1.16")
          ->printOutput(TRUE)
          ->dir($this->getConfigValue('repo.root'))
          ->run();

  }

  /**
   * Prevent re-dispatch to site local drush bin in favor of vendor-bin to 
   * support running legacy Drush 8 commands. 
   *
   * @command blt:drush:redispatch
   *
   * @aliases redispatch
   */

  public function redispatchToVendorBin() {
  
    // Rename vendor/bin/drush to prevent re-dispatch to site local drush bin.
    $this->_rename('vendor/bin/drush', 'vendor/bin/drush.bak', TRUE);
    if (file_exists("vendor/bin/drush.launcher")) {
      $this->_rename('vendor/bin/drush.launcher',
        'vendor/bin/drush.launcher.bak', TRUE);
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
   * @aliases drushcli install-drush-cli-alias
   */
  public function createNewDrushCliAlias($repo_root) {
    $this->say("Installing <comment>Drush CLI</comment> alias...");
    $config_file = $this->getInspector()->getCliConfigFile();
    $scr = $this->getConfigValue('blt.root');

    if (is_null($config_file)) {
      $this->logger->error("Could not install drush cli alias. No profile found. Tried ~/.zshrc, ~/.bashrc, ~/.bash_profile, ~/.profile, and ~/.functions.");
    }
    else {
      $command = "bash $scr/scripts/blt/drush-config.sh $repo_root";
      $result = $this->taskExec($command)
        ->printMetadata(FALSE)
        ->printOutput(FALSE)
        ->interactive(FALSE)
        ->run();

      if (!$result->wasSuccessful()) {
        throw new BltException("Unable to install Drush CLI aliases.");
      }

      $this->say("<info>Added Drush CLI aliases to $config_file.</info>");
    }
  }

}





