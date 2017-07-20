<?php

namespace Acquia\Blt\Robo\Filesets;

// Do not remove this, even though it appears to be unused.
// @codingStandardsIgnoreStart
use Acquia\Blt\Annotations\Fileset;
// @codingStandardsIgnoreEnd
use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Robo\Contract\ConfigAwareInterface;
use Symfony\Component\Finder\Finder;

/**
 *
 */
class Filesets implements ConfigAwareInterface {

  use ConfigAwareTrait;

  /**
   * @fileset(id="files.frontend.custom.themes")
   *
   * @return \Symfony\Component\Finder\Finder
   */
  public function getFilesetFrontend() {
    $finder = $this->getFrontendFilesetFinder();
    $finder->in([$this->getConfigValue('docroot') . '/themes/custom']);

    return $finder;
  }

  /**
   * @fileset(id="files.twig")
   *
   * @return \Symfony\Component\Finder\Finder
   */
  public function getFilesetTwig() {
    $finder = $this->getTwigFilesetFinder();
    $finder->in([$this->getConfigValue('docroot') . '/themes/custom']);
    $finder->in([$this->getConfigValue('docroot') . '/modules/custom']);

    return $finder;
  }

  /**
   * @fileset(id="files.yaml")
   *
   * @return \Symfony\Component\Finder\Finder
   */
  public function getFilesetYaml() {
    $finder = $this->getYamlFilesetFinder();
    $finder->in([
      $this->getConfigValue('repo.root') . '/config',
      $this->getConfigValue('docroot') . '/modules/custom',
    ]);

    return $finder;
  }

  /**
   * Adds Drupalistic JS patterns to a Symfony finder object.
   *
   * @return \Symfony\Component\Finder\Finder
   *   The finder object.
   */
  protected function getFrontendFilesetFinder() {
    $finder = new Finder();
    $finder
      ->files()
      ->path("*/js/*")
      ->name("*.js")
      ->notName('*.min.js')
      ->notPath('bower_components')
      ->notPath('node_modules')
      ->notPath('vendor');

    return $finder;
  }

  /**
   * Adds Drupalistic YAML patterns to a Symfony finder object.
   *
   * @return \Symfony\Component\Finder\Finder
   *   The finder object.
   */
  protected function getYamlFilesetFinder() {
    $finder = new Finder();
    $finder
      ->files()
      ->name("*.yml")
      ->name("*.yaml")
      ->notPath('bower_components')
      ->notPath('node_modules')
      ->notPath('vendor');

    return $finder;
  }

  /**
   * Adds Drupalistic Twig patterns to a Symfony finder object.
   *
   * @return \Symfony\Component\Finder\Finder
   *   The finder object.
   */
  protected function getTwigFilesetFinder() {
    $finder = new Finder();
    $finder
      ->files()
      ->name("*.twig")
      ->notPath('bower_components')
      ->notPath('node_modules')
      ->notPath('vendor');

    return $finder;
  }

}
