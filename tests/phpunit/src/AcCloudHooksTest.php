<?php

namespace Acquia\Blt\Tests;

/**
 * Test cloud hooks recipes.
 */
class AcCloudHooksTest extends BltProjectTestBase {

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

    // Mimics hooks/post-code-deploy/post-code-deploy.sh.
    [$status_code, $output] = $this->blt("artifact:ac-hooks:post-code-update", [
      'site' => 's1',
      'target_env' => 'dev',
      'source_branch' => 'master',
      'deployed_tag' => 'master',
      'repo_url' => 's1@svn-3.bjaspan.hosting.acquia.com:s1.git',
      'repo_type' => 'git',
    ]);
    $this->assertEquals(0, $status_code);
    $this->assertStringContainsString('Running updates for environment: dev', $output);
    $this->assertStringContainsString('Finished updates for environment: dev', $output);

    // Mimics hooks/post-code-deploy/post-code-deploy.sh.
    [$status_code, $output] = $this->blt("artifact:ac-hooks:post-code-deploy", [
      'site' => 's1',
      'target_env' => 'dev',
      'source_branch' => 'master',
      'deployed_tag' => 'master',
      'repo_url' => 's1@svn-3.bjaspan.hosting.acquia.com:s1.git',
      'repo_type' => 'git',
    ]);
    $this->assertEquals(0, $status_code);
    // @todo Test that using an ACSF env name fails. E.g., 01dev.
    $this->assertStringContainsString('Running updates for environment: dev', $output);
    $this->assertStringContainsString('Finished updates for environment: dev', $output);

    // Mimics hooks/post-db-copy/db-scrub.sh.
    [$status_code, $output] = $this->blt("artifact:ac-hooks:db-scrub", [
      'site' => 's1',
      'target_env' => 'dev',
      'db_name' => 'dev',
      'source_env' => 'dev',
    ]);
    $this->assertEquals(0, $status_code);
    // @todo Test that using an ACSF env name fails. E.g., 01dev.
    $this->assertStringContainsString('Scrubbing database in dev', $output);
  }

}
