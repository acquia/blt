<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltTestBase;

/**
 * Class DeployTest.
 *
 * Verifies that build artifact matches standards.
 */
class DeployTest extends BltTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->deploy_dir = $this->newProjectDir . '/deploy';
  }

  /**
   * Tests deploy:build command.
   *
   * @group deploy
   * @group blt
   */
  public function testBltDeployBuild() {

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
    $this->assertFileExists($this->deploy_dir . '/.gitignore');
    $this->assertFileExists($this->deploy_dir . '/docroot/sites/default/settings.php');
    $this->assertFileNotExists($this->deploy_dir . '/docroot/sites/default/settings/local.settings.php');

    // Ensure hooks were copied to deploy directory.
    $this->assertFileExists($this->deploy_dir . '/hooks');
    $this->assertFileExists($this->deploy_dir . '/hooks/README.md');

    // Ensure deploy directory was sanitized.
    $this->assertFileNotExists($this->deploy_dir . '/docroot/LICENSE.txt');

    // Ensure non-required files are not in deploy dir.
    $this->assertFileNotExists($this->deploy_dir . '/blt.sh');
    $this->assertFileNotExists($this->deploy_dir . '/build');
    $this->assertFileExists($this->deploy_dir . '/blt/project.yml');
    $this->assertFileNotExists($this->deploy_dir . '/tests');
  }

  /**
   * Tests deploy:build:push command.
   *
   * @group deploy
   * @group deploy-push
   * @group blt
   */
  public function testBltDeployPush() {

    global $_ENV;
    $deploy_branch = '8.x-build';

    foreach ($this->config['git']['remotes'] as $remote) {
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
