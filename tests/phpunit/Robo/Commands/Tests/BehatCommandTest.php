<?php

namespace Acquia\Blt\Tests\Robo\Commands\Tests;

use Acquia\Blt\Robo\Commands\Tests\BehatCommand;
use Acquia\Blt\Tests\Robo\Commands\CommandTestCase;

/**
 * Class CreateCommandTest
 * Test suite class for Pantheon\Terminus\Commands\Site\CreateCommand
 * @package Pantheon\Terminus\UnitTests\Commands\Site
 */
class TestsBehatCommandTest extends CommandTestCase
{

  /**
   * @var BehatCommand
   */
  protected $command;

  /**
   * @inheritdoc
   */
  protected function setUp()
  {
    parent::setUp();

    $this->command = new BehatCommand();
  }

  /**
   * Tests the tests:behat command
   */
  public function testBehat() {
    $this->command->behat();
  }

  public function testLaunchSelenium() {}

  public function testLaunchPhantomJs() {}

  public function testSetupPhantomJs() {}

}
