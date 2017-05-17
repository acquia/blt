<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltTestBase;

/**
 * Class CloudHookTest.
 *
 * Verifies that acsf support has been initialized.
 */
class CloudHookTest extends BltTestBase {

  /**
   * Tests setup:cloud-hooks command.
   */
  public function testSetupCloudHooks() {
    $this->assertFileExists($this->new_project_dir . '/hooks');

    $commonPostCodeDeployScript = $this->new_project_dir . '/hooks/common/post-code-deploy/post-code-deploy.sh';
    $this->assertFileExists($commonPostCodeDeployScript);

    $filePermissions = substr(sprintf('%o', fileperms($commonPostCodeDeployScript)), -4);
    $this->assertEquals('0755', $filePermissions);
  }

}
