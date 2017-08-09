<?php

namespace Acquia\Blt\Robo\Commands\Vm;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
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
  protected $vmConfigDir;
  protected $vmConfigFile;
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
    $this->projectDrushAliasesFile = $this->getConfigValue('repo.root') . '/drush/site-aliases/aliases.drushrc.php';
    $this->projectDrupalVmVagrantfile = $this->getConfigValue('repo.root') . '/Vagrantfile';
    $this->projectDrupalVmConfigFile = $this->getConfigValue('vm.config');
    $this->vmDir = dirname($this->projectDrupalVmConfigFile);
    $this->vmConfigDir = str_replace($this->getConfigValue('repo.root') . DIRECTORY_SEPARATOR, '', $this->vmDir);
    $path_parts = explode(DIRECTORY_SEPARATOR, $this->projectDrupalVmConfigFile);
    $this->vmConfigFile = array_pop($path_parts);
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

    if (!$options['no-boot'] && !$this->getInspector()->isDrupalVmBooted()) {
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
        ->copy($this->defaultDrushAliasesFile, $this->projectDrushAliasesFile, TRUE)
        ->run();
      $this->say("Your Drupal VM instance has been obliterated.");
      $this->say("Please run `blt vm` to create a new one.");
    }
  }

  /**
   * Installs and configures default Drupal VM instance.
   */
  protected function install() {
    if (!$this->isDrupalVmRequired()) {
      $this->requireDrupalVm();
    }
    $this->config();
  }

  /**
   * Generates default configuration for Drupal VM.
   *
   * @command vm:config
   */
  public function config() {
    $this->say("Generating default configuration for Drupal VM...");

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
      ->copy($this->defaultDrupalVmConfigFile, $this->projectDrupalVmConfigFile, TRUE)
      ->copy($this->defaultDrupalVmVagrantfile, $this->projectDrupalVmVagrantfile, TRUE)
      ->stopOnFail()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    $config = clone $this->getConfig();

    $config->set('drupalvm.config.dir', $this->vmConfigDir);
    $config->expandFileProperties($this->projectDrupalVmVagrantfile);

    // Generate a Random IP address for the new VM.
    $random_local_ip = "192.168." . rand(0, 255) . '.' . rand(0, 255);
    $config->set('random.ip', $random_local_ip);
    $config->expandFileProperties($this->projectDrupalVmConfigFile);

    $vm_config = Yaml::parse(file_get_contents($this->projectDrupalVmConfigFile));
    $this->validateConfig($vm_config);

    $this->say("");
    $this->say("<info>BLT has created default configuration for your Drupal VM!</info>");
    $this->say(" * The configuration file is <comment>{$this->projectDrupalVmConfigFile}</comment>.");

    $this->say(" * To customize the VM, follow the Quick Start Guide in Drupal VM's README:");
    $this->say("   <comment>https://github.com/geerlingguy/drupal-vm#quick-start-guide</comment>");

    $this->say(" * To run drush commands against the VM, use the <comment>@{$this->drupalVmAlias}</comment> alias.");
    $this->say(" * From now on, please use vagrant commands to manage your virtual machine.");
    $this->say("");
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
    $contents['vm']['enable'] = TRUE;
    $yaml = Yaml::dump($contents, 3, 2);
    file_put_contents($filename, $yaml);

    $this->say("<comment>$filename</comment> was modified.");
    $this->say("BLT will now use <comment>@{$contents['drush']['default_alias']}</comment> as the default drush alias for all commands on this machine.");
  }

  /**
   * Boots a Drupal VM.
   */
  protected function boot() {
    $this->checkRequirements();
    $confirm = $this->confirm("Do you want to boot Drupal VM?", TRUE);
    if ($confirm) {
      $this->say("In future, run <comment>vagrant up</comment> to boot the VM.");
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
          if (!$result->wasSuccessful()) {
            throw new BltException("Unable to provision virtual machine. This is likely due to an issue with your Drupal VM configuration and not BLT itself.");
          }
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

    $this->say("Adding geerlingguy/drupal-vm:{$this->drupalVmVersionConstraint} to composer.json's require-dev array...");
    $package_options = [
      'package_name' => 'geerlingguy/drupal-vm',
      'package_version' => $this->drupalVmVersionConstraint,
    ];
    return $this->invokeCommand('composer:require', $package_options);
  }

  /**
   * Determines if Drupal VM is currently in composer.json's require-dev.
   *
   * @return bool
   *   TRUE if it is present already and matches version constraint.
   */
  protected function isDrupalVmRequired() {
    $composer_json = json_decode($this->getConfigValue('repo.root') . '/composer.json', TRUE);
    return !empty($composer_json['require-dev']['geerlingguy/drupal-vm'])
      && $composer_json['require-dev']['geerlingguy/drupal-vm'] == $this->drupalVmVersionConstraint;
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
      throw new BltException("Drupal VM requirements are missing.");
    }
    else {
      $this->installVagrantPlugin('vagrant-hostsupdater');
    }
  }

  /**
   * Validates Drupal VM Config.
   *
   * @param string $config
   *   Drupal VM config from box/config.yml.
   */
  protected function validateConfig($config) {
    if (strstr($config['vagrant_machine_name'], '_')) {
      $this->logger->warning("vagrant_machine_namefor should not contain an underscore.");
    }
  }

}
