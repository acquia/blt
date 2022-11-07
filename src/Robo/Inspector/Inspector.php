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
   * Clear state.
   */
  public function clearState() {
    $this->isMySqlAvailable = NULL;
    $this->isPostgreSqlAvailable = NULL;
    $this->isSqliteAvailable = NULL;
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
    $result = $this->executor->drush(["status", "bootstrap"])->run();
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
    $docroot = $this->getConfigValue('docroot');
    $status_info = (array) json_decode($this->executor->drush([
      'status',
      '--format=json',
      '--fields=*',
      "--root=$docroot",
    ])->run()->getMessage(), TRUE);

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
    return $this->executor->drush([
      "site:alias",
      "@$alias",
      "--format=json",
    ])->run()->wasSuccessful();
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
    $result = $this->executor->drush(["sqlq", "SHOW DATABASES"])
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
    $result = $this->executor->drush(["sqlq \"SHOW DATABASES\""])
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
    $result = $this->executor->drush(["sqlq", ".tables"])
      ->run();

    return $result->wasSuccessful();
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
   * Gets Composer version.
   *
   * @return string
   *   The version of Composer.
   */
  public function getComposerVersion() {
    $version = $this->executor->execute(["composer", "--version"])
      ->interactive(FALSE)
      ->silent(TRUE)
      ->run()
      ->getMessage();

    return $version;
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
   * Issues warnings to user if their local environment is mis-configured.
   *
   * @param string $command_name
   *   The name of the BLT Command being executed.
   */
  public function issueEnvironmentWarnings($command_name) {
    if (!$this->warningsIssued) {
      $this->warnIfPhpOutdated();

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
   * Determines if the active config is identical to sync directory.
   *
   * @return bool
   *   TRUE if config is identical.
   */
  public function isActiveConfigIdentical() {
    $result = $this->executor->drush(["config:status"])->run();
    $message = trim($result->getMessage());
    $this->logger->debug("Config status check results:");
    $this->logger->debug($message);

    // A successful test here results in "no message" so check for null.
    if ($message == NULL) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

}
