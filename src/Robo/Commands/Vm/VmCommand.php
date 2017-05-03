<?php

namespace Acquia\Blt\Robo\Commands\Vm;

use Acquia\Blt\Robo\BltTasks;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Defines commands in the "vm" namespace.
 */
class VmCommand extends BltTasks {

  protected $drupalVmAlias;
  protected $drupalVmVersionConstraint;
  protected $defaultDrupalVmDrushAliasesFile;
  protected $defaultDrupalVmConfigFile;
  protected $defaultDrupalVmVagrantfile;
  protected $defaultDrushAliasesFile;
  protected $projectDrushAliasesFile;
  protected $projectDrupalVmConfigFile;
  protected $projectDrupalVmVagrantfile;
  protected $vmDir;

  /**
   * This hook will fire for all commands in this command file.
   *
   * @hook init
   */
  public function initialize() {
    $this->drupalVmAlias = $this->getConfigValue('project.machine_name') . '.local';
    $this->drupalVmVersionConstraint = '~4.3';
    $this->defaultDrupalVmDrushAliasesFile = $this->getConfigValue('blt.root') . '/scripts/drupal-vm/drupal-vm.aliases.drushrc.php';
    $this->defaultDrupalVmConfigFile = $this->getConfigValue('blt.root') . '/scripts/drupal-vm/config.yml';
    $this->defaultDrupalVmVagrantfile = $this->getConfigValue('blt.root') . '/scripts/drupal-vm/Vagrantfile';
    $this->defaultDrushAliasesFile = $this->getConfigValue('blt.root') . '/template/drush/site-aliases/aliases.drushrc.php';
    $this->projectDrupalVmConfigFile = $this->getConfigValue('repo.root') . '/box/config.yml';
    $this->projectDrushAliasesFile = $this->getConfigValue('repo.root') . '/drush/site-aliases/aliases.drushrc.php';
    $this->projectDrupalVmVagrantfile = $this->getConfigValue('repo.root') . '/Vagrantfile';
    $this->vmDir = $this->getConfigValue('repo.root') . '/box';
  }

  /**
   * Configures and boots a Drupal VM.
   *
   * @command vm
   *
   * @aliases vm:all
   *
   * @options no-boot
   */
  public function vm($options = ['no-boot' => FALSE]) {
    if (!$this->getInspector()->isDrupalVmConfigPresent()) {
      $confirm = $this->confirm("Drupal VM is not currently installed. Install it now? ", TRUE);
      if ($confirm) {
        $this->install();
      }
      else {
        return FALSE;
      }
    }

    // @todo Check that VM is properly configured. E.g., all config files exist
    // and geerlingguy/drupalvm is in composer.lock.
    if (!$this->getInspector()->isDrupalVmLocallyInitialized()) {
      $this->localInitialize();
    }
    else {
      $this->say("Drupal VM is already configured. In future, please use vagrant commands to interact directly with the VM.");
    }

    if (!$options['no-boot']) {
      return $this->boot();
    }
  }

  /**
   * Destroys existing VM and all related configuration.
   *
   * @command vm:nuke
   */
  public function nuke() {
    $confirm = $this->confirm("This will destroy your VM, and delete all associated configuration. Continue?");
    if ($confirm) {
      $this->taskExecStack()
        ->exec("vagrant destroy")
        ->dir($this->getConfigValue('repo.root'))
        ->printOutput(TRUE)
        ->stopOnFail()
        ->run();
      $this->taskFilesystemStack()
        ->remove($this->projectDrupalVmConfigFile)
        ->remove($this->projectDrupalVmVagrantfile)
        // @todo More surgically remove drush.default_alias and drush.aliases.local values from this file
        // rather than overwriting it.
        ->remove($this->getConfigValue('repo.root') . '/blt/project.local.yml')
        ->copy($this->defaultDrushAliasesFile, $this->projectDrushAliasesFile)
        ->run();
      $this->say("Your Drupal VM instance has been obliterated.");
      $this->say("Please run `blt vm` to create a new one.");
    }
  }

  /**
   * Installs and configures default Drupal VM instance.
   */
  protected function install() {
    $this->requireDrupalVm();
    $this->config();
  }

  /**
   * Generates default configuration for Drupal VM.
   *
   * @command vm:config
   */
  public function config() {

    $this->say("Generating default configuration for Drupal VM");

    $this->logger->info("Adding a drush alias for the new VM...");
    // @todo Concat only if it has not already been done.
    $this->taskConcat([
      $this->projectDrushAliasesFile,
      $this->defaultDrupalVmDrushAliasesFile,
    ])
      ->to($this->projectDrushAliasesFile)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
    $this->getConfig()->expandFileProperties($this->projectDrushAliasesFile);

    $this->logger->info("Creating configuration files for Drupal VM...");

    $this->taskFilesystemStack()
      ->mkdir($this->vmDir)
      ->copy($this->defaultDrupalVmConfigFile, $this->projectDrupalVmConfigFile)
      ->copy($this->defaultDrupalVmVagrantfile, $this->projectDrupalVmVagrantfile)
      ->stopOnFail()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    // @todo Ensure that hostname does not contain underscores.
    $this->getConfig()->expandFileProperties($this->projectDrupalVmConfigFile);

    $this->say("<info>BLT has created default configuration for your Drupal VM.</info>");
    $this->say("The configuration file is <comment>{$this->projectDrupalVmConfigFile}</comment>.");

    $this->say("To customize the VM, follow the Quick Start Guide in Drupal VM's README:");
    $this->say("<comment>https://github.com/geerlingguy/drupal-vm#quick-start-guide</comment>");

    $this->say("To run drush commands against the VM, use the <comment>@{$this->drupalVmAlias}</comment> alias.");
    $this->yell("From now on, please use vagrant commands to manage your virtual machine.");
  }

  /**
   * Configures local machine to use Drupal VM as default env for BLT commands.
   */
  protected function localInitialize() {
    if (!$this->getInspector()->isBltLocalConfigFilePresent()) {
      $this->invokeCommands(['setup:settings']);
    }

    $filename = $this->getConfigValue('blt.config-files.local');
    $this->logger->info("Updating $filename");

    $contents = Yaml::parse(file_get_contents($filename));
    $contents['drush']['default_alias'] = $this->drupalVmAlias;
    $contents['drush']['aliases']['local'] = $this->drupalVmAlias;
    $contents['behat']['launch-selenium'] = FALSE;
    $contents['behat']['launch-phantomjs'] = TRUE;
    $yaml = Yaml::dump($contents, 3, 2);
    file_put_contents($filename, $yaml);

    $this->say("$filename was modified.");
    $this->say("BLT will now use <comment>@{$contents['drush']['default_alias']}</comment> as the default drush alias for all commands.");
  }

  /**
   * Boots a Drupal VM.
   */
  protected function boot() {
    $this->checkRequirements();
    $confirm = $this->confirm("Do you want to boot Drupal VM?", TRUE);
    if ($confirm) {
      $this->say("In future, run <comment>vagrant up</comment> to boot the VM");
      $result = $this->taskExec("vagrant up")
        ->dir($this->getConfigValue('repo.root'))
        ->printOutput(TRUE)
        ->run();
      if (!$result->wasSuccessful()) {
        $this->logger->error("Drupal VM failed to boot. Read Drupal VM's previous output for more information.");
        $confirm = $this->confirm("Do you want to try to re-provision the VM? Sometimes this works.", TRUE);
        if ($confirm) {
          $result = $this->taskExec("vagrant provision")
            ->dir($this->getConfigValue('repo.root'))
            ->printOutput(TRUE)
            ->run();
        }
      }
      else {
        $this->yell("Drupal VM booted successfully. Please use vagrant commands to interact with your VM from now on.");
      }
      return $result;
    }
  }

  /**
   * Installs geerlingguy/drupalvm via Composer.
   *
   * @throws \Exception
   */
  protected function requireDrupalVm() {
    $this->say("Adding geerlingguy/drupal-vm:{$this->drupalVmVersionConstraint} to composer.json's require-dev array.");
    $result = $this->taskExec("composer require --dev geerlingguy/drupal-vm:{$this->drupalVmVersionConstraint}")
      ->dir($this->getConfigValue('repo.root'))
      ->printOutput(TRUE)
      ->run();

    if (!$result->wasSuccessful()) {
      $this->logger->error("An error occurred while requiring geerlingguy/drupal-vm.");
      $this->say("This is likely due to an incompatibility with your existing packages.");
      $confirm = $this->confirm("Should BLT attempt to update all of your Composer packages in order to find a compatible version?");
      if ($confirm) {
        $result = $this->taskExec("composer require --dev geerlingguy/drupal-vm:{$this->drupalVmVersionConstraint} --no-update && composer update")
          ->dir($this->getConfigValue('repo.root'))
          ->printOutput(TRUE)
          ->run();
      }
      else {
        // @todo revert previous file chanages.
        throw new \Exception("Unable to install Drupal VM.");
      }
    }

    return $result;
  }

  /**
   * Checks local system for Drupal VM requirements.
   *
   * Verifies that vagrant and its required plugins are installed.
   */
  protected function checkRequirements() {
    if (!$this->getInspector()->commandExists("vagrant")) {
      $this->logger->error("Vagrant is not installed.");
      $this->say("Please install all dependencies for Drupal VM by following the Quickstart Guide:");
      $this->say("https://github.com/geerlingguy/drupal-vm#quick-start-guide");
      throw new \Exception("Drupal VM requirements are missing.");
    }
    else {
      $this->installVagrantPlugin('vagrant-hostsupdater');
      $this->installVagrantPlugin('vagrant-exec');
    }
  }

}
