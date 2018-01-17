<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class CloudHookTest.
 */
class CloudHookTest extends BltProjectTestBase {

  /**
   * Tests setup:cloud-hooks command.
   *
   * @group blted8
   */
  public function testSetupCloudHooks() {
    $this->blt('setup:cloud-hooks');
    $this->assertFileExists($this->sandboxInstance . '/hooks');

    $commonPostCodeDeployScript = $this->sandboxInstance . '/hooks/common/post-code-deploy/post-code-deploy.sh';
    $this->assertFileExists($commonPostCodeDeployScript);

    $filePermissions = substr(sprintf('%o', fileperms($commonPostCodeDeployScript)), -4);
    $this->assertEquals('0755', $filePermissions);
  }

}
