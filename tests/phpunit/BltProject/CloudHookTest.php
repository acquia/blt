<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class CloudHookTest.
 *
 * Verifies that Acquia cloud hook support has been initialized.
 */
class CloudHookTest extends BltProjectTestBase {

  /**
   * Tests setup:cloud-hooks command.
   *
   * @group blt-project
   */
  public function testSetupCloudHooks() {
    $this->assertFileExists($this->projectDirectory . '/hooks');

    $commonPostCodeDeployScript = $this->projectDirectory . '/hooks/common/post-code-deploy/post-code-deploy.sh';
    $this->assertFileExists($commonPostCodeDeployScript);

    $filePermissions = substr(sprintf('%o', fileperms($commonPostCodeDeployScript)), -4);
    $this->assertEquals('0755', $filePermissions);
  }

}
