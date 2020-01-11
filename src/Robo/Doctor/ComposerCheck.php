<?php

namespace Acquia\Blt\Robo\Doctor;

use Acquia\Blt\Robo\Common\Executor;
use Acquia\Blt\Robo\Inspector\Inspector;
use Robo\Config\Config;

/**
 * BLT Doctor checks.
 */
class ComposerCheck extends DoctorCheck {

  /**
   * Composer.json.
   *
   * @var array
   */
  protected $composerJson;

  /**
   * Composer.lock.
   *
   * @var array
   */
  protected $composerLock;

  /**
   * Template composer.json.
   *
   * @var array
   */
  protected $templateComposerJson;

  /**
   * DoctorCheck constructor.
   */
  public function __construct(Config $config, Inspector $inspector, Executor $executor, $drush_status) {
    parent::__construct($config, $inspector, $executor, $drush_status);
    $this->setComposerJson();
    $this->setComposerLock();
    $this->setTemplateComposerJson();
  }

  /**
   * Sets $this->composerJson using root composer.json file.
   *
   * @return array
   *   Array.
   */
  protected function setComposerJson() {
    if (file_exists($this->getConfigValue('repo.root') . '/composer.json')) {
      $composer_json = json_decode(file_get_contents($this->getConfigValue('repo.root') . '/composer.json'), TRUE);
      $this->composerJson = $composer_json;

      return $composer_json;
    }

    return [];
  }

  /**
   * Sets $this->templateComposerJson using template composer.json file.
   *
   * @return array
   *   Array.
   */
  protected function setTemplateComposerJson() {
    $file_name = $this->getConfigValue('repo.root') . '/vendor/acquia/blt/subtree-splits/blt-project/composer.json';
    if (file_exists($file_name)) {
      $template_composer_json = json_decode(file_get_contents($file_name, TRUE), TRUE);
      $this->templateComposerJson = $template_composer_json;

      return $template_composer_json;
    }

    return [];
  }

  /**
   * Get composer.json.
   *
   * @return array
   *   Array
   */
  public function getComposerJson() {
    return $this->composerJson;
  }

  /**
   * Sets $this->composerJson using root composer.lock file.
   *
   * @return array
   *   Array.
   */
  protected function setComposerLock() {
    if (file_exists($this->getConfigValue('repo.root') . '/composer.lock')) {
      $composer_lock = json_decode(file_get_contents($this->getConfigValue('repo.root') . '/composer.lock'), TRUE);
      $this->composerLock = $composer_lock;

      return $composer_lock;
    }

    return [];
  }

  /**
   * Get composer.lock.
   *
   * @return array
   *   Array.
   */
  public function getComposerLock() {
    return $this->composerLock;
  }

  /**
   * Checks that composer.json is configured correctly.
   */
  public function performAllChecks() {
    $this->checkRequire();
    $this->checkBltRequireDev();
    if (!$this->getInspector()->isVmCli()) {
      $this->checkPrestissimo();
    }
    $this->checkComposerConfig();

    return $this->problems;
  }

  /**
   * Check require.
   */
  protected function checkRequire() {
    if (!empty($this->composerJson['require-dev']['acquia/blt'])) {
      $this->logProblem('require', [
        "acquia/blt is defined as a development dependency in composer.json",
        "  Move acquia/blt out of the require-dev object and into the require object in composer.json.",
        "  This is necessary for BLT settings files to be available at runtime in production.",
      ], 'error');
    }
  }

  /**
   * Check prestissimo.
   */
  protected function checkPrestissimo() {
    $prestissimo_intalled = $this->getExecutor()->execute("composer global show | grep hirak/prestissimo")->run()->wasSuccessful();
    if (!$prestissimo_intalled) {
      $this->logProblem(__FUNCTION__ . ":plugins", [
        "hirak/prestissimo plugin for composer is not installed.",
        "  Run <comment>composer global require hirak/prestissimo</comment> to install it.",
        "  This will improve composer install/update performance by parallelizing the download of dependency information.",
      ], 'comment');
    }
  }

  /**
   * Emits a warning if project Composer config is different than default.
   */
  protected function checkComposerConfig() {
    // @todo specify third key Acquia\\Blt\\Custom\\.
    $this->compareComposerConfig('autoload', 'psr-4');
    // @todo specify third key Drupal\\Tests\\PHPUnit\\\.
    $this->compareComposerConfig('autoload-dev', 'psr-4');
    $this->compareComposerConfig('extra', 'installer-paths');
    $this->compareComposerConfig('extra', 'enable-patching');
    $this->compareComposerConfig('extra', 'composer-exit-on-patch-failure');
    $this->compareComposerConfig('extra', 'patchLevel');
    $this->compareComposerConfig('repositories', 'drupal');
    $this->compareComposerConfig('scripts', 'nuke');
  }

  /**
   * Check BLT.
   */
  protected function checkBltRequireDev() {
    if (empty($this->composerJson['require-dev']['acquia/blt-require-dev'])) {
      $this->logProblem('acquia/blt-require-dev', [
        "acquia/blt-require-dev is not defined as a development dependency in composer.json",
        "  Move add acquia/blt-require-dev to the require-dev object in composer.json.",
        "  This is necessary for BLT to run development tasks.",
      ], 'error');
    }
  }

  /**
   * Compare composer.
   */
  private function compareComposerConfig($key1, $key2) {
    if (!array_key_exists($key1, $this->composerJson) ||
      !array_key_exists($key2, $this->composerJson[$key1])) {
      $project_values = NULL;
    }
    else {
      $project_values = json_encode($this->composerJson[$key1][$key2], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
    $template_values = json_encode($this->templateComposerJson[$key1][$key2], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    $has_diff = $template_values != $project_values;

    if ($has_diff) {
      $this->logProblem("composer.$key1.$key2", [
        "The Composer configuration for $key1.$key2 differs from BLT's default, recommended values.",
        "  Change your configuration to match BLT's defaults in",
        "  vendor/acquia/blt/subtree-splits/blt-project/composer.json.",
      ], 'error');
    }
  }

}
