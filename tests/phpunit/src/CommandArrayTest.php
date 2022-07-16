<?php

namespace Acquia\Blt\Tests;

use Acquia\Blt\Robo\Common\StringManipulator;

/**
 * Tests String to Array Conversion for Commands.
 */
class CommandArrayTest extends BltProjectTestBase {

  public function testStringToArray() {
    $string = "site-install minimal --existing-config --ansi -n";
    $command = StringManipulator::commandConvert($string);
    $this->assertIsArray($command);

    $keys = $this->getKeys();
    foreach ($keys as $key) {
      $result = in_array($key, $command);
      $this->assertTrue($result);
    }
  }

  public function testStringDrush() {
    $string = "status --yes";
    $this->executor->drush($string, "json")->run();
  }

  public function getKeys() {
    return [
      "site-install",
      "minimal",
      "--existing-config",
      "--ansi",
      "-n",
    ];
  }

}
