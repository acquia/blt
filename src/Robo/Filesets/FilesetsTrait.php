<?php

namespace Acquia\Blt\Robo\Filesets;

use Symfony\Component\Finder\Finder;

/**
 * Defines a trait for filesets helper methods.
 */
trait FilesetsTrait {

  /**
   * Adds Drupalistic PHP patterns to a Symfony finder object.
   *
   * @return \Symfony\Component\Finder\Finder
   *   The finder object.
   */
  protected function getPhpFilesetFinder() {
    $finder = new Finder();
    $finder
      ->files()
      ->name("*.inc")
      ->name("*.install")
      ->name("*.module")
      ->name("*.php")
      ->name("*.profile")
      ->name("*.test")
      ->name("*.theme")
      // Behat php files are ignored because method names and comments do not
      // conform to Drupal coding standards by default.
      ->notPath('behat')
      ->notPath('node_modules')
      ->notPath('vendor');
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
