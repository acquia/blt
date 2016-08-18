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
   * Class constructor.
   */
  public function __construct($name = NULL, array $data = array(), $data_name = '') {
    parent::__construct($name, $data, $data_name);

    $this->projectDirectory = dirname(dirname(dirname(dirname(dirname(dirname(__DIR__))))));
    $this->drupalRoot = $this->projectDirectory . '/docroot';
    $this->config = Yaml::parse(file_get_contents("{$this->projectDirectory}/project.yml"));
    if (file_exists("{$this->projectDirectory}/project.local.yml")) {
      $this->config = array_replace_recursive($this->config, Yaml::parse(file_get_contents("{$this->projectDirectory}/project.local.yml")));
    }
  }

}
