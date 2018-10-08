<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class DeployTest.
 */
class DeployTest extends BltProjectTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->deploy_dir = $this->sandboxInstance . '/deploy';
  }

  /**
   * Tests artifact:build command.
   */
  public function testBltDeployBuild() {
    $this->blt('recipes:cloud-hooks:init');
    $this->blt('artifact:build');

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
    $this->assertFileExists($this->deploy_dir . '/blt/blt.yml');
    $this->assertFileNotExists($this->deploy_dir . '/tests');

    // Ensure deployment_identifier is in the deploy directory.
    $this->assertFileExists($this->deploy_dir . '/deployment_identifier');
    $this->assertNotEmpty(file_get_contents($this->deploy_dir . '/deployment_identifier'));
  }

  // @todo add artifact:build:push test.
  // git.remotes.1 git@github.com:acquia-pso/blted8.git
  // @todo add artifact:update:drupal test.

}
