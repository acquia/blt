<?php

namespace Acquia\Blt\Tests;

use Symfony\Component\Yaml\Yaml;

/**
 * Class BltProjectTestBase.
 *
 * Base class for all tests that are executed within a blt project.
 */
abstract class BltProjectTestBase extends \PHPUnit_Framework_TestCase {

  protected $projectDirectory;
  protected $drupalRoot;
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->projectDirectory = $this->projectDirectory = dirname(dirname(dirname((__DIR__))));
    $this->drupalRoot = $this->projectDirectory . '/docroot';
    $this->config = Yaml::parse(file_get_contents("{$this->projectDirectory}/project.yml"));
    if (file_exists("{$this->projectDirectory}/project.local.yml")) {
      $this->config = array_replace_recursive($this->config, Yaml::parse(file_get_contents("{$this->projectDirectory}/project.local.yml")));
    }
  }

}
