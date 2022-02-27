<?php

namespace Acquia\Blt\Tests;

use Acquia\Blt\Robo\Commands\Blt\ConfigCommand;
use Robo\Config\Config;
/**
 * Test config commands.
 */
class BltConfigTest extends BltProjectTestBase {

  /**
   * Tests blt:config:dump command.
   */
  public function testPipelinesInit() {
    $this->blt('blt:config:dump', []);
    list(, $output,) = $this->blt('blt:config:dump', [
      '--site' => 'site2',
    ]);
    $this->assertStringContainsString('site2', $output);
    $this->blt('blt:config:dump', [
      '--environment' => 'local',
    ]);
    $this->blt('blt:config:dump', [
      '--environment' => 'ci',
    ]);
  }

  /**
   * Tests getvalue of config command.
   */
  public function testGetValueConfigCommand() {
    $mockconfigcommand = $this->getMockBuilder(ConfigCommand::class)
      ->disableOriginalConstructor()
      ->onlyMethods([
        'getConfig',
      ])
      ->getMock();
    $mockconfig = $this->getMockBuilder(Config::class)
      ->disableOriginalConstructor()
      ->getMock();

    $mockconfigcommand->expects($this->any())->method('getConfig')->willReturn($mockconfig);
    $mockconfigcommand->getValue('abc');
  }


}
