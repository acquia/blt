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
   * Find Robo config options.
   *
   * At this stage, Robo is not initialized, so commandline config options like
   * --define=site=example.com have not been parsed and added to config.
   * We need to parse them ourselves.
   *
   * We have the InputInterface available to do this. There appear to be 4
   * valid forms Robo will accept these arguments in:
   * * --define=site=example.com
   * * --define site=example.com
   * * -Dsite=example.com
   * * -D site=example.com
   *
   * @param string $param
   * @return mixed
   */
  private function getRoboOption($param = '') {
    if ($param) {
      // The case where the flag and the value are separated is easiest, since
      // InputInterface->hasParameterOption($option) works here.
      if ($this->input->hasParameterOption($param)) {
        return $this->input->getParameterOption($param);
      }
      // Check the -Dsite case.
      if ($this->input->hasParameterOption("-D$param")) {
        return $this->input->getParameterOption("-D$param");
      }
      // InterfaceInput->getParameterOption() returns the first matching
      // item, rather than returning an array of all values, so passing
      // --define=site=example.com --define=environment=stage will fail.
      // We need to do the parsing ourselves.
      if ($this->input->hasParameterOption("--define")) {
        $parameter_string = $this->input->__toString();
        if (preg_match_all('/define=\'(.*?)\'/', $parameter_string, $matches, PREG_PATTERN_ORDER)) {
          foreach ($matches[1] as $match) {
            $split = explode('=', $match);
            // Return on first match of $param.
            if ($split[0] === $param) {
              return $split[1];
            }
          }
        }
      }
    }

    return NULL;
  }

  /**
   * @param mixed $site
   */
  public function setSite($site = '') {
    if (!$site) {
      $site = $this->getRoboOption('site') ?: 'default';
    }

    $this->site = $site;
    $this->config->setSite($site);
  }

  /**
   * @return \Acquia\Blt\Robo\Config\DefaultConfig
   */
  public function initialize() {
    $this->setSite();
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
    $this->loadEnvironmentConfig();

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
    $this->processor->extend($this->loader->load($this->config->get('repo.root') . '/blt/project.yml'));
    $this->processor->extend($this->loader->load($this->config->get('repo.root') . '/blt/project.local.yml'));

    return $this;
  }

  /**
   *
   * @return $this
   */
  public function loadSiteConfig() {
    if ($this->site) {
      $this->processor->extend($this->loader->load($this->config->get('docroot') . '/sites/' . $this->site . '/site.yml'));
    }

    return $this;
  }

  /**
   * @return $this
   */
  public function loadEnvironmentConfig() {
    if ($this->getRoboOption('environment') !== NULL) {
      $this->processor->extend($this->loader->load($this->config->get('repo.root') . '/blt/' . $this->getRoboOption('environment') . '.yml'));
    }

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
