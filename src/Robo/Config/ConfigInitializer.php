<?php

namespace Acquia\Blt\Robo\Config;

use Acquia\Blt\Robo\Common\EnvironmentDetector;
use Consolidation\Config\Loader\YamlConfigLoader;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Config init.
 */
class ConfigInitializer {

  /**
   * Config.
   *
   * @var \Acquia\Blt\Robo\Config\DefaultConfig
   */
  protected $config;
  /**
   * Input.
   *
   * @var \Symfony\Component\Console\Input\InputInterface
   */
  protected $input;
  /**
   * Loader.
   *
   * @var \Consolidation\Config\Loader\YamlConfigLoader
   */
  protected $loader;
  /**
   * Processor.
   *
   * @var \Acquia\Blt\Robo\Config\YamlConfigProcessor
   */
  protected $processor;

  /**
   * Site.
   *
   * @var string
   */
  protected $site;

  /**
   * Environment.
   *
   * @var string
   */
  protected $environment;

  /**
   * ConfigInitializer constructor.
   *
   * @param string $repo_root
   *   Repo root.
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   Input.
   */
  public function __construct($repo_root, InputInterface $input) {
    $this->input = $input;
    $this->config = new DefaultConfig($repo_root);
    $this->loader = new YamlConfigLoader();
    $this->processor = new YamlConfigProcessor();
  }

  /**
   * Set site.
   *
   * @param mixed $site
   *   Site.
   */
  public function setSite($site) {
    $this->site = $site;
    $this->config->setSite($site);
  }

  /**
   * Determine site.
   *
   * @return mixed|string
   *   Site.
   */
  protected function determineSite() {
    // Support --define site=foo.
    if ($site = $this->findDefinedParameter('site')) {
      return $site;
    }
    // Support --site=foo.
    if ($this->input->hasParameterOption('--site')) {
      return $this->input->getParameterOption('--site');
    }
    return 'default';
  }

  /**
   * Find a parameter defined via --define.
   *
   * The --define parameter is used to set config at runtime. However, special
   * config keys (especially site and environment) are used in the BLT bootstrap
   * and thus need to be extracted prior to config being fully processed. This
   * isn't trivial because reasons.
   *
   * @see https://github.com/acquia/blt/issues/4325
   */
  protected function findDefinedParameter($parameter) {
    foreach (['--define', '-D'] as $define) {
      if ($this->input->hasParameterOption($define)) {
        $option = $this->input->getParameterOption($define);
        if (is_string($option)) {
          $parts = explode('=', $option);
          if ($parts[0] === $parameter) {
            return $parts[1];
          }
        }
      }
    }
    return FALSE;
  }

  /**
   * Initialize.
   *
   * @return \Acquia\Blt\Robo\Config\DefaultConfig
   *   Config.
   */
  public function initialize() {
    if (!$this->site) {
      $site = $this->determineSite();
      $this->setSite($site);
    }
    $environment = $this->determineEnvironment();
    $this->environment = $environment;
    $this->config->set('environment', $environment);
    $this->loadConfigFiles();
    $this->processConfigFiles();

    return $this->config;
  }

  /**
   * Load config.
   *
   * @return $this
   *   Config.
   */
  public function loadConfigFiles() {
    $this->loadDefaultConfig();
    $this->loadProjectConfig();
    $this->loadSiteConfig();

    return $this;
  }

  /**
   * Load config.
   *
   * @return $this
   *   Config.
   */
  public function loadDefaultConfig() {
    $this->processor->add($this->config->export());
    $this->processor->extend($this->loader->load($this->config->get('blt.root') . '/config/build.yml'));

    return $this;
  }

  /**
   * Load config.
   *
   * @return $this
   *   Config.
   */
  public function loadProjectConfig() {
    $this->processor->extend($this->loader->load($this->config->get('repo.root') . '/blt/blt.yml'));
    $this->processor->extend($this->loader->load($this->config->get('repo.root') . "/blt/{$this->environment}.blt.yml"));

    return $this;
  }

  /**
   * Load config.
   *
   * @return $this
   *   Config.
   */
  public function loadSiteConfig() {
    if ($this->site) {
      // Since docroot can change in the project, we need to respect that here.
      $this->config->replace($this->processor->export());
      $this->processor->extend($this->loader->load($this->config->get('docroot') . "/sites/{$this->site}/blt.yml"));
      $this->processor->extend($this->loader->load($this->config->get('docroot') . "/sites/{$this->site}/{$this->environment}.blt.yml"));
    }

    return $this;
  }

  /**
   * Determine env.
   *
   * @return string|bool
   *   Env.
   */
  public function determineEnvironment() {
    // Support --environment=ci.
    if ($this->input->hasParameterOption('--environment')) {
      return $this->input->getParameterOption('--environment');
    }
    // Support --define environment=ci.
    if ($environment = $this->findDefinedParameter('environment')) {
      return $environment;
    }
    if (EnvironmentDetector::isCiEnv()) {
      return 'ci';
    }
    return 'local';
  }

  /**
   * Process config.
   *
   * @return $this
   *   Config.
   */
  public function processConfigFiles() {
    $this->config->replace($this->processor->export());
    $this->config->populateHelperConfig();

    return $this;
  }

}
