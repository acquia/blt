<?php

namespace Acquia\Blt\Tests;

use Acquia\Blt\Robo\Common\YamlMunge;

/**
 * Test frontend commands.
 */
class FrontendTest extends BltProjectTestBase {

  public function testFrontendHooks() {

    YamlMunge::mergeArrayIntoFile([
      'command-hooks' => [
        'frontend-reqs' => [
          'command' => 'echo "hello reqs"',
        ],
        'frontend-assets' => [
          'command' => 'echo "hello assets"',
        ],
        'frontend-test' => [
          'command' => 'echo "hello test"',
        ],
      ],
    ], $this->sandboxInstance . "/blt/ci.blt.yml");

    [, $output] = $this->blt('source:build:frontend-reqs');
    $this->assertStringContainsString('hello reqs', $output);

    [, $output] = $this->blt('source:build:frontend-assets');
    $this->assertStringContainsString('hello assets', $output);

    [, $output] = $this->blt('tests:frontend:run');
    $this->assertStringContainsString('hello test', $output);
  }

}
