<?php

namespace Acquia\Blt\Robo\Common;

use Drupal\Core\Installer\Exception\AlreadyInstalledException;
use Grasmash\YamlExpander\Expander;

class LocalEnvironment {

  protected $defaultConfig = [];
  protected $projectConfig = [];
  protected $config = [];
  protected $repoRoot;
  protected $bltRoot;
  protected $docroot;
  protected $drupalSettingsFile;
  protected $bin;

  public function __construct() {
    $this->setRepoRoot();
    $this->setBltRoot();
    $this->setDocroot();
    $this->setConfig();
    $this->setDrupalSettingsFile();
    $this->bin = "{$this->repoRoot}/vendor/bin";
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

  protected function setDocroot() {
    if (!empty($this->repoRoot)) {
      $this->docroot = "{$this->repoRoot}/docroot";
    }
  }

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


  public function setDefaultConfig($reference_data = []) {
    if ($this->repoRootExists()) {
      $default_config = Expander::parse(file_get_contents("{$this->bltRoot}/phing/build.yml"), $reference_data);
      $this->defaultConfig = ArrayManipulator::reKeyDotNotatedKeys($default_config);

      return $this;
    }
  }

  public function getDefaultConfig() {
    return $this->defaultConfig;
  }

  public function setProjectConfig($reference_data = []) {
    if ($this->repoRootExists()) {
      $project_config = Expander::parse(file_get_contents("{$this->repoRoot}/blt/project.yml"), $reference_data);
      $this->projectConfig = ArrayManipulator::reKeyDotNotatedKeys($project_config);

      return $this;
    }
  }

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
}
