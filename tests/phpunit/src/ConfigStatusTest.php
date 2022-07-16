<?php

namespace Acquia\Blt\Tests;

/**
 * Test blt config imports.
 */
class ConfigStatusTest extends BltProjectTestBase {

  public function testSingleSiteConfig() {
    $this->installDrupalMinimal();
    $result = $this->inspector->isActiveConfigIdentical();
    $this->assertEquals(0, $result);

  }

}
