<?php

namespace Acquia\Blt\Tests;

use Acquia\Blt\Robo\Common\YamlMunge;

/**
 * Class FrontendTest.
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

    list(, $output,) = $this->blt('source:build:frontend-reqs');
    $this->assertStringContainsString('hello reqs', $output);

    list(, $output,) = $this->blt('source:build:frontend-assets');
    $this->assertStringContainsString('hello assets', $output);

    list(, $output,) = $this->blt('tests:frontend:run');
    $this->assertStringContainsString('hello test', $output);
  }

}
