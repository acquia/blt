<?php

namespace Acquia\Blt\Robo\LocalEnvironment;

use Acquia\Blt\Robo\Common\ArrayManipulator;
use Acquia\Blt\Robo\Common\IO;
use Drupal\Core\Installer\Exception\AlreadyInstalledException;
use Grasmash\YamlExpander\Expander;

/**
 * Class LocalEnvironment
 * @package Acquia\Blt\Robo\Common
 */
class LocalEnvironment {

  use IO;

  /**
   * @var array
   */
  protected $defaultConfig = [];
  /**
   * @var array
   */
  protected $projectConfig = [];
  /**
   * @var array
   */
  protected $config = [];
  /**
   * @var
   */
  protected $repoRoot;
  /**
   * @var
   */
  protected $bltRoot;
  /**
   * @var
   */
  protected $docroot;
  /**
   * @var
   */
  protected $drupalSettingsFile;
  /**
   * @var string
   */
  protected $bin;

  /**
   * LocalEnvironment constructor.
   */
  public function __construct() {
    // Move these to DefaultConfig and call $this->set();
    // @see https://github.com/pantheon-systems/terminus/blob/master/src/Config/DefaultsConfig.php
    $this->setRepoRoot();
    $this->setBltRoot();
    $this->setDocroot();
    $this->setConfig();
    $this->setDrupalSettingsFile();
    $this->bin = "{$this->repoRoot}/vendor/bin";
  }

  /**
   * @return mixed
   */
  public function getBltRoot() {
    return $this->bltRoot;
  }

  /**
   * @return string
   */
  public function getBin() {
    return $this->bin;
  }

  /**
   * @return array
   */
  public function getConfig() {
    return $this->config;
  }

  /**
   * Set $this->repoRoot.
   */
  public function setRepoRoot() {
    $possible_repo_roots = [
      $_SERVER['PWD'],
      getcwd(),
    ];
    foreach ($possible_repo_roots as $possible_repo_root) {
      if (file_exists("$possible_repo_root/blt/project.yml")) {
        $this->repoRoot = $possible_repo_root;
        break;
      }
    }
  }

  /**
   * @return mixed
   */
  public function getRepoRoot() {
    return $this->repoRoot;
  }

  /**
   * Set $this->bltRoot.
   */
  public function setBltRoot() {
    $possible_blt_roots = [
      dirname(dirname(dirname(dirname(__FILE__)))),
      dirname(dirname(dirname(__FILE__))),
    ];
    foreach ($possible_blt_roots as $possible_blt_root) {
      if (file_exists("$possible_blt_root/template")) {
        $this->bltRoot = $possible_blt_root;
        break;
      }
    }
  }

  /**
   *
   */
  protected function setDocroot() {
    if (!empty($this->repoRoot)) {
      $this->docroot = "{$this->repoRoot}/docroot";
    }
  }

  /**
   * @return mixed
   */
  public function getDocroot() {
    return $this->docroot;
  }

  /**
   * @return string
   */
  public function getDrupalSettingsFile() {
    return $this->drupalSettingsFile;
  }

  /**
   *
   */
  public function setDrupalSettingsFile() {
    $this->drupalSettingsFile = $this->getDocroot() . '/docroot/sites/default/settings.php';
  }


  /**
   * @param array $reference_data
   *
   * @return $this
   */
  public function setDefaultConfig($reference_data = []) {
    $default_config = Expander::parse(file_get_contents("{$this->bltRoot}/phing/build.yml"), $reference_data);
    $this->defaultConfig = ArrayManipulator::reKeyDotNotatedKeys($default_config);

    return $this;
  }

  /**
   * @return array
   */
  public function getDefaultConfig() {
    return $this->defaultConfig;
  }

  /**
   * @param array $reference_data
   *
   * @return $this
   */
  public function setProjectConfig($reference_data = []) {
    if ($this->repoRootExists()) {
      $project_config = Expander::parse(file_get_contents("{$this->repoRoot}/blt/project.yml"), $reference_data);
      $this->projectConfig = ArrayManipulator::reKeyDotNotatedKeys($project_config);

      return $this;
    }
  }

  /**
   * @return array
   */
  public function getProjectConfig() {
    return $this->projectConfig;
  }

  /**
   * Set $this->config.
   */
  public function setConfig() {
    $reference_data = [
      'repo' => [
        'root' => $this->getRepoRoot(),
      ],
      'blt' => [
        'root' => $this->bltRoot,
      ],
      'docroot' => $this->docroot,
    ];
    $this->setDefaultConfig($reference_data);
    $this->setProjectConfig($reference_data);
    $project_config = $this->getProjectConfig();
    $default_config = $this->getDefaultConfig();
    $default_config['repo.root'] = $this->getRepoRoot();
    $default_config['blt.root'] = $this->bltRoot;
    $default_config['docroot'] = $this->docroot;
    $config = ArrayManipulator::arrayMergeRecursiveDistinct($project_config, $default_config);
    $config = Expander::expandArrayProperties($config);
    ksort($config);
    $this->config = ArrayManipulator::reKeyDotNotatedKeys($config);
  }

  /**
   * @return bool
   */
  protected function repoRootExists() {
    return file_exists($this->getRepoRoot());
  }

  /**
   * @return bool
   */
  public function drupalSettingsFileExists($settings_file_path) {
    if (!file_exists($settings_file_path)) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * @return bool
   */
  public function drupalSettingsFileIsValid($settings_file_path) {
    $settings_file_contents = file_get_contents($settings_file_path);
    if (!strstr($settings_file_contents,
      '/../vendor/acquia/blt/settings/blt.settings.php')
    ) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Checks that Drupal is installed.
   */
  public function drupalIsInstalled($docroot) {
    try {
      require $docroot . '/core/includes/install.core.inc';
      require $docroot . '/core/includes/install.inc';
      require $docroot . '/core/modules/system/system.install';
      install_verify_database_ready();
    }
    catch (AlreadyInstalledException $e) {
      return TRUE;
    }
    catch (\Exception $e) {

    }
    catch (\Throwable $e) {

    }

    return FALSE;
  }

  /**
   * Checks if a given command exists on the system.
   *
   * @param $command string the command binary only. E.g., "drush" or "php".
   *
   * @return bool
   *   TRUE if the command exists, otherwise FALSE.
   */
  public function commandExists($command) {
    exec("command -v $command >/dev/null 2>&1", $output, $exit_code);
    return $exit_code == 0;
  }

  public function behatIsConfigured() {
    return file_exists($this->getRepoRoot() . '/tests/behat/local.yml');
  }


  /**
   * @param $methods
   *
   * @return bool
   */
  public function performLocalEnvironmentChecks($methods) {
    foreach ($methods as $method) {
      if (!$this->$method()) {
        return FALSE;
      }
    }
  }

  /**
   * Check if an array of commands exists on the system.
   *
   * @param $commands array An array of command binaries.
   *
   * @return bool
   *   TRUE if all commands exist, otherwise FALSE.
   */
  protected function checkCommandsExist($commands) {
    foreach ($commands as $command) {
      if (!$this->localEnvironment->commandExists($command)) {
        $this->yell("Unable to find '$command' command!");
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * @return bool
   *   FALSE if repo root cannot be found.
   */
  protected function checkDocrootExists() {
    if (empty($this->docroot) || !file_exists($this->docroot)) {
      $this->error("Unable to find docroot.");

      return FALSE;
    }

    return TRUE;
  }

  /**
   * @return bool
   *   FALSE if repo root cannot be found.
   */
  protected function checkRepoRootExists() {
    if (empty($this->getRepoRoot())) {
      $this->error("Unable to find repository root.");
      $this->say("This command must be run from a BLT-generated project directory.");

      return FALSE;
    }

    return TRUE;
  }

  /**
   * @return bool
   */
  protected function checkDrupalInstalled() {
    if ($this->drupalIsInstalled($this->getDocroot())) {
      return TRUE;
    }

    $this->error("Drupal is not installed");
    return FALSE;
  }

  /**
   * Checks active settings.php file.
   */
  protected function checkSettingsFile() {
    if (!$this->drupalSettingsFileExists($this->getDrupalSettingsFile())) {
      $this->error("Could not find settings.php for this site.");
      return FALSE;
    }

    if (!$this->drupalSettingsFileIsValid($this->getDrupalSettingsFile())) {
      $this->error("BLT settings are not included in settings file.");
      return FALSE;
    }

    return TRUE;
  }

  protected function checkBehatIsConfigured() {
    if (!$this->behatIsConfigured()) {
      $this->error("Behat is not properly configured.");
      return FALSE;
    }

    return TRUE;
  }
}
