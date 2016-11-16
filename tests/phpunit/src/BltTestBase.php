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
  protected $drupalRoot;
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->bltDirectory = realpath(dirname(__FILE__) . '/../../');
    $this->newProjectDir = dirname(dirname($this->bltDirectory)) . '/blt-project';
    $this->config = Yaml::parse(file_get_contents("{$this->newProjectDir}/blt/project.yml"));

  }

}
