<?php

namespace Acquia\Blt\Robo\Config;

use Symfony\Component\Finder\Finder;

/**
 * Default configuration for BLT.
 */
class DefaultConfig extends BltConfig {

  /**
   * Constructor.
   */
  public function __construct() {
    parent::__construct();

    $repo_root = $this->getRepoRoot();
    $this->set('repo.root', $repo_root);
    $this->set('docroot', $repo_root . '/docroot');
    $this->set('blt.root', $this->getBltRoot());
    $this->set('composer.bin', $repo_root . '/vendor/bin');
  }

  /**
   * Gets the repository root.
   *
   * @return string
   *   The filepath for the repository root.
   *
   * @throws \Exception
   */
  protected function getRepoRoot() {
    $possible_repo_roots = [
      $_SERVER['PWD'],
      realpath($_SERVER['PWD'] . '/..'),
      getcwd(),
    ];
    foreach ($possible_repo_roots as $possible_repo_root) {
      if (file_exists("$possible_repo_root/vendor/acquia/blt")
        ||file_exists("$possible_repo_root/blt/project.yml")) {
        return $possible_repo_root;
      }
    }

    throw new \Exception('Could not find repository root directory!');
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

    throw new \Exception('Could not find the Drupal docroot directory');
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
