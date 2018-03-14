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
    $this->assertContains('tests/phpunit/phpunit.xml', $output);
    $this->assertContains('OK (1 test, 1 assertion)', $output);
  }

}
