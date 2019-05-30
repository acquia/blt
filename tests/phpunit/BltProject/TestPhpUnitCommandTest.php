<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class TestPhpUnitCommandTest.
 *
 * @group orca_ignore
 */
class TestPhpUnitCommandTest extends BltProjectTestBase {

  /**
   * @var string
   */
  protected $docroot;

  /**
   * @var string
   */
  protected $reporoot;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->blt('recipes:blt:init:command');
    $this->docroot = $this->config->get("docroot");
    $this->reporoot = $this->config->get("repo.root");
  }

  /**
   * Test that ExampleTest.php is correctly executed and passes.
   */
  public function testPhpUnitCommandExampleTests() {
    list($status_code, $output, $config) = $this->blt("tests:phpunit:run", [
      "--define" => [
        "tests.phpunit.0.config=$this->docroot/core/phpunit.xml.dist",
        "tests.phpunit.0.path=$this->reporoot/tests/phpunit",
        "tests.phpunit.0.directory=$this->reporoot/tests/phpunit",
      ],
    ]);
    $results = file_get_contents($this->sandboxInstance . '/reports/phpunit/results.xml');
    $this->assertContains('tests="1" assertions="1"', $results);
    $this->assertContains('name="testExample" class="My\Example\Project\Tests\ExampleTest"', $results);
  }

  /**
   * Tests that removing ExampleTest.php doesn't cause failure for users.
   */
  public function testPhpUnitCommandNoTests() {
    $this->fs->remove($this->sandboxInstance . "/tests/phpunit/ExampleTest.php");
    list($status_code, $output, $config) = $this->blt("tests:phpunit:run", [
      "--define" => [
        "tests.phpunit.0.config=$this->docroot/core/phpunit.xml.dist",
        "tests.phpunit.0.path=$this->reporoot/tests/phpunit",
        "tests.phpunit.0.directory=$this->reporoot/tests/phpunit",
      ],
    ]);
    $this->assertEquals(0, $status_code);
  }

}
