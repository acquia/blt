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
  protected $config = [];

  /**
   * BltProjectTestBase constructor.
   *
   * @inheritdoc
   */
  public function __construct($name = NULL, array $data = [], $dataName = '') {
    parent::__construct($name, $data, $dataName);

    // This seems super hacky, but when blt is included as a symlink in the
    // test project, php resolves the symlink and we end up in the wrong
    // directory when Travis runs the tests.
    if (strpos(__DIR__, 'vendor/acquia') !== FALSE) {
      $this->projectDirectory = dirname(dirname(dirname(dirname(dirname(dirname((__DIR__)))))));
    }
    else {
      $this->projectDirectory = getcwd();
    }

    $this->drupalRoot = $this->projectDirectory . '/docroot';
    if (file_exists("{$this->projectDirectory}/project.yml")) {
      $this->config = Yaml::parse(file_get_contents("{$this->projectDirectory}/project.yml"));
    }
    if (file_exists("{$this->projectDirectory}/project.local.yml")) {
      $this->config = array_replace_recursive($this->config, Yaml::parse(file_get_contents("{$this->projectDirectory}/project.local.yml")));
    }
  }

}
