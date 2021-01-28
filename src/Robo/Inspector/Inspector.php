<?php

namespace Acquia\Blt\Robo\Inspector;

use Acquia\Blt\Robo\Blt;
use Acquia\Blt\Robo\Common\ArrayManipulator;
use Acquia\Blt\Robo\Common\Executor;
use Acquia\Blt\Robo\Common\IO;
use Acquia\Blt\Robo\Config\BltConfig;
use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Acquia\Blt\Robo\Config\YamlConfigProcessor;
use Acquia\Blt\Robo\Exceptions\BltException;
use Consolidation\Config\Loader\YamlConfigLoader;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Robo\Common\BuilderAwareTrait;
use Robo\Contract\BuilderAwareInterface;
use Robo\Contract\ConfigAwareInterface;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Inspects various details about the current project.
 *
 * @package Acquia\Blt\Robo\Common
 */
class Inspector implements BuilderAwareInterface, ConfigAwareInterface, ContainerAwareInterface, LoggerAwareInterface {

  use BuilderAwareTrait;
  use ConfigAwareTrait;
  use ContainerAwareTrait;
  use LoggerAwareTrait;
  use IO;

  /**
   * Process executor.
   *
   * @var \Acquia\Blt\Robo\Common\Executor
   */
  protected $executor;

  /**
   * Is VM initialized.
   *
   * @var null
   */
  protected $isDrupalVmLocallyInitialized = NULL;

  /**
   * Is MYSQL available.
   *
   * @var null
   */
  protected $isMySqlAvailable = NULL;

  /**
   * Is PostgreSQL available.
   *
   * @var null
   */
  protected $isPostgreSqlAvailable = NULL;

  /**
   * Is Sqlite available.
   *
   * @var null
   */
  protected $isSqliteAvailable = NULL;

  /**
   * DrupalVM status.
   *
   * @var array
   */
  protected $drupalVmStatus = NULL;

  /**
   * Is DrupalVM booted.
   *
   * @var null
   */
  protected $isDrupalVmBooted = NULL;

  /**
   * Filesystem.
   *
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  protected $fs;

  /**
   * Warnings were issued.
   *
   * @var bool
   */
  protected $warningsIssued = FALSE;

  /**
   * The constructor.
   *
   * @param \Acquia\Blt\Robo\Common\Executor $executor
   *   Process executor.
   */
  public function __construct(Executor $executor) {
    $this->executor = $executor;
    $this->fs = new Filesystem();
  }

  /**
   * Get filesystem.
   *
   * @return \Symfony\Component\Filesystem\Filesystem
   *   Filesystem.
   */
  public function getFs() {
    return $this->fs;
  }

  /**
   * Set filesystem.
   *
   * @param \Symfony\Component\Filesystem\Filesystem $fs
   *   Filesystem.
   */
  public function setFs(Filesystem $fs) {
    $this->fs = $fs;
  }

  /**
   * Clear state.
   */
  public function clearState() {
    $this->isMySqlAvailable = NULL;
    $this->isPostgreSqlAvailable = NULL;
    $this->isSqliteAvailable = NULL;
    $this->drupalVmStatus = [];
    $this->isDrupalVmLocallyInitialized = NULL;
    $this->isDrupalVmBooted = NULL;
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
   * Determines if BLT configuration file exists, typically blt.yml.
   *
   * @return bool
   *   TRUE if file exists.
   */
  public function isBltConfigFilePresent() {
    return file_exists($this->getConfigValue('blt.config-files.project'));
  }

  /**
   * Determines if BLT configuration file exists, typically local.blt.yml.
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
   * Determines if salt.txt file exists.
   *
   * @return bool
   *   TRUE if file exists.
   */
  public function isHashSaltPresent() {
    return file_exists($this->getConfigValue('repo.root') . '/salt.txt');
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
    $this->logger->debug("Verifying that Drupal is installed...");
    $uri = $this->getConfigValue('drush.uri');
    $result = $this->executor->drush("--uri=$uri status bootstrap")->run();
    $output = trim($result->getMessage());
    $installed = $result->wasSuccessful() && strpos($output, 'Drupal bootstrap : Successful') !== FALSE;
    $this->logger->debug("Drupal bootstrap results: $output");

    return $installed;
  }

  /**
   * Gets the result of `drush status`.
   *
   * @return array
   *   The result of `drush status`.
   */
  public function getDrushStatus() {
    $status_info = (array) json_decode($this->executor->drush('status --format=json --fields=*')->run()->getMessage(), TRUE);

    return $status_info;
  }

  /**
   * Get status.
   *
   * @return mixed
   *   Status.
   */
  public function getStatus() {
    $status = $this->getDrushStatus();
    if (array_key_exists('php-conf', $status)) {
      foreach ($status['php-conf'] as $key => $conf) {
        unset($status['php-conf'][$key]);
        $status['php-conf'][] = $conf;
      }
    }

    $defaults = [
      'root' => $this->getConfigValue('docroot'),
      'uri' => $this->getConfigValue('site'),
    ];

    $status['composer-version'] = $this->getComposerVersion();
    $status['blt-version'] = Blt::getVersion();
    $status['stacks']['drupal-vm']['inited'] = $this->isDrupalVmLocallyInitialized();
    $status['stacks']['dev-desktop']['inited'] = $this->isDevDesktopInitialized();

    $status = ArrayManipulator::arrayMergeRecursiveDistinct($defaults, $status);
    ksort($status);

    return $status;
  }

  /**
   * Validates a drush alias.
   *
   * Note that this runs in the context of the _configured_ Drush alias, but
   * validates the _passed_ Drush alias. So the generated command might be:
   * `drush @self site:alias @self --format=json`
   *
   * @param string $alias
   *   Drush alias.
   *
   * @return bool
   *   TRUE if alias is valid.
   */
  public function isDrushAliasValid($alias) {
    return $this->executor->drush("site:alias @$alias --format=json")
      ->run()
      ->wasSuccessful();
  }

  /**
   * Gets the major version of drush.
   *
   * @return int
   *   The major version of drush.
   */
  public function getDrushMajorVersion() {
    $version_info = json_decode($this->executor->drush('version --format=json')->run()->getMessage(), TRUE);
    if (!empty($version_info['drush-version'])) {
      $version = $version_info['drush-version'];
    }
    else {
      $version = $version_info;
    }

    $major_version = substr($version, 0, 1);

    return (int) $major_version;
  }

  /**
   * Determines if database is available, caches result.
   *
   * This method caches its result in $this->isDatabaseAvailable.
   *
   * @return bool
   *   TRUE if MySQL is available.
   */
  public function isDatabaseAvailable() {
    $db = $this->getDrushStatus()['db-driver'];
    switch ($db) {
      case 'mysql':
        return $this->isMySqlAvailable();

      case 'pgsql':
        return $this->isPostgreSqlAvailable();

      case 'sqlite':
        return $this->isSqliteAvailable();
    }

  }

  /**
   * Determines if MySQL is available, caches result.
   *
   * This method caches its result in $this->isMySqlAvailable.
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
   * Determines if PostgreSQL is available, caches result.
   *
   * This method caches its result in $this->isPostgreSqlAvailable.
   *
   * @return bool
   *   TRUE if MySQL is available.
   */
  public function isPostgreSqlAvailable() {
    if (is_null($this->isPostgreSqlAvailable)) {
      $this->isPostgreSqlAvailable = $this->getPostgreSqlAvailable();
    }

    return $this->isPostgreSqlAvailable;
  }

  /**
   * Determines if PostgreSQL is available. Uses credentials from Drush.
   *
   * This method does not cache its result.
   *
   * @return bool
   *   TRUE if PostgreSQL is available.
   */
  public function getPostgreSqlAvailable() {
    $this->logger->debug("Verifying that PostgreSQL is available...");
    /** @var \Robo\Result $result */
    $result = $this->executor->drush("sqlq \"SHOW DATABASES\"")
      ->run();

    return $result->wasSuccessful();
  }

  /**
   * Determines if Sqlite is available, caches result.
   *
   * This method caches its result in $this->isSqliteAvailable.
   *
   * @return bool
   *   TRUE if Sqlite is available.
   */
  public function isSqliteAvailable() {
    if (is_null($this->isSqliteAvailable)) {
      $this->isSqliteAvailable = $this->getSqliteAvailable();
    }

    return $this->isSqliteAvailable;
  }

  /**
   * Determines if Sqlite is available. Uses credentials from Drush.
   *
   * This method does not cache its result.
   *
   * @return bool
   *   TRUE if Sqlite is available.
   */
  public function getSqliteAvailable() {
    $this->logger->debug("Verifying that Sqlite is available...");
    /** @var \Robo\Result $result */
    $result = $this->executor->drush("sqlq \".tables\"")
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
    return file_exists($this->getConfigValue('repo.root') . '/Vagrantfile')
      && file_exists($this->getConfigValue('vm.config'));
  }

  /**
   * Determines if Lando configuration exists in the project.
   *
   * @return bool
   *   TRUE if Lando configuration exists.
   */
  public function isLandoConfigPresent() {
    return file_exists($this->getConfigValue('repo.root') . '/.lando.yml');
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
    if (is_null($this->isDrupalVmLocallyInitialized)) {
      $this->isDrupalVmLocallyInitialized = $this->isVmCli() || $this->getConfigValue('vm.enable');
      $statement = $this->isDrupalVmLocallyInitialized ? "is" : "is not";
      $this->logger->debug("Drupal VM $statement initialized.");
    }

    return $this->isDrupalVmLocallyInitialized;
  }

  /**
   * Determines if Drupal VM config is valid.
   *
   * @return bool
   *   TRUE is Drupal VM config is valid.
   */
  public function isDrupalVmConfigValid() {
    $valid = TRUE;
    $status = $this->getDrupalVmStatus();
    $machine_name = $this->getConfigValue('project.machine_name');
    if (empty($status[$machine_name]['state'])) {
      $this->logger->error("Could not find VM. Please ensure that the VM machine name matches project.machine_name");
      $valid = FALSE;
    }
    else {
      if ($status[$machine_name]['state'] == 'not_created') {
        $this->logger->error("Drupal VM config has been initialized, but the VM has not been created. Please re-run `blt vm`.");
        $valid = FALSE;
      }
    }

    $file_path = $this->getConfigValue('vm.config');
    if (!file_exists($file_path)) {
      $this->logger->error("$file_path is missing. Please re-run `blt vm`.");
      $valid = FALSE;
    }

    return $valid;
  }

  /**
   * Determines if Drupal VM is booted.
   *
   * @return bool
   *   TRUE if Drupal VM is booted.
   */
  public function isDrupalVmBooted() {
    if (!$this->commandExists('vagrant')) {
      $this->isDrupalVmBooted = FALSE;
    }

    if (is_null($this->isDrupalVmBooted)) {
      $status = $this->getDrupalVmStatus();
      $machine_name = $this->getConfigValue('project.machine_name');
      $this->isDrupalVmBooted = !empty($status[$machine_name]['state'])
        && $status[$machine_name]['state'] == 'running';

      $statement = $this->isDrupalVmBooted ? "is" : "is not";
      $this->logger->debug("Drupal VM $statement booted.");
    }

    return $this->isDrupalVmBooted;
  }

  /**
   * Determines if the current PHP process is being executed inside VM.
   *
   * @return bool
   *   TRUE if current PHP process is being executed inside of VM.
   */
  public function isVmCli() {
    return (isset($_SERVER['USER']) && $_SERVER['USER'] == 'vagrant');
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
      ->getMessage();

    return $installed;
  }

  /**
   * Is devdesktop inited?
   */
  public function isDevDesktopInitialized() {
    $file_contents = file_get_contents($this->getConfigValue('drupal.settings_file'));

    return strstr($file_contents, 'DDSETTINGS');
  }

  /**
   * Gets Composer version.
   *
   * @return string
   *   The version of Composer.
   */
  public function getComposerVersion() {
    $version = $this->executor->execute("composer --version")
      ->interactive(FALSE)
      ->silent(TRUE)
      ->run()
      ->getMessage();

    return $version;
  }

  /**
   * Checks if a Composer package is installed.
   *
   * @return bool
   *   True if package exists, false otherwise.
   */
  public function isComposerPackageInstalled($package_name) {
    $result = $this->executor->execute("composer show $package_name")
      ->interactive(FALSE)
      ->silent(TRUE)
      ->run();
    return $result->wasSuccessful();
  }

  /**
   * Verifies that installed minimum Composer version is met.
   *
   * @param string $minimum_version
   *   The minimum Composer version that is required.
   *
   * @return bool
   *   TRUE if minimum version is satisfied.
   */
  public function isComposerMinimumVersionSatisfied($minimum_version) {
    // phpcs:ignore
    exec("composer --version | cut -d' ' -f3", $output, $exit_code);
    if (version_compare($output[0], $minimum_version, '>=')) {
      return TRUE;
    }
    return FALSE;
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
    if (DIRECTORY_SEPARATOR == '\\') {
      $home_dir = $_SERVER['USERPROFILE'];
    }
    else {
      $home_dir = getenv('HOME');
    }

    if (strstr(getenv('SHELL'), 'zsh')) {
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
    elseif (file_exists($home_dir . '/.functions')) {
      $file = $home_dir . '/.functions';
    }

    return $file;
  }

  /**
   * Checks if a given command exists on the system.
   *
   * @param string $command
   *   The command binary only, e.g., "drush" or "php".
   *
   * @return bool
   *   TRUE if the command exists, otherwise FALSE.
   */
  public function commandExists($command) {
    // phpcs:ignore
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
   */
  public function isGitMinimumVersionSatisfied($minimum_version) {
    // phpcs:ignore
    exec("git --version | cut -d' ' -f3", $output, $exit_code);
    if (version_compare($output[0], $minimum_version, '>=')) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Verifies that Git user is configured.
   *
   * @return bool
   *   TRUE if configured, FALSE otherwise.
   */
  public function isGitUserSet() {
    // phpcs:ignore
    exec("git config user.name", $output, $name_not_set);
    // phpcs:ignore
    exec("git config user.email", $output, $email_not_set);
    return !($name_not_set || $email_not_set);
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
    $processor->extend($loader->load($this->getConfigValue('repo.root') . '/tests/behat/behat.yml'));
    $behat_local_config->replace($processor->export());

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
      $this->logger->warning('project.local.uri in blt.yml does not match local.extensions.Behat\MinkExtension.base_url in local.yml.');
      $this->logger->warning('project.local.uri = ' . $this->getConfigValue('project.local.uri'));
      $this->logger->warning('local.extensions.Behat\MinkExtension.base_url = ' . $local_behat_config->get('local.extensions.Behat\MinkExtension.base_url'));
      return FALSE;
    }

    // Verify that URIs required for an ad-hoc PHP internal server are
    // configured correctly.
    if ($this->getConfigValue('tests.run-server')) {
      if ($this->getConfigValue('tests.server.url') != $this->getConfigValue('project.local.uri')) {
        $this->logger->warning("tests.run-server is enabled, but the server URL does not match Drupal's base URL.");
        $this->logger->warning('project.local.uri = ' . $this->getConfigValue('project.local.uri'));
        $this->logger->warning('tests.server.url = ' . $this->getConfigValue('tests.server.url'));
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
   * Checks if simplesamlphp has already been setup by BLT.
   *
   * @return bool
   *   TRUE if the simplesamlphp config key exists and is true.
   */
  public function isSimpleSamlPhpInstalled() {
    return $this->getConfig()->has('simplesamlphp') && $this->getConfigValue('simplesamlphp');
  }

  /**
   * Gets the value of $this->drupalVmStatus. Sets it if empty.
   *
   * @return array
   *   An array of status data.
   */
  protected function getDrupalVmStatus() {
    if (is_null($this->drupalVmStatus)) {
      $this->setDrupalVmStatus();
    }
    return $this->drupalVmStatus;
  }

  /**
   * Sets $this->drupalVmStatus by executing `vagrant status`.
   */
  protected function setDrupalVmStatus() {
    $result = $this->executor->execute("vagrant status --machine-readable")
      ->interactive(FALSE)
      ->printMetadata(TRUE)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
      ->run();
    $output = $result->getMessage();
    if (!$result->wasSuccessful() || !$output) {
      $this->drupalVmStatus = [];
      return FALSE;
    }
    $lines = explode("\n", $output);
    foreach ($lines as $line) {
      $parsed_line = explode(',', $line);
      if (count($parsed_line) < 4) {
        continue;
      }
      list(, $target, $type, $data) = $parsed_line;
      $this->drupalVmStatus[$target][$type] = $data;
      $this->logger->debug("vagrant $target.$type = $data");
    }
  }

  /**
   * Gets the current schema version of the root project.
   *
   * @return string
   *   The current schema version.
   */
  public function getCurrentSchemaVersion() {
    if (file_exists($this->getConfigValue('blt.config-files.schema-version'))) {
      $version = trim(file_get_contents($this->getConfigValue('blt.config-files.schema-version')));
    }
    else {
      $version = $this->getContainer()->get('updater')->getLatestUpdateMethodVersion();
    }

    return $version;
  }

  /**
   * Is schema version up to date?
   */
  public function isSchemaVersionUpToDate() {
    return $this->getCurrentSchemaVersion() >= $this->getContainer()->get('updater')->getLatestUpdateMethodVersion();
  }

  /**
   * Emits a warning if Drupal VM is initialized but not running.
   */
  protected function warnIfDrupalVmNotRunning() {
    if (!$this->isVmCli() && $this->isDrupalVmLocallyInitialized() && !$this->isDrupalVmBooted()) {
      $this->logger->warning("Drupal VM is locally initialized, but is not running.");
    }
  }

  /**
   * Issues warnings to user if their local environment is mis-configured.
   *
   * @param string $command_name
   *   The name of the BLT Command being executed.
   */
  public function issueEnvironmentWarnings($command_name) {
    if (!$this->warningsIssued) {
      $this->warnIfPhpOutdated();
      $this->warnIfXdebugLoaded();

      $exclude_commands = [
        'list',
        'recipes:drupalvm:init',
        'recipes:drupalvm:destroy',
      ];
      if (!in_array($command_name, $exclude_commands)) {
        $this->warnIfDrupalVmNotRunning();
      }

      $this->warningsIssued = TRUE;
    }
  }

  /**
   * Throws an exception if the minimum PHP version is not met.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function warnIfPhpOutdated() {
    $minimum_php_version = 7;
    $current_php_version = phpversion();
    if ($current_php_version < $minimum_php_version) {
      throw new BltException("BLT requires PHP $minimum_php_version or greater. You are using $current_php_version.");
    }
  }

  /**
   * Warns the user if the xDebug extension is loaded.
   */
  protected function warnIfXdebugLoaded() {
    $xdebug_loaded = extension_loaded('xdebug');
    if ($xdebug_loaded) {
      $this->logger->warning("The xDebug extension is loaded. This will significantly decrease performance.");
    }
  }

  /**
   * Determines if the active config is identical to sync directory.
   *
   * @return bool
   *   TRUE if config is identical.
   */
  public function isActiveConfigIdentical() {
    $uri = $this->getConfigValue('drush.uri');
    $result = $this->executor->drush("config:status --uri=$uri 2>&1")->run();
    $message = trim($result->getMessage());
    $identical = strstr($message, 'No differences between DB and sync directory') !== FALSE;

    return $identical;
  }

}
