<?php

namespace Drupal;

use Symfony\Component\Yaml\Yaml;

/**
 * Class BLTTest.
 *
 * Verifies that project structure and configuration matches BLT
 * standards.
 */
class BltTest extends \PHPUnit_Framework_TestCase {

  /**
   * Class constructor.
   */
  public function __construct() {

    $this->projectDirectory = realpath(dirname(__FILE__) . '/../../');
    $this->config = Yaml::parse(file_get_contents("{$this->projectDirectory}/project.yml"));
    $this->new_project_dir = dirname($this->projectDirectory) . '/' . $this->config['project']['machine_name'];
  }

  /**
   * Tests Phing pt:create target.
   */
  public function testBltCreate() {

    $this->assertFileExists($this->new_project_dir);
    $this->assertFileNotExists($this->new_project_dir . '/install');
    $this->assertFileNotExists($this->new_project_dir . '/tests/phpunit/BltTest.php');
    $this->assertFileExists($this->new_project_dir . '/vendor');
    $this->assertNotContains(
          'pt:self-test',
          file_get_contents($this->new_project_dir . '/.travis.yml')
      );
    $this->assertFileNotExists($this->new_project_dir . '/build/tasks/blt.xml');
    $this->assertNotContains(
          '${project.machine_name}',
          file_get_contents($this->new_project_dir . '/docroot/sites/default/settings.php')
      );
    $this->assertNotContains(
          '${project.human_name}',
          file_get_contents($this->new_project_dir . '/readme/architecture.md')
      );
    $profile_dir = $this->new_project_dir . '/docroot/profiles/contrib/' .
          $this->config['project']['profile']['name'];

    // Test new installation profile.
    if (!$this->config['project']['profile']['contrib']) {
      $this->assertFileExists($profile_dir . '/' . $this->config['project']['profile']['name'] . '.info.yml');
      $this->assertFileExists($profile_dir . '/' . $this->config['project']['profile']['name'] . '.install');
      $this->assertNotContains(
            '${project.profile.name}',
            file_get_contents($profile_dir . '/' . $this->config['project']['profile']['name'] . '.install')
        );
    }
  }

}
