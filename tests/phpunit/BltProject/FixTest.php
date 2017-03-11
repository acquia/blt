<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class FixTasksTest.
 *
 * Verifies that fix related tasks work as expected.
 */
class FixTasksTest extends BltProjectTestBase {

  private $returnVar;
  private $output = [];

  /**
   * Tests for existence of phpcbf script.
   */
  public function testFixPhpcbfExists() {
    $this->executePhpcbfFileListScript();
    $this->assertNotContains("PHP Code Beautifier was not found in this project's bin directory. Please run composer install.", $this->output);
    $this->assertEquals(1, $this->returnVar);
  }

  /**
   * Tests the handling of a missing file parameter.
   */
  public function testFixPhpcbfExitsWhenMissingArguments() {
    $this->executePhpcbfFileListScript();
    $this->assertContains("Missing file list parameter.", $this->output);
    $this->assertEquals(1, $this->returnVar);
  }

  /**
   * Tests that a file list is not generated, given a file without violations.
   */
  public function testFixPhpcbfWithNotFixableFile() {
    $file = $this->createTestFile('file_without_violations.php', FALSE);
    $report = $this->createTestReport('report_without_violations.txt', $file);
    $this->executePhpcbfFileListScript($report);
    $this->assertNotContains("Missing file list parameter.", $this->output);
    $this->assertContains("No fixable files found.", $this->output);
    $this->assertEquals(0, $this->returnVar);
  }

  /**
   * Tests that a file list is generated, given a file with a fixable violation.
   */
  public function testFixPhpcbfWithFixableFile() {
    $file = $this->createTestFile('file_with_violations.php');
    $report = $this->createTestReport('report_with_violations.txt', $file);
    $this->executePhpcbfFileListScript($report);
    $this->assertNotContains("Missing file list parameter.", $this->output);

    foreach ($this->output as $value) {
      if (!file_exists($value)) {
        $this->assertEquals('Files that can be fixed by PHPCBF:', $value);
      }
      else {
        $this->assertStringEndsWith('file_with_violations.php', $value);
        $this->assertStringEndsNotWith('file_without_violations.php', $value);
        $phpcbf_first_pass = $this->executePhpcbf($value);
        $this->assertContains("Processing file_with_violations.php", $phpcbf_first_pass);

        // Run phpcbf a second time to ensure the file has been fixed.
        $phpcbf_second_pass = $this->executePhpcbf($value);
        $this->assertContains("No fixable errors were found", $phpcbf_second_pass);
      }
    }
    $this->assertEquals(0, $this->returnVar);
  }

  /**
   * Helper method that executes the phpcbf-file-list.sh script.
   *
   * @param string $file
   *   The full path to the file to be processed.
   */
  protected function executePhpcbfFileListScript($file = '') {
    chdir($this->projectDirectory);
    $command = "sh vendor/acquia/blt/scripts/blt/phpcbf-file-list.sh $file";
    exec($command, $this->output, $this->returnVar);
  }

  /**
   * Helper method to run phpcbf.
   *
   * @param string $file
   *   The full path to the file to be processed.
   *
   * @return string
   *   The output of the executed phpcbf command.
   */
  protected function executePhpcbf($file) {
    chdir($this->projectDirectory);
    return shell_exec("./vendor/bin/phpcbf --standard=vendor/drupal/coder/coder_sniffer/Drupal/ruleset.xml {$file}");
  }

  /**
   * Helper method to create a phpcs csv file report.
   *
   * @param string $report_name
   *   The name of the report file to be generated.
   * @param string $file
   *   The full path to the file to be processed.
   *
   * @return string
   *   The full path to the generated csv report file.
   */
  protected function createTestReport($report_name, $file) {
    chdir($this->projectDirectory);
    $report_path = sys_get_temp_dir() . '/' . $report_name;
    exec("./vendor/bin/phpcs --standard=vendor/drupal/coder/coder_sniffer/Drupal/ruleset.xml --report=csv --report-file={$report_path} {$file}");
    return $report_path;
  }

  /**
   * Helper method to create a php file.
   *
   * @param string $file_name
   *   The name of the file to be generated.
   * @param bool $with_violations
   *   Boolean indicating whether the file should contain a fixable violation.
   *
   * @return string
   *   The full path to the generated php file.
   */
  protected function createTestFile($file_name, $with_violations = TRUE) {
    $file_path = sys_get_temp_dir() . '/' . $file_name;

    $file_contents = "<?php

/**
 * @file
 * A file to fix.
 */

/**
 * A function that doesn't need to be fixed by phpcbf.
 */
function first_test_function() {
}
";

    if ($with_violations) {
      $file_contents .= "
/**
 * A function that needs to be fixed by phpcbf.
 */
function second_test_function(){
}
";
    }
    file_put_contents($file_path, $file_contents);
    return $file_path;
  }

}
