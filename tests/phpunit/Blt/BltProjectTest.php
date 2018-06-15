<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltTestBase;

/**
 * Class BltProjectTest.
 *
 * Verifies that blt-project sub tree is valid.
 */
class BltProjectTest extends BltTestBase {

  /**
   * @group blt
   */
  public function testComposerJson() {
    $this->assertEquals(file_get_contents($this->bltDirectory . '/template/composer.json'), file_get_contents($this->bltDirectory . '/subtree-splits/blt-project/composer.json'), 'template/composer.json should be identical to subtree-splits/blt-project/composer.json.');
  }

}
