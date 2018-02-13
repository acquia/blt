<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

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

    list($status_code, $output, $config) = $this->blt("artifact:ac-hooks:post-code-update", [
      's1',
      'dev',
      'master',
      'master',
      's1@svn-3.bjaspan.hosting.acquia.com:s1.git',
      'git',
    ]);
    $this->assertEquals(0, $status_code);
    $this->assertContains('Running updates for environment: dev', $output);
    $this->assertContains('Finished updates for environment: dev', $output);

    list($status_code, $output, $config) = $this->blt("artifact:ac-hooks:post-code-deploy", [
      's1',
      'dev',
      'master',
      'master',
      's1@svn-3.bjaspan.hosting.acquia.com:s1.git',
      'git',
    ]);
    $this->assertEquals(0, $status_code);
    $this->assertContains('Running updates for environment: dev', $output);
    $this->assertContains('Finished updates for environment: dev', $output);
  }

}
