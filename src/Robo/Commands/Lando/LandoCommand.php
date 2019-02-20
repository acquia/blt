<?php

namespace Acquia\Blt\Robo\Commands\Lando;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\ArrayManipulator;
use Acquia\Blt\Robo\Exceptions\BltException;
use function file_exists;
use function file_get_contents;
use Grasmash\YamlExpander\Expander;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Defines commands in the "lando" namespace.
 */
class LandoCommand extends BltTasks {
  protected $landoDrushAliasesFile;
  protected $defaultLocalSettingsFile;
  protected $defaultLandoFile;
  protected $projectDrushAliasesFile;
  protected $projectLandoConfigFile;
  protected $projectLandoFile;
  protected $projectLocalSettingsFile;

  /**
   * This hook will fire for all commands in this command file.
   *
   * @hook init
   */
  public function initialize() {
    $this->landoDrushAliasesFile = $this->getConfigValue('blt.root') . '/scripts/lando/lando.site.yml';
    $this->defaultLandoFile = $this->getConfigValue('blt.root') . '/scripts/lando/landofile.yml';
    $this->defaultLocalSettingsFile = $this->getConfigValue('blt.root') . '/scripts/lando/local.settings.php';
    $this->projectDrushAliasesFile = $this->getConfigValue('drush.alias-dir') . '/' . $this->getConfigValue('project.machine_name') . '.site.yml';
    $this->projectLandoFile = $this->getConfigValue('repo.root') . '/.lando.yml';
    $this->projectLocalSettingsFile = $this->getConfigValue('repo.root') . '/docroot/sites/default/settings/local.settings.php';
    $this->projectLandoConfigFile = $this->getConfigValue('lando.config');
  }

  /**
   * Configures and boots a Lando Environment.
   *
   * @command recipes:lando:init
   *
   * @aliases rdil lando
   *
   * @options no-boot
   *
   * @throws \Exception
   */
  public function lando($options = ['no-boot' => FALSE]) {
    if (!$this->isLandoAlreadyInitialized()) {
      $confirm = $this->confirm("Lando configuration is not currently installed. Install it now? ", TRUE);
      if ($confirm) {
        $this->install();
        $this->localInitialize();
      }
      else {
        return FALSE;
      }
    } else {
      $this->say("Lando is already configured. In the future, please use lando commands to interact directly with the environment.");
    }

    if (!$options['no-boot']) {
      return $this->boot();
    }
  }

  /**
   * Destroys existing Lando environment and all related configuration.
   *
   * @command recipes:lando:destroy
   * @aliases rddl lando:nuke
   * @throws \Exception
   */
  public function nuke() {
    $confirm = $this->confirm("This will destroy your Lando environment, and delete all associated configuration. Continue?");
    if ($confirm) {
      $this->taskExecStack()
        ->exec("lando destroy")
        ->dir($this->getConfigValue('repo.root'))
        ->printOutput(TRUE)
        ->stopOnFail()
        ->run();
      $this->taskFilesystemStack()
        ->remove($this->projectLandoConfigFile)
        ->remove($this->projectLandoFile)
        // @todo More surgically remove drush.default_alias and drush.aliases.local values from this file
        // rather than overwriting it.
        ->remove($this->getConfigValue('blt.config-files.local'))
        ->run();
      $this->say("Your Lando environment instance has been obliterated.");
      $this->say("Please run `blt lando` to create a new one.");
    }
  }

  /**
   * Generates default configuration for Lando.
   */
  protected function install() {
    $this->checkRequirements();
    $this->logger->info("Generating default configuration for Lando...");

    $this->createDrushAlias();
    $this->createConfigFiles();
    $this->customizeConfigFiles();
    $this->updateLocalSettingsFile();

    $this->say("");
    $this->say("<info>BLT has created default configuration for Lando!</info>");
    $this->say(" * The configuration file is <comment>{$this->projectLandoConfigFile}</comment>.");

    $this->say(" * To customize Lando, follow the Quick Start Guide in Lando's documentation:");
    $this->say("   <comment>https://docs.devwithlando.io/</comment>");
    $this->say(" * To run blt or drush commands against your Lando environment, must run <comment>lando blt</comment>, or <comment>lando drush</comment>.");
    $this->say(" * From now on, please use lando commands to manage your virtual environment on this computer.");
    $this->say("");
  }

  protected function updateLocalSettingsFile() {
    file_put_contents($this->projectLocalSettingsFile, file_get_contents($this->defaultLocalSettingsFile));
  }

  /**
   * Create a symlink to proxy blt commands into the container
   */
  protected function createBLTSymlink() {
    $result = $this->taskExec("lando ssh -u root -c \"ln -s /app/vendor/acquia/blt/bin/blt /usr/bin/blt\"")
      ->dir($this->getConfigValue('repo.root'))
      ->printOutput(TRUE)
      ->run();
    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to create the BLT Symlink. This is likely due to an issue with your lando configuration and not BLT itself.");
    }
  }

  /**
   * Configures local machine to use Lando as default env for BLT commands.
   */
  protected function localInitialize() {
    if (!$this->getInspector()->isBltLocalConfigFilePresent()) {
      $this->invokeCommands(['blt:init:settings']);
    }

    $filename = $this->getConfigValue('blt.config-files.local');
    $this->logger->info("Updating $filename");

    $contents = Yaml::parse(file_get_contents($filename));
    $contents['lando']['enable'] = TRUE;
    $yaml = Yaml::dump($contents, 3, 2);
    file_put_contents($filename, $yaml);

    $this->say("<comment>$filename</comment> was modified.");
  }

  /**
   * Boots a Lando environment.
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function boot() {
    $confirm = $this->confirm("Do you want to boot Lando?", TRUE);
    if ($confirm) {
      $this->say("In the future, run <comment>lando start</comment> to boot the environment.");
      $result = $this->taskExec("lando start")
        ->dir($this->getConfigValue('repo.root'))
        ->printOutput(TRUE)
        ->run();
      if (!$result->wasSuccessful()) {
        throw new BltException("Unable to provision the lando environment. This is likely due to an issue with your lando configuration and not BLT itself.");
      } else {
        $this->createBLTSymlink();

        $this->yell("Lando booted successfully.");
        $this->say(" * To run blt or drush commands against your environment, must run using <comment>lando</comment>.");
        $this->say(" * From now on, please use lando commands to manage your virtual machine on this computer.");
      }
      return $result;
    }
  }

  /**
   * Determines if Lando is currently configured in the project.
   *
   * @return bool
   *   TRUE if it is present already.
   */
  protected function isLandoAlreadyInitialized() {
    return file_exists($this->getConfigValue('repo.root') . '/.lando.yml');
  }

  /**
   * Checks local system for Lando requirements.
   *
   * Verifies that Lando is installed.
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function checkRequirements() {
    if (!$this->getInspector()->commandExists("lando")) {
      $this->logger->error("Lando is not installed.");
      $this->say("Please install all dependencies for Lando by following the Quickstart Guide:");
      $this->say("https://docs.devwithlando.io/started.html");
      throw new BltException("Lando requirements are missing.");
    }
  }

  /**
   * Modifies the default configuration file.
   */
  protected function customizeConfigFiles() {
    /** @var \Acquia\Blt\Robo\Config\BltConfig $config */
    $config = clone $this->getConfig();

//    $config->set('lando.config.dir', $this->landoConfigDir);
    $config->expandFileProperties($this->projectLandoFile);
//    $config->expandFileProperties($this->projectLandoConfigFile);
  }

  /**
   * Creates the default configuration files.
   */
  protected function createConfigFiles() {
    $this->taskFilesystemStack()
      ->copy($this->defaultLandoFile, $this->projectLandoFile, TRUE)
      ->stopOnFail()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
  }

  /**
   * Creates a new drush alias record.
   */
  protected function createDrushAlias() {
    $this->logger->info("Adding a drush alias for the new lando environment...");
    if (!file_exists($this->projectDrushAliasesFile)) {
      $new_aliases = Expander::parse(file_get_contents($this->landoDrushAliasesFile), $this->getConfig()->export());
    }
    else {
      $project_drush_aliases = Expander::parse(file_get_contents($this->projectDrushAliasesFile), $this->getConfig()->export());
      $default_lando_drush_aliases = Expander::parse(file_get_contents($this->landoDrushAliasesFile), $this->getConfig()->export());
      $new_aliases = ArrayManipulator::arrayMergeRecursiveDistinct($project_drush_aliases, $default_lando_drush_aliases);
    }

    file_put_contents($this->projectDrushAliasesFile, Yaml::dump($new_aliases));
  }

}
