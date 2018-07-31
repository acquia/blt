<?php

namespace Acquia\Blt\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Class BltTestBase.
 *
 * Base class for all tests that are executed for BLT itself.
 */
abstract class BltTestBase extends TestCase {

  protected $bltDirectory;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->bltDirectory = realpath(dirname(__FILE__) . '/../../../');
  }

}
