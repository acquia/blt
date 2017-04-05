<?php

namespace Acquia\Blt\Robo\Config;

/**
 *
 */
class DefaultConfig extends BltConfig {

  /**
   *
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
   */
  protected function getRepoRoot() {
    $possible_repo_roots = [
      $_SERVER['PWD'],
      $_SERVER['PWD'] . '/..',
      getcwd(),
    ];
    foreach ($possible_repo_roots as $possible_repo_root) {
      if (file_exists("$possible_repo_root/blt/project.yml")) {
        return $possible_repo_root;
        break;
      }
    }
  }

  /**
   *
   */
  protected function getBltRoot() {
    $possible_blt_roots = [
      dirname(dirname(dirname(dirname(__FILE__)))),
      dirname(dirname(dirname(__FILE__))),
    ];
    foreach ($possible_blt_roots as $possible_blt_root) {
      if (file_exists("$possible_blt_root/template")) {
        return $possible_blt_root;
        break;
      }
    }
  }

}
