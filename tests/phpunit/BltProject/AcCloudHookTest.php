<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class AcCloudHookTest.
 */
class AcCloudHookTest extends BltProjectTestBase {

  /**
   * Tests recipes:cloud-hooks:init command.
   */
  public function testSetupCloudHooks() {
    $this->blt('recipes:cloud-hooks:init');
    $this->assertFileExists($this->sandboxInstance . '/hooks');

    $commonPostCodeDeployScript = $this->sandboxInstance . '/hooks/common/post-code-deploy/post-code-deploy.sh';
    $this->assertFileExists($commonPostCodeDeployScript);

    $filePermissions = substr(sprintf('%o', fileperms($commonPostCodeDeployScript)), -4);
    $this->assertEquals('0755', $filePermissions);
  }

  public function testCloudHooks() {
    $this->blt('recipes:cloud-hooks:init');

    $fs = new Filesystem();
    $fs->symlink($this->sandboxInstance, '/var/www/html/s1.dev');

    $process = $this->execute("./hooks/common/post-code-update/post-code-update.sh s1 dev master master s1@svn-3.bjaspan.hosting.acquia.com:s1.git git");
    $this->assertContains('Running updates for environment: dev', $process->getOutput());
    $this->assertContains('artifact:update:drupal:all-sites', $process->getOutput());
    $this->assertContains('Finished updates for environment: dev', $process->getOutput());

    $process = $this->execute("./hooks/common/post-code-deploy/post-code-deploy.sh s1 dev master master s1@svn-3.bjaspan.hosting.acquia.com:s1.git git");
    $this->assertContains('Running updates for environment: dev', $process->getOutput());
    $this->assertContains('artifact:update:drupal:all-sites', $process->getOutput());
    $this->assertContains('Finished updates for environment: dev', $process->getOutput());
  }

}
