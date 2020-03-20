<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class CustomCommandTest.
 */
class CustomCommandTest extends BltProjectTestBase {

  public function testExampleCustomCommand() {
    $this->blt("recipes:blt:command:init");
    // For some reason, using $this->blt does not work. Guessing related to
    // symlinking blt.
    $process = $this->execute("./vendor/bin/blt custom:hello");
    $this->assertStringContainsString("preCommandMessage hook: The custom:hello command is about to run!", $process->getOutput());
    $this->assertStringContainsString("Hello world!", $process->getOutput());
  }

}
