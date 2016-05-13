<?php

namespace Drupal;

use Symfony\Component\Yaml\Yaml;

/**
 * Class DeployTest.
 *
 * Verifies that build artifact matches standards.
 */
class DeployTest extends \PHPUnit_Framework_TestCase {

  /**
   * Class constructor.
   */
  public function __construct() {

    $this->projectDirectory = realpath(dirname(__FILE__) . '/../../');
    $this->config = Yaml::parse(file_get_contents("{$this->projectDirectory}/project.yml"));
    $this->new_project_dir = dirname($this->projectDirectory) . '/' . $this->config['project']['machine_name'];
    $this->deploy_dir = $this->new_project_dir . '/deploy';
  }

  /**
   * Tests Phing deploy:build:all target.
   */
  public function testBoltDeployBuild() {

    // Ensure deploy directory exists.
    $this->assertFileExists($this->deploy_dir);

    // Ensure docroot was built into to deploy directory.
    $this->assertFileExists($this->deploy_dir . '/docroot');
    $this->assertFileExists($this->deploy_dir . '/docroot/core');
    $this->assertFileExists($this->deploy_dir . '/docroot/modules/contrib');

    // Ensure settings files were copied to deploy directory.
    $this->assertFileExists($this->deploy_dir . '/docroot/index.php');
    $this->assertFileExists($this->deploy_dir . '/docroot/autoload.php');
    $this->assertFileExists($this->deploy_dir . '/composer.lock');
    $this->assertFileExists($this->deploy_dir . '/docroot/sites/default/settings.php');
    $this->assertFileNotExists($this->deploy_dir . '/docroot/sites/default/settings/local.settings.php');

    // Ensure hooks were copied to deploy directory.
    $this->assertFileExists($this->deploy_dir . '/hooks');
    $this->assertFileExists($this->deploy_dir . '/hooks/README.md');

    // Ensure deploy directory was sanitized.
    $this->assertFileNotExists($this->deploy_dir . '/docroot/LICENSE.txt');
  }

  /**
   * Tests Phing deploy:build:push target.
   */
  public function testBoltDeployPush() {

    global $_ENV;
    $deploy_branch = '8.x-build';

    foreach ($this->config['deployments'] as $deployment_target) {

      // Default is a single git, so pulling that one out.
      $remote = $this->config[$deployment_target]['gits'][0];
      $commands = [
        "git remote add temp $remote",
        "git fetch temp $deploy_branch",
        "git log temp/$deploy_branch",
        "git remote rm temp",
      ];

      $log = '';
      foreach ($commands as $command) {
        print "Executing \"$command\" \n";
        $log .= shell_exec($command);
      }

      // We expect the remote git log to contain a commit message matching
      // the current build number, unless this build has not introduced
      // any new changes. Example message:
      // "Automated commit by Travis CI for Build #$travis_build_id".
      if (!empty($_ENV['DEPLOY_UPTODATE'])) {
        $this->assertContains('#' . $_ENV['TRAVIS_BUILD_ID'], $log);
      }
    }
  }

}
