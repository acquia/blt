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
    if ($this->input->hasParameterOption('site')) {
      $site = $this->input->getParameterOption('site');
    }
    elseif ($this->input->hasParameterOption('--site')) {
      $site = $this->input->getParameterOption('--site');
    }
    else {
      $site = 'default';
    }

    return $site;
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
    $this->determineEnvironment();
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
   * @return $this
   *   Env.
   */
  public function determineEnvironment() {
    // Support BLT_ENV=ci.
    if (getenv("BLT_ENV")) {
      $environment = getenv("BLT_ENV");
    }
    // Support --environment=ci.
    elseif ($this->input->hasParameterOption('--environment')) {
      $environment = $this->input->getParameterOption('--environment');
    }
    // Support --define environment=ci.
    elseif ($this->input->hasParameterOption('environment')) {
      $environment = ltrim($this->input->getParameterOption('environment'), '=');
    }
    elseif (EnvironmentDetector::isCiEnv()) {
      $environment = 'ci';
    }
    else {
      $environment = 'local';
    }

    $this->environment = $environment;
    $this->config->set('environment', $environment);

    return $this;
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
