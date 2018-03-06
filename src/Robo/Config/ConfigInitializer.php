<?php

namespace Acquia\Blt\Robo\Config;

use Consolidation\Config\Loader\YamlConfigLoader;
use Symfony\Component\Console\Input\InputInterface;

/**
 *
 */
class ConfigInitializer {

  /**
   * @var \Acquia\Blt\Robo\Config\DefaultConfig
   */
  protected $config;
  /**
   * @var \Symfony\Component\Console\Input\InputInterface
   */
  protected $input;
  /**
   * @var \Consolidation\Config\Loader\YamlConfigLoader
   */
  protected $loader;
  /**
   * @var \Acquia\Blt\Robo\Config\YamlConfigProcessor
   */
  protected $processor;

  /**
   * @var string
   */
  protected $site;

  /**
   * @var string
   */
  protected $environment;

  /**
   * ConfigInitializer constructor.
   *
   * @param string $repo_root
   * @param \Symfony\Component\Console\Input\InputInterface $input
   */
  public function __construct($repo_root, InputInterface $input) {
    $this->input = $input;
    $this->config = new DefaultConfig($repo_root);
    $this->loader = new YamlConfigLoader();
    $this->processor = new YamlConfigProcessor();
  }

  /**
   * @param mixed $site
   */
  public function setSite($site) {
    $this->site = $site;
    $this->config->setSite($site);
  }

  /**
   * @return mixed|string
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
   * @return \Acquia\Blt\Robo\Config\DefaultConfig
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
   * @return $this
   */
  public function loadConfigFiles() {
    $this->loadDefaultConfig();
    $this->loadProjectConfig();
    $this->loadSiteConfig();

    return $this;
  }

  /**
   * @return $this
   */
  public function loadDefaultConfig() {
    $this->processor->add($this->config->export());
    $this->processor->extend($this->loader->load($this->config->get('blt.root') . '/config/build.yml'));

    return $this;
  }

  /**
   * @return $this
   */
  public function loadProjectConfig() {
    $this->processor->extend($this->loader->load($this->config->get('repo.root') . '/blt/blt.yml'));
    $this->processor->extend($this->loader->load($this->config->get('repo.root') . "/blt/{$this->environment}.blt.yml"));

    return $this;
  }

  /**
   *
   * @return $this
   */
  public function loadSiteConfig() {
    if ($this->site) {
      $this->processor->extend($this->loader->load($this->config->get('docroot') . "/sites/{$this->site}/blt.yml"));
      $this->processor->extend($this->loader->load($this->config->get('docroot') . "/sites/{$this->site}/{$this->environment}.blt.yml"));
    }

    return $this;
  }

  /**
   * @return $this
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
    else {
      $environment = 'local';
    }

    $this->environment = $environment;
    $this->config->set('environment', $environment);

    return $this;
  }

  /**
   * @return $this
   */
  public function processConfigFiles() {
    $this->config->import($this->processor->export());
    $this->config->populateHelperConfig();

    return $this;
  }

}
