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
   * Class constructor.
   */
  public function __construct($name = NULL, array $data = array(), $data_name = '') {
    parent::__construct($name, $data, $data_name);

    $this->bltDirectory = realpath(dirname(__FILE__) . '/../../');
    $this->newProjectDir = dirname($this->bltDirectory) . '/blt-project';
    $this->config = Yaml::parse(file_get_contents("{$this->newProjectDir}/project.yml"));
  }

}
