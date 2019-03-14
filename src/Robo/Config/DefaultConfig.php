<?php

namespace Acquia\Blt\Robo\Config;

use Acquia\Blt\Robo\Exceptions\BltException;
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
   * Gets the BLT root directory, e.g., /vendor/acquia/blt.
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
    $this->set('drush.alias', $this->get('drush.default_alias'));

    if (!$this->get('multisites')) {
      $this->set('multisites', $this->getSiteDirs());
    }

    $multisites = $this->get('multisites');
    $first_multisite = reset($multisites);
    $site = $this->get('site', $first_multisite);
    $this->setSite($site);
  }

  /**
   * @param $site
   */
  public function setSite($site) {
    $this->config->set('site', $site);
    if (!$this->get('drush.uri') && $site != 'default') {
      $this->set('drush.uri', $site);
    }

  }

  /**
   * Gets an array of sites for the Drupal application.
   *
   * I.e., sites under docroot/sites, not including acsf 'g' pseudo-site and
   * 'settings' directory globbed in blt.settings.php.
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
      ->exclude(['g', 'settings'])
      ->sortByName();
    foreach ($dirs->getIterator() as $dir) {
      $sites[] = $dir->getRelativePathname();
    }

    return $sites;
  }

}
