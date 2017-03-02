<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class FixTasksTest.
 *
 * Verifies that fix related tasks work as expected.
 */
class FixTasksTest extends BltProjectTestBase {

  private $return_var;
  private $output = [];

  /**
   * Tests for existence of phpcbf script.
   */
  public function testFixPhpcbfExists() {
    $this->executePhpcbfFileListScript();
    $this->assertNotContains("PHP Code Beautifier was not found in this project's bin directory. Please run composer install.", $this->output);
    $this->assertEquals(1, $this->return_var);
  }

  /**
   * Tests the handling of a missing file parameter.
   */
  public function testFixPhpcbfExitsWhenMissingArguments() {
    $this->executePhpcbfFileListScript();
    $this->assertContains("Missing file list parameter.", $this->output);
    $this->assertEquals(1, $this->return_var);
  }

  /**
   * Tests that a file list is generated.
   */
  public function testFixPhpcbfWithArguments() {
    $report = $this->createTestReport();
    $this->executePhpcbfFileListScript($report);
    $this->assertNotContains("Missing file list parameter.", $this->output);
    $this->assertContains("Files that can be fixed by PHPCBF:", $this->output);
    $this->assertContains("{$this->drupalRoot}/foo.php", $this->output);
    $this->assertEquals(0, $this->return_var);
  }

  /**
   * Helper method that xecutes the phpcbf-file-list.sh script.
   *
   * @param string $file
   *  The full path to the file to be processed.
   */
  protected function executePhpcbfFileListScript($file = '') {
    chdir($this->projectDirectory);
    $command = "sh vendor/acquia/blt/scripts/blt/phpcbf-file-list.sh $file";
    exec($command, $this->output, $this->return_var);
  }

  /**
   * Helper method to create a temp file, formatted as a phpcs csv report.
   *
   * @param string $tmp_file
   *  The full path to the file to be created.
   */
  protected function createTestReport() {
    $tmp_file = tempnam(sys_get_temp_dir(), '');
    if (!$tmp_file) {
      throw new \Exception("Unable to create temporary file.");
    }
    $file_contents = "\"{$this->projectDirectory}/docroot/foo.php\",1,1,error,\"Line indented incorrectly; expected 2 spaces, found 4\",Drupal.WhiteSpace.ScopeIndent.IncorrectExact,5,1";
    $command = "echo '$file_contents' > {$tmp_file}";
    exec($command);
    return $tmp_file;
  }

}
