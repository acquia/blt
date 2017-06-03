<?php

namespace Acquia\Blt\Robo\Inspector;

use Acquia\Blt\Robo\Config\YamlConfigProcessor;
use Robo\Config\YamlConfigLoader;
use Acquia\Blt\Robo\Common\Executor;
use Acquia\Blt\Robo\Common\IO;
use Acquia\Blt\Robo\Config\BltConfig;
use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Robo\Common\BuilderAwareTrait;
use Robo\Contract\BuilderAwareInterface;
use Robo\Contract\ConfigAwareInterface;

/**
 * Class Inspector.
 *
 * @package Acquia\Blt\Robo\Common
 */
class Inspector implements BuilderAwareInterface, ConfigAwareInterface, LoggerAwareInterface {

  use BuilderAwareTrait;
  use ConfigAwareTrait;
  use LoggerAwareTrait;
  use IO;

  /**
   * Process executor.
   *
   * @var \Acquia\Blt\Robo\Common\Executor
   */
  protected $executor;

  /**
   * @var null
   */
  protected $isDrupalInstalled = NULL;

  /**
   * @var null
   */
  protected $isMySqlAvailable = NULL;

  /**
   * The constructor.
   *
   * @param \Acquia\Blt\Robo\Common\Executor $executor
   *   Process executor.
   */
  public function __construct(Executor $executor) {
    $this->executor = $executor;
  }

  public function clearState() {
    $this->isDrupalInstalled = NULL;
    $this->isMySqlAvailable = NULL;
  }

  /**
   * Determines if the repository root directory exists.
   *
   * @return bool
   *   TRUE if file exists.
   */
  public function isRepoRootPresent() {
    return file_exists($this->getConfigValue('repo.root'));
  }

  /**
   * Determines if the Drupal docroot directory exists.
   *
   * @return bool
   *   TRUE if file exists.
   */
  public function isDocrootPresent() {
    return file_exists($this->getConfigValue('docroot'));
  }

  /**
   * Determines if BLT configuration file exists, typically project.yml.
   *
   * @return bool
   *   TRUE if file exists.
   */
  public function isBltConfigFilePresent() {
    return file_exists($this->getConfigValue('blt.config-files.project'));
  }

  /**
   * Determines if BLT configuration file exists, typically project.local.yml.
   *
   * @return bool
   *   TRUE if file exists.
   */
  public function isBltLocalConfigFilePresent() {
    return file_exists($this->getConfigValue('blt.config-files.local'));
  }

  /**
   * Determines if Drupal settings.php file exists.
   *
   * @return bool
   *   TRUE if file exists.
   */
  public function isDrupalSettingsFilePresent() {
    return file_exists($this->getConfigValue('drupal.settings_file'));
  }

  /**
   * Determines if Drupal local.settings.php file exists.
   *
   * @return bool
   *   TRUE if file exists.
   */
  public function isDrupalLocalSettingsFilePresent() {
    return file_exists($this->getConfigValue('drupal.local_settings_file'));
  }

  /**
   * Determines if Drupal settings.php contains required BLT includes.
   *
   * @return bool
   *   TRUE if settings.php is valid for BLT usage.
   */
  public function isDrupalSettingsFileValid() {
    $settings_file_contents = file_get_contents($this->getConfigValue('drupal.settings_file'));
    if (!strstr($settings_file_contents,
      '/../vendor/acquia/blt/settings/blt.settings.php')
    ) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Checks that Drupal is installed, caches result.
   *
   * This method caches its result in $this->drupalIsInstalled.
   *
   * @return bool
   *   TRUE if Drupal is installed.
   */
  public function isDrupalInstalled() {
    // This will only run once per command. If Drupal is installed mid-command,
    // this value needs to be changed.
    if (is_null($this->isDrupalInstalled)) {
      $this->isDrupalInstalled = $this->getDrupalInstalled();
    }

    return $this->isDrupalInstalled;
  }

  /**
   * Determines if Drupal is installed.
   *
   * This method does not cache its result.
   *
   * @return bool
   *   TRUE if Drupal is installed.
   */
  protected function getDrupalInstalled() {
    $this->logger->debug("Verifying that Drupal is installed...");
    $result = $this->executor->drush("sqlq \"SHOW TABLES LIKE 'config'\"")->run();
    $output = trim($result->getOutputData());
    $installed = $result->wasSuccessful() && $output == 'config';

    return $installed;
  }

  /**
   * Gets the result of `drush status`.
   *
   * @return array
   *   The result of `drush status`.
   */
  public function getDrushStatus() {
    $status_info = json_decode($this->executor->drush('status --format=json --show-passwords')->run()->getOutputData(), TRUE);

    return $status_info;
  }

  /**
   * Determines if MySQL is available, caches result.
   *
   * This method caches its result in $this->mySqlAvailable.
   *
   * @return bool
   *   TRUE if MySQL is available.
   */
  public function isMySqlAvailable() {
    if (is_null($this->isMySqlAvailable)) {
      $this->isMySqlAvailable = $this->getMySqlAvailable();
    }

    return $this->isMySqlAvailable;
  }

  /**
   * Determines if MySQL is available. Uses MySQL credentials from Drush.
   *
   * This method does not cache its result.
   *
   * @return bool
   *   TRUE if MySQL is available.
   */
  public function getMySqlAvailable() {
    $this->logger->debug("Verifying that MySQL is available...");
    /** @var \Robo\Result $result */
    $result = $this->executor->drush("sqlq \"SHOW DATABASES\"")
      ->run();

    return $result->wasSuccessful();
  }

  /**
   * Determines if Drupal VM configuration exists in the project.
   *
   * @return bool
   *   TRUE if Drupal VM configuration exists.
   */
  public function isDrupalVmConfigPresent() {
    return file_exists($this->getConfigValue('repo.root') . '/Vagrantfile');
  }

  /**
   * Determines if Drupal VM is initialized for the local machine.
   *
   * I.E., whether Drupal VM is the default LAMP stack for BLT on local machine.
   *
   * @return bool
   *   TRUE if Drupal VM is initialized for the local machine.
   */
  public function isDrupalVmLocallyInitialized() {
    // We assume that if the local drush alias is ${project.machine_name.local},
    // rather than self, then Drupal VM is being used locally.
    $drush_local_alias = $this->getConfigValue('drush.aliases.local');
    $expected_vm_alias = $this->getConfigValue('project.machine_name') . '.local';
    $initialized = ($drush_local_alias == $expected_vm_alias) && file_exists($this->getConfigValue('repo.root') . '/box/config.yml');
    $statement = $initialized ? "is" : "is not";
    $this->logger->debug("Drupal VM $statement initialized.");

    return $initialized;
  }

  /**
   * Determines if Drupal VM is booted.
   *
   * @return bool
   *   TRUE if Drupal VM is booted.
   */
  public function isDrupalVmBooted() {
    if (!$this->commandExists('vagrant')) {
      return FALSE;
    }

    $result = $this->executor->execute("vagrant status")
      ->printOutput(FALSE)
      ->printMetadata(FALSE)
      ->interactive(FALSE)
      ->run();
    $output = $result->getOutputData();

    $booted = strstr($output, "running");
    $statement = $booted ? "is" : "is not";
    $this->logger->debug("Drupal VM $statement booted.");

    return $booted;
  }

  /**
   * Determines if the current PHP process is being executed inside VM.
   *
   * @return bool
   *   TRUE if current PHP process is being executed inside of VM.
   */
  public function isVmCli() {
    return $_SERVER['USER'] == 'vagrant';
  }

  /**
   * Checks to see if a given vagrant plugin is installed.
   *
   * You can check to see if vagrant is installed with commandExists('vagrant').
   *
   * @param string $plugin
   *   The plugin name.
   *
   * @return bool
   *   TRUE if the plugin is installed.
   */
  public function isVagrantPluginInstalled($plugin) {
    $installed = (bool) $this->executor->execute("vagrant plugin list | grep '$plugin'")
      ->interactive(FALSE)
      ->silent(TRUE)
      ->run()
      ->getOutputData();

    return $installed;
  }

  /**
   * Checks to see if BLT alias is installed on CLI.
   *
   * @return bool
   *   TRUE if BLT alias is installed.
   */
  public function isBltAliasInstalled() {
    $cli_config_file = $this->getCliConfigFile();
    if (!is_null($cli_config_file) && file_exists($cli_config_file)) {
      $contents = file_get_contents($cli_config_file);
      if (strstr($contents, 'function blt')) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Determines the CLI config file.
   *
   * @return null|string
   *   Returns file path or NULL if none was found.
   */
  public function getCliConfigFile() {
    $file = NULL;
    $user = posix_getpwuid(posix_getuid());
    $home_dir = $user['dir'];

    if (!empty($_ENV['SHELL']) && strstr($_ENV['SHELL'], 'zsh')) {
      $file = $home_dir . '/.zshrc';
    }
    elseif (file_exists($home_dir . '/.bash_profile')) {
      $file = $home_dir . '/.bash_profile';
    }
    elseif (file_exists($home_dir . '/.bashrc')) {
      $file = $home_dir . '/.bashrc';
    }
    elseif (file_exists($home_dir . '/.profile')) {
      $file = $home_dir . '/.profile';
    }

    return $file;
  }

  /**
   * Checks if a given command exists on the system.
   *
   * @param string $command
   *   The command binary only. E.g., "drush" or "php".
   *
   * @return bool
   *   TRUE if the command exists, otherwise FALSE.
   */
  public function commandExists($command) {
    exec("command -v $command >/dev/null 2>&1", $output, $exit_code);
    return $exit_code == 0;
  }

  /**
   * Verifies that installed minimum git version is met.
   *
   * @param string $minimum_version
   *   The minimum git version that is required.
   *
   * @return bool
   *   TRUE if minimum version is satisfied.
   *
   */
  public function isGitMinimumVersionSatisfied($minimum_version) {
    exec("git --version | cut -d' ' -f3", $output, $exit_code);
    if (version_compare($output[0], $minimum_version, '>=')) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Gets the local behat configuration defined in local.yml.
   *
   * @return \Acquia\Blt\Robo\Config\BltConfig
   *   The local Behat configuration.
   */
  public function getLocalBehatConfig() {
    $behat_local_config_file = $this->getConfigValue('repo.root') . '/tests/behat/local.yml';

    $behat_local_config = new BltConfig();
    $loader = new YamlConfigLoader();
    $processor = new YamlConfigProcessor();
    $processor->extend($loader->load($behat_local_config_file));
    $behat_local_config->import($processor->export());

    return $behat_local_config;
  }

  /**
   * Returns an array of required Behat files, as defined by Behat config.
   *
   * For instance, this will return the Drupal root dir, Behat features dir,
   * and bootstrap dir on the local file system. All of these files are
   * required for behat to function properly.
   *
   * @return array
   *   An array of required Behat configuration files.
   */
  public function getBehatConfigFiles() {
    $behat_local_config = $this->getLocalBehatConfig();

    return [
      $behat_local_config->get('local.extensions.Drupal\DrupalExtension.drupal.drupal_root'),
      $behat_local_config->get('local.suites.default.paths.features'),
      $behat_local_config->get('local.suites.default.paths.bootstrap'),
    ];
  }

  /**
   * Determines if required Behat files exist.
   *
   * @return bool
   *   TRUE if all required Behat files exist.
   */
  public function areBehatConfigFilesPresent() {
    return $this->filesExist($this->getBehatConfigFiles());
  }

  /**
   * Determines if all file in a given array exist.
   *
   * @return bool
   *   TRUE if all files exist.
   */
  public function filesExist($files) {
    foreach ($files as $file) {
      if (!file_exists($file)) {
        $this->logger->warning("Required file $file does not exist.");
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * Determines if Behat is properly configured on the local machine.
   *
   * This will ensure that required Behat file exists, and that require
   * configuration values are properly defined.
   *
   * @return bool
   *   TRUE is Behat is properly configured on the local machine.
   */
  public function isBehatConfigured() {

    // Verify that URIs required for Drupal and Behat are configured correctly.
    $local_behat_config = $this->getLocalBehatConfig();
    if ($this->getConfigValue('project.local.uri') != $local_behat_config->get('local.extensions.Behat\MinkExtension.base_url')) {
      $this->logger->warning('project.local.uri in project.yml does not match local.extensions.Behat\MinkExtension.base_url in local.yml.');
      $this->logger->warning('project.local.uri = ' . $this->getConfigValue('project.local.uri'));
      $this->logger->warning('local.extensions.Behat\MinkExtension.base_url = ' . $local_behat_config->get('local.extensions.Behat\MinkExtension.base_url'));
      return FALSE;
    }

    // Verify that URIs required for an ad-hoc PHP internal server are
    // configured correctly.
    if ($this->getConfigValue('behat.run-server')) {
      if ($this->getConfigValue('behat.server.url') != $this->getConfigValue('project.local.uri')) {
        $this->logger->warning("behat.run-server is enabled, but the server URL does not match Drupal's base URL.");
        $this->logger->warning('project.local.uri = ' . $this->getConfigValue('project.local.uri'));
        $this->logger->warning('behat.server.url = ' . $this->getConfigValue('behat.server.url'));
        $this->logger->warning('local.extensions.Behat\MinkExtension.base_url = ' . $local_behat_config->get('local.extensions.Behat\MinkExtension.base_url'));

        return FALSE;
      }
    }

    // Verify that required Behat files are present.
    if (!$this->areBehatConfigFilesPresent()) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Determines if the PhantomJS binary is present.
   *
   * @return bool
   *   TRUE if the PhantomJS binary is present.
   */
  public function isPhantomJsBinaryPresent() {
    return file_exists("{$this->getConfigValue('composer.bin')}/phantomjs");
  }

  /**
   * Checks if simplesamlphp has already been setup by BLT.
   *
   * @return bool
   *   TRUE if the simplesamlphp config key exists and is true.
   */
  public function isSimpleSamlPhpInstalled() {
    return $this->getConfig()->has('simplesamlphp') && $this->getConfigValue('simplesamlphp');
  }

}
