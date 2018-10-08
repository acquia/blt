<?php

namespace Acquia\Blt\Robo\Commands\Vm;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\ArrayManipulator;
use Acquia\Blt\Robo\Exceptions\BltException;
use function file_exists;
use function file_get_contents;
use Grasmash\YamlExpander\Expander;
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
    $this->drupalVmVersionConstraint = '^4.8';
    $this->defaultDrupalVmDrushAliasesFile = $this->getConfigValue('blt.root') . '/scripts/drupal-vm/drupal-vm.site.yml';
    $this->defaultDrupalVmConfigFile = $this->getConfigValue('blt.root') . '/scripts/drupal-vm/config.yml';
    $this->defaultDrupalVmVagrantfile = $this->getConfigValue('blt.root') . '/scripts/drupal-vm/Vagrantfile';
    $this->projectDrushAliasesFile = $this->getConfigValue('drush.alias-dir') . '/' . $this->getConfigValue('project.machine_name') . '.site.yml';
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
   * @command recipes:drupalvm:init
   *
   * @aliases rdi vm
   *
   * @options no-boot
   *
   * @throws \Exception
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

    // @todo Check that VM is properly configured, e.g., all config files exist
    // and geerlingguy/drupalvm is in composer.lock.
    if (!$this->getInspector()->isDrupalVmLocallyInitialized()) {
      $this->localInitialize();
    }
    else {
      $this->say("Drupal VM is already configured. In the future, please use vagrant commands to interact directly with the VM.");
    }

    if (!$options['no-boot'] && !$this->getInspector()->isDrupalVmBooted()) {
      return $this->boot();
    }
  }

  /**
   * Destroys existing VM and all related configuration.
   *
   * @command recipes:drupalvm:destroy
   * @aliases rdd vm:nuke
   * @throws \Exception
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
        ->remove($this->getConfigValue('blt.config-files.local'))
        ->run();
      $this->say("Your Drupal VM instance has been obliterated.");
      $this->say("Please run `blt vm` to create a new one.");
    }
  }

  /**
   * Installs and configures default Drupal VM instance.
   * @throws \Exception
   */
  protected function install() {
    if (!$this->isDrupalVmRequired()) {
      $this->requireDrupalVm();
    }
    $this->config();
  }

  /**
   * Generates default configuration for Drupal VM.
   */
  protected function config() {
    $this->say("Generating default configuration for Drupal VM...");

    $this->createDrushAlias();
    $this->createConfigFiles();
    $this->customizeConfigFiles();

    $vm_config = Yaml::parse(file_get_contents($this->projectDrupalVmConfigFile));
    $this->validateConfig($vm_config);

    $this->say("");
    $this->say("<info>BLT has created default configuration for your Drupal VM!</info>");
    $this->say(" * The configuration file is <comment>{$this->projectDrupalVmConfigFile}</comment>.");
    $this->say(" * Be sure to commit this file as well as <comment>Vagrantfile</comment>.");

    $this->say(" * To customize the VM, follow the Quick Start Guide in Drupal VM's README:");
    $this->say("   <comment>https://github.com/geerlingguy/drupal-vm#quick-start-guide</comment>");
    $this->say(" * To run blt or drush commands against your VM, must SSH into the VM via <comment>vagrant ssh</comment>.");
    $this->say(" * From now on, please use vagrant commands to manage your virtual machine on this computer.");
    $this->say("");
  }

  /**
   * Configures local machine to use Drupal VM as default env for BLT commands.
   */
  protected function localInitialize() {
    if (!$this->getInspector()->isBltLocalConfigFilePresent()) {
      $this->invokeCommands(['blt:init:settings']);
    }

    $filename = $this->getConfigValue('blt.config-files.local');
    $this->logger->info("Updating $filename");

    $contents = Yaml::parse(file_get_contents($filename));
    $contents['vm']['enable'] = TRUE;
    $yaml = Yaml::dump($contents, 3, 2);
    file_put_contents($filename, $yaml);

    $this->say("<comment>$filename</comment> was modified.");
  }

  /**
   * Boots a Drupal VM.
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function boot() {
    $this->checkRequirements();
    $this->yell(" * We have configured your new Drupal VM to use PHP 7.1 If you would like to change this, edit box/config.yml.");
    $confirm = $this->confirm("Do you want to boot Drupal VM?", TRUE);
    if ($confirm) {
      $this->say("In the future, run <comment>vagrant up</comment> to boot the VM.");
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
        $this->yell("Drupal VM booted successfully.");
        $this->say(" * To run blt or drush commands against your VM, must SSH into the VM via <comment>vagrant ssh</comment>.");
        $this->say(" * From now on, please use vagrant commands to manage your virtual machine on this computer.");
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
      ['dev' => TRUE],
    ];
    return $this->invokeCommand('internal:composer:require', $package_options);
  }

  /**
   * Determines if Drupal VM is currently in composer.json's require-dev.
   *
   * @return bool
   *   TRUE if it is present already and matches version constraint.
   */
  protected function isDrupalVmRequired() {
    $composer_json = json_decode(file_get_contents($this->getConfigValue('repo.root') . '/composer.json'), TRUE);
    return !empty($composer_json['require-dev']['geerlingguy/drupal-vm'])
      && $composer_json['require-dev']['geerlingguy/drupal-vm'] == $this->drupalVmVersionConstraint;
  }

  /**
   * Checks local system for Drupal VM requirements.
   *
   * Verifies that vagrant and its required plugins are installed.
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
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
      $this->logger->warning("vagrant_machine_name {$config['vagrant_machine_name']} should not contain an underscore.");
    }
  }

  /**
   * Sets the Drupal VM base box.
   *
   * @param \Acquia\Blt\Robo\Config\BltConfig $config
   */
  protected function setBaseBox($config) {
    $base_box = $this->askChoice(
      "Which base box would you like to use?",
      [
        'geerlingguy/ubuntu1604',
        'beet/box',
      ],
      0);

    switch ($base_box) {
      case 'beet/box':
        $config->set('workspace', '/beetbox/workspace/{{ php_version }}');
        $config->set('installed_extras', [
          'drush',
          'nodejs',
          'xdebug',
          'selenium',
        ]);
        break;

      case 'geerlingguy/ubuntu1604':
        $config->set('workspace', '/root');
        $config->set('installed_extras', [
          'adminer',
          'selenium',
          'drush',
          'mailhog',
          'memcached',
          'nodejs',
          'solr',
          'xdebug',
        ]);
        break;
    }

    $config->set('base_box', $base_box);
  }

  /**
   * Modifies the default configuration file.
   */
  protected function customizeConfigFiles() {
    /** @var \Acquia\Blt\Robo\Config\BltConfig $config */
    $config = clone $this->getConfig();

    $config->set('drupalvm.config.dir', $this->vmConfigDir);
    $config->expandFileProperties($this->projectDrupalVmVagrantfile);

    // Generate a Random IP address for the new VM.
    $random_local_ip = "192.168." . rand(3, 254) . '.' . rand(3, 254);
    $config->set('random.ip', $random_local_ip);

    $this->setBaseBox($config);

    $config->expandFileProperties($this->projectDrupalVmConfigFile);
  }

  /**
   * Creates the default configuration file.
   */
  protected function createConfigFiles() {
    $this->logger->info("Creating configuration files for Drupal VM...");

    $this->taskFilesystemStack()
      ->mkdir($this->vmDir)
      ->copy($this->defaultDrupalVmConfigFile, $this->projectDrupalVmConfigFile,
        TRUE)
      ->copy($this->defaultDrupalVmVagrantfile,
        $this->projectDrupalVmVagrantfile, TRUE)
      ->stopOnFail()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
  }

  /**
   * Creates a new drush alias record.
   */
  protected function createDrushAlias() {
    $this->logger->info("Adding a drush alias for the new VM...");
    if (!file_exists($this->projectDrushAliasesFile)) {
      $new_aliases = Expander::parse(file_get_contents($this->defaultDrupalVmDrushAliasesFile), $this->getConfig()->export());
    }
    else {
      $project_drush_aliases = Expander::parse(file_get_contents($this->projectDrushAliasesFile), $this->getConfig()->export());
      $default_drupal_vm_drush_aliases = Expander::parse(file_get_contents($this->defaultDrupalVmDrushAliasesFile), $this->getConfig()->export());
      $new_aliases = ArrayManipulator::arrayMergeRecursiveDistinct($project_drush_aliases, $default_drupal_vm_drush_aliases);
    }

    file_put_contents($this->projectDrushAliasesFile, Yaml::dump($new_aliases));
  }

}
