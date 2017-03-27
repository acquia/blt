<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class HookTest.
 *
 * Verifies that BLT hook variables are set as expected.
 */
class HookTest extends BltProjectTestBase {

  /**
   * Tests whether hook variables are set as expected.
   *
   * @group blt-hooks
   */
  public function testHookVariables() {
    $this->assertVariableEquals('environment', BLT_ENV);
    $this->assertVariableEquals('multisite_name', BLT_MULTISITE_NAME);
  }

  /**
   * Asserts that a given variable is set as expected.
   *
   * @param string $variable
   *   The variable name to check.
   * @param string $expected
   *   The expected value of $variable.
   */
  protected function assertVariableEquals($variable, $expected) {
    $value = $this->getHookVariable($variable);
    $this->assertEquals($expected, $value,
      "Expected value at $variable to equal '$expected'. Instead, $variable equals '$value'.");
  }

  /**
   * Gets the value of a given hook variable.
   *
   * @param string $variable
   *   The property to check.
   *
   * @return string
   *   The value of $variable.
   */
  private function getHookVariable($variable) {
    $output = [];
    $blt_bin = "$this->projectDirectory/vendor/bin/blt";
    // Override normal command execution to use the 'hook-test' namespace and
    // run a command which will echo our specified variable value.
    $namespace = 'hook-test';
    // Run command with minimal output and console styling.
    $cmd = "$blt_bin target-hook:invoke -emacs -silent";
    $cmd .= " -Dhook-name=$namespace";
    $cmd .= " -Dtarget-hooks.$namespace.dir=$this->projectDirectory";
    $cmd .= " -Dtarget-hooks.$namespace.command=\"echo \\$$variable\"";
    exec($cmd, $output);
    // Property value will be output to the 1st line.
    // Return an empty string if $variable does not exist.
    return !empty($output[0]) ? $output[0] : '';
  }

}
