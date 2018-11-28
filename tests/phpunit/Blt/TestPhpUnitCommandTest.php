<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class TestPhpUnitCommandTest.
 */
class TestPhpUnitCommandTest extends BltProjectTestBase {

  /**
   * Tests that removing ExampleTest.php doesn't cause failure for users.
   */
  public function testPhpUnitCommandNoTests() {
    $this->fs->remove($this->sandboxInstance . "/tests/phpunit/ExampleTest.php");
    $repoRroot = $this->config->get("repo.root");
    list($status_code, $output, $config) = $this->blt("tests:phpunit:run", [
      "--define" => [
        "tests.phpunit.0.config=$repoRroot/tests/phpunit/phpunit.xml",
        "tests.phpunit.0.path=$repoRroot/tests/phpunit",
        "tests.phpunit.0.class=NULL",
      ],
    ]);
    $this->assertEquals(0, $status_code);
  }

  /**
   * Test that ExampleTest.php is correctly executed and passes.
   */
  public function testPhpUnitCommandExampleTests() {
    $this->installDrupalMinimal();
    $docroot = $this->config->get("docroot");
    $repoRroot = $this->config->get("repo.root");
    list($status_code, $output, $config) = $this->blt("tests:phpunit:run", [
      "--define" => [
        "tests.phpunit.0.config=$docroot/core/phpunit.xml.dist",
        "tests.phpunit.0.path=$repoRroot/tests/phpunit",
        "tests.phpunit.0.class=ExampleTest",
      ],
    ]);
    $results = file_get_contents($this->sandboxInstance . '/reports/phpunit/results.xml');
    $this->assertContains('tests="1" assertions="1"', $results);
    $this->assertContains('name="testExample" class="My\Example\Project\Tests\ExampleTest"', $results);
  }

}
