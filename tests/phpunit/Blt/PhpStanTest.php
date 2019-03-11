<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;
use Symfony\Component\Process\Process;

/**
 * Class PhpStanTest.
 *
 * Verifies that PhpStan works as expected.
 */
class PhpStanTest extends BltProjectTestBase {

  /**
   * @group blt
   *
   * @dataProvider providerPhpStanFilesBootstrapProvider
   */
  public function testPhpStanFilesBootstrap($filename, $needle, $contains) {
    $process = new Process("./vendor/bin/phpstan analyse $filename --configuration=phpstan.neon");
    $process->setWorkingDirectory($this->sandboxInstance);
    $process->run();
    $output = $process->getOutput();

    if ($contains) {
      $this->assertContains($needle, $output);
    }
    else {
      $this->assertNotContains($needle, $output);
    }
  }

  /**
   * Tests recipes:ci:pipelines:init command.
   *
   * @group blted8
   */
  public function testPhpStanSniffAll() {
    $this->blt('tests:phpstan:sniff:all');
    $this->assertFileExists($this->sandboxInstance . '/acquia-pipelines.yml');
  }

  /**
   * Tests recipes:ci:pipelines:init command.
   *
   * @group blted8
   */
  public function testPhpStanSniffFiles() {
    $this->blt('tests:phpstan:sniff:files');
    $this->assertFileExists($this->sandboxInstance . '/acquia-pipelines.yml');
  }

  /**
   * Tests recipes:ci:pipelines:init command.
   *
   * @group blted8
   */
  public function testsPhpstanSniffModified() {
    $this->blt('tests:phpstan:sniff:modified');
    $this->assertFileExists($this->sandboxInstance . '/acquia-pipelines.yml');
  }

  /**
   * @return array
   */
  public function providerPhpStanFilesBootstrapProvider() {
    return [
      // Test non-php extension.
      ['README.md', '[OK] No errors', TRUE],
      // Test included file.
      ['docroot/index.php', '[OK] No errors', TRUE],
      // Test file expected to fail.
      ['vendor/acquia/blt/RoboFile.php', 'Class RoboFile was not found', TRUE],
    ];
  }

}
