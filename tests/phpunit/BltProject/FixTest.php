<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class FixTasksTest.
 *
 * Verifies that fix related tasks work as expected.
 */
class FixTasksTest extends BltProjectTestBase {
  
  public function testFixPhpcbfExitsWhenMissingArguments() {
    chdir($this->projectDirectory);
    $output =  [];
    $return_var = 0;
    exec('sh vendor/acquia/blt/scripts/blt/phpcbf-file-list.sh', $output, $return_var);
    $this->assertContains("Missing file list parameter.", $output);
    $this->assertEquals(1, $return_var);
  }

}
