<?php

namespace Acquia\Blt\Robo\Filesets;

// Do not remove this, even though it appears to be unused.
// @codingStandardsIgnoreStart
use Acquia\Blt\Annotations\Fileset;
// @codingStandardsIgnoreEnd
use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Robo\Contract\ConfigAwareInterface;

/**
 * Defines filesets.
 */
class Filesets implements ConfigAwareInterface {

  use ConfigAwareTrait;
  use FilesetsTrait;

  /**
   * Get fileset php custom modules.
   *
   * @fileset(id="files.php.custom.modules")
   *
   * @return \Symfony\Component\Finder\Finder
   *   Finder.
   */
  public function getFilesetPhpCustomModules() {
    $finder = $this->getPhpFilesetFinder();
    $finder->in([$this->getConfigValue('docroot') . '/modules/custom']);

    return $finder;
  }

  /**
   * Get fileset php custom themes.
   *
   * @fileset(id="files.php.custom.themes")
   *
   * @return \Symfony\Component\Finder\Finder
   *   Finder.
   */
  public function getFilesetPhpCustomThemes() {
    $finder = $this->getPhpFilesetFinder();
    $finder->in([$this->getConfigValue('docroot') . '/themes/custom']);

    return $finder;
  }

  /**
   * Get fileset php tests.
   *
   * @fileset(id="files.php.tests")
   *
   * @return \Symfony\Component\Finder\Finder
   *   finder
   */
  public function getFilesetPhpTests() {
    $finder = $this->getPhpFilesetFinder();
    $finder->in([$this->getConfigValue('repo.root') . '/tests']);

    return $finder;
  }

  /**
   * Get frontend fileset.
   *
   * @fileset(id="files.frontend.custom.themes")
   *
   * @return \Symfony\Component\Finder\Finder
   *   Finder.
   */
  public function getFilesetFrontend() {
    $finder = $this->getFrontendFilesetFinder();
    $finder->in([$this->getConfigValue('docroot') . '/themes/custom']);

    return $finder;
  }

  /**
   * Get Twig fileset.
   *
   * @fileset(id="files.twig")
   *
   * @return \Symfony\Component\Finder\Finder
   *   finder
   */
  public function getFilesetTwig() {
    $finder = $this->getTwigFilesetFinder();
    $finder->in([$this->getConfigValue('docroot') . '/themes/custom']);
    $finder->in([$this->getConfigValue('docroot') . '/modules/custom']);

    return $finder;
  }

  /**
   * Get YAML fileset.
   *
   * @fileset(id="files.yaml")
   *
   * @return \Symfony\Component\Finder\Finder
   *   Finder.
   */
  public function getFilesetYaml() {
    $finder = $this->getYamlFilesetFinder();
    $finder->in([
      $this->getConfigValue('repo.root') . '/config',
      $this->getConfigValue('docroot') . '/modules/custom',
    ]);

    return $finder;
  }

}
