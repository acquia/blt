<?php

namespace Acquia\Blt\Robo\Config;

use Acquia\Blt\Robo\Exceptions\BltException;
use Consolidation\Config\Loader\YamlConfigLoader;
use Symfony\Component\Finder\Finder;

/**
 * Default configuration for BLT.
 */
class DefaultConfig extends BltConfig {

  /**
   * DefaultConfig constructor.
   *
   * @param string $repo_root
   *   The repository root of the project that depends on BLT.
   */
  public function __construct($repo_root) {
    parent::__construct();

    $this->set('repo.root', $repo_root);
    $this->set('docroot', $repo_root . '/docroot');
    $this->set('blt.root', $this->getBltRoot());
    $this->set('composer.bin', $repo_root . '/vendor/bin');
  }

  /**
   * Gets the BLT root directory. E.g., /vendor/acquia/blt.
   *
   * @return string
   *   THe filepath for the Drupal docroot.
   *
   * @throws \Exception
   */
  protected function getBltRoot() {
    $possible_blt_roots = [
      dirname(dirname(dirname(dirname(__FILE__)))),
      dirname(dirname(dirname(__FILE__))),
    ];
    foreach ($possible_blt_roots as $possible_blt_root) {
      if (file_exists("$possible_blt_root/template")) {
        return $possible_blt_root;
      }
    }

    throw new BltException('Could not find the Drupal docroot directory');
  }

  /**
   * Populates configuration settings not available during construction.
   */
  public function populateHelperConfig() {
    $defaultAlias = $this->get('drush.default_alias');
    $alias = $defaultAlias == 'self' ? '' : $defaultAlias;
    $this->set('drush.alias', $alias);
    if (!$this->get('multisites')) {
      $this->set('multisites', $this->getSiteDirs());
    }
  }

  /**
   * Sets multisite context by settings site-specific config values.
   *
   * @param string $site_name
   *   The name of a multisite. E.g., if docroot/sites/example.com is the site,
   *   $site_name would be example.com.
   */
  public function setSiteConfig($site_name) {
    $this->config->set('site', $site_name);
    if (!$this->config->get('drush.uri')) {
      $this->config->set('drush.uri', $site_name);
    }

    // After having set site, this should now return the multisite
    // specific config.
    $site_config_file = $this->get('blt.config-files.multisite');
    $this->importYamlFile($site_config_file);
  }

  /**
   * Sets multisite context by settings site-specific config values.
   *
   * @param string $file_path
   *   The file path to the config yaml file.
   */
  public function importYamlFile($file_path) {
    $loader = new YamlConfigLoader();
    $processor = new YamlConfigProcessor();
    $processor->add($this->config->export());
    $processor->extend($loader->load($file_path));
    $this->config->import($processor->export());
  }

  /**
   * Gets an array of sites for the Drupal application.
   *
   * I.e., sites under docroot/sites, not including acsf 'g' pseudo-site.
   *
   * @return array
   *   An array of sites.
   */
  protected function getSiteDirs() {
    $sites_dir = $this->get('docroot') . '/sites';
    $sites = [];

    // If BLT's template has not yet been rsynced into the project root, it is
    // possible that docroot/sites does not exist.
    if (!file_exists($sites_dir)) {
      return $sites;
    }

    $finder = new Finder();
    $dirs = $finder
      ->in($sites_dir)
      ->directories()
      ->depth('< 1')
      ->exclude(['g']);
    foreach ($dirs->getIterator() as $dir) {
      $sites[] = $dir->getRelativePathname();
    }

    return $sites;
  }

}
