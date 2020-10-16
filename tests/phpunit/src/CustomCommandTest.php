<?php

namespace Acquia\Blt\Tests;

/**
 * Test custom commands.
 *
 * @group orca_ignore
 */
class CustomCommandTest extends BltProjectTestBase {

  public function testExampleCustomCommand() {
    $this->blt("recipes:blt:command:init");
    // For some reason, using $this->blt does not work. Guessing related to
    // symlinking blt.
    $process = $this->execute("./vendor/bin/blt custom:hello");
    $this->assertContains("preCommandMessage hook: The custom:hello command is about to run!", $process->getOutput());
    $this->assertContains("Hello world!", $process->getOutput());
  }

}
