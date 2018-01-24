<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Robo\Common\YamlMunge;
use Acquia\Blt\Tests\BltProjectTestBase;

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
    ], $this->sandboxInstance . "/blt/" . getenv("BLT_ENV") . ".blt.yml");

    list($status_code, $output, $config) = $this->blt('source:build:frontend-reqs');
    $this->assertContains('hello reqs', $output);

    list($status_code, $output, $config) = $this->blt('source:build:frontend-assets');
    $this->assertContains('hello assets', $output);

    $this->installDrupalMinimal();
    list($status_code, $output, $config) = $this->blt('tests:frontend:run');
    $this->assertContains('hello test', $output);
  }

}
