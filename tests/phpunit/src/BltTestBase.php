<?php

namespace Acquia\Blt\Tests;

use Symfony\Component\Yaml\Yaml;

/**
 * Class BltTestBase.
 *
 * Base class for all tests that are executed for BLT itself.
 */
abstract class BltTestBase extends \PHPUnit_Framework_TestCase {

  protected $bltDirectory;
  protected $newProjectDir;
  protected $drupalRoot;
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->bltDirectory = realpath(dirname(__FILE__) . '/../../');

    // Symlink scenario.
    $this->newProjectDir = dirname(dirname($this->bltDirectory)) . '/blt-project';
    // Non-symlink scenario.
    if (!file_exists($this->newProjectDir . '/blt/project.yml')) {
      $this->newProjectDir = realpath(dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME']))));
    }

    $this->config = Yaml::parse(file_get_contents("{$this->newProjectDir}/blt/project.yml"));

  }

}
