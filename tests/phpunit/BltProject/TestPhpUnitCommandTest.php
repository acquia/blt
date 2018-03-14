<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class TestPhpUnitCommandTest.
 */
class TestPhpUnitCommandTest extends BltProjectTestBase {

  /**
   * Test that ExampleTest.php is correctly executed and passes.
   */
  public function testPhpUnitCommandExampleTests() {
    list($status_code, $output, $config) = $this->blt("tests:phpunit:run");
    $results = file_get_contents($this->sandboxInstance . '/reports/phpunit/results.xml');
    $this->assertContains('tests="1" assertions="1"', $results);
    $this->assertContains('name="testExample" class="My\Example\Project\Tests\ExampleTest"', $results);
  }

  /**
   * Tests that removing ExampleTest.php doesn't cause failure for users.
   */
  public function testPhpUnitCommandNoTests() {
    $this->fs->remove($this->sandboxInstance . "/tests/phpunit/ExampleTest.php");
    list($status_code, $output, $config) = $this->blt("tests:phpunit:run");
  }

}
