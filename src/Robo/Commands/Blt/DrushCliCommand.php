<?php

namespace Acquia\Blt\Robo\Commands\Blt;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
use Robo\Contract\VerbosityThresholdInterface;

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
          $this->setupComposerBinPlugin();
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
       $result = $this->taskExec("composer bin drush-9 require drush/drush")
          ->printOutput(TRUE)
          ->dir($this->getConfigValue('repo.root'))
          ->run();

      $this->say('Adding drush 8 binaries');
       $result = $this->taskExec("composer bin drush-8 require drush/drush:8.1.16")
          ->printOutput(TRUE)
          ->dir($this->getConfigValue('repo.root'))
          ->run();

      $this->say('Adding drush 8 wrapper');
      //$source_path = $this->getConfigValue('blt.root') . '/templates/drush/drush' ;
      //$target_path = $this->getConfigValue('repo.root') . "/vendor-bin/drush-8/drush" ;

      $result = $this->taskFilesystemStack()
        ->copy($this->getConfigValue('blt.root') . '/template/drush/drush', $this->getConfigValue('repo.root') . '/vendor-bin/drush-8/drush', TRUE)
        ->stopOnFail()
        ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
        ->run();

      if (!$result->wasSuccessful()) {
        throw new BltException("Could not copy drush wrapper.");
      }

     /* // Fail silently if target file already exists.
      if (!$this->getFileSystem()->exists($target_path)) {
        $this->getFileSystem()->rename($source_path, $target_path);
        $this->say("Drush 8 wrapper created");
      }
       else {
          $this->logger->warning("Error creating drush 8 wrapper. Create manually and/or review documentation via drush8 topic core-global-options.");
        }*/

      $this->say("<info>The Drush CLI alias and dependencies are already installed.</info>");
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


    // Remove vendor/bin/drush to prevent re-dispatch to site local drush bin.

    if (file_exists("vendor/bin/drush") ) {
      $this->_remove('vendor/bin/drush');
    }

    if (file_exists("vendor/bin/drush.launcher") ) {
      $this->_remove('vendor/bin/drush.launcher');
    }

    // Remove drush 9 package if it exists in vendor dir    
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





