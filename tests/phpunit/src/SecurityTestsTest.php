<?php

namespace Acquia\Blt\Tests;

/**
 * Test security test commands.
 */
class SecurityTestsTest extends BltProjectTestBase {

  /**
   * Test that executes the blt drupal security test.
   */
  public function testDrupalSecurity() {
    [$response] = $this->blt('tests:security-drupal');
    $this->assertStringContainsString("0", $response);
  }

  /**
   * Test that executes the blt composer security test.
   */
  public function testComposerSecurity() {
    [$response] = $this->blt('tests:security-composer');
    $this->assertStringContainsString("0", $response);
  }

}
