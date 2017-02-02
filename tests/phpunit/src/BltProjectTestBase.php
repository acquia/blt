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
  protected $sites = [];
  protected $config = [];

  /**
   * BltProjectTestBase constructor.
   *
   * @inheritdoc
   */
  public function __construct($name = NULL, array $data = [], $dataName = '') {
    parent::__construct($name, $data, $dataName);

    // We must consider that this package may be symlinked into its location.
    $repo_root_locations = [
      dirname($_SERVER['SCRIPT_FILENAME']) . '/../..',
      getcwd(),
    ];

    foreach ($repo_root_locations as $location) {
      if (file_exists($location . '/vendor/bin/blt')
        && file_exists($location . '/composer.json')) {
        if ($path = realpath($location)) {
          $this->projectDirectory = $path;
        }
        else {
          $this->projectDirectory = $location;
        }
        break;
      }
    }

    if (empty($this->projectDirectory)) {
      throw new \Exception("Could not find project root directory!");
    }

    $this->drupalRoot = $this->projectDirectory . '/docroot';
    if (file_exists("{$this->projectDirectory}/blt/project.yml")) {
      $this->config = Yaml::parse(file_get_contents("{$this->projectDirectory}/blt/project.yml"));
    }
    else {
      throw new \Exception("Could not find project.yml!");
    }
    if (file_exists("{$this->projectDirectory}/blt/project.local.yml")) {
      $this->config = array_replace_recursive($this->config, (array) Yaml::parse(file_get_contents("{$this->projectDirectory}/blt/project.local.yml")));
    }

    // Build sites list.
    $this->sites = !empty($this->config['multisite']['name']) ? $this->config['multisite']['name'] : ['default'];
  }

}
