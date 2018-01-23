<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class FrontendTest.
 */
class FrontendTest extends BltProjectTestBase {

  public function testFrontendHooks() {
    list($status_code, $output, $config) = $this->blt('source:build:frontend-reqs', [
      '--define' => [
        'command-hooks.frontend-reqs.command=\'echo "hello reqs"\'',
      ],
    ]);
    $this->assertContains('hello reqs', $output);

    list($status_code, $output, $config) = $this->blt('source:build:frontend-assets', [
      '--define' => [
        'command-hooks.frontend-assets.command=\'echo "hello assets"\'',
      ],
    ]);
    $this->assertContains('hello assets', $output);

    list($status_code, $output, $config) = $this->blt('frontend:test', [
      '--define' => [
        'command-hooks.frontend-test.command=\'echo "hello test"\'',
      ],
    ]);
    $this->assertContains('hello test', $output);
  }

}
