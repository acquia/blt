<?php

namespace Acquia\Blt\Tests;

use Acquia\Blt\Robo\Config\ConfigInitializer;
use Symfony\Component\Console\Input\ArrayInput;

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
    // Initialize configuration.
    $config_initializer = new ConfigInitializer($this->projectDirectory, new ArrayInput([]));
    $this->config = $config_initializer->initialize()->export();

    // Build sites list.
    $this->sites = !empty($this->config['multisite']['name']) ? $this->config['multisite']['name'] : ['default'];
  }

  /**
   * @param $command
   *
   * @return mixed
   */
  protected function drush($command) {
    chdir($this->drupalRoot);
    $drush_bin = $this->projectDirectory . '/vendor/bin/drush';
    $command_string = "$drush_bin $command --format=json --no-interaction --no-ansi";
    $output = shell_exec($command_string);

    return json_decode($output, TRUE);
  }

}
