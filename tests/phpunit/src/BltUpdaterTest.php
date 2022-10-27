<?php

namespace Acquia\Blt\Tests;

/**
 * Test Updater commands.
 */
class BltUpdaterTest extends BltProjectTestBase {

  /**
   * Tests internal:add-to-project command.
   */
  public function testConfigSplitRecipe() {
    [$status_code] = $this->blt('internal:add-to-project');
    $this::assertEquals(0, $status_code);
  }

}
