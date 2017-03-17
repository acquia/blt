<?php

namespace Acquia\Blt\Tests\Robo\Commands\Tests;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Commands\Tests\BehatCommand;
use Acquia\Blt\Robo\Common\Executor;
use Acquia\Blt\Robo\Inspector\Inspector;
use Acquia\Blt\Tests\Robo\Commands\CommandTestCase;
use Robo\Collection\CollectionBuilder;
use Robo\Config;

/**
 * Class CreateCommandTest
 * Test suite class for Pantheon\Terminus\Commands\Site\CreateCommand
 * @package Pantheon\Terminus\UnitTests\Commands\Site
 */
class BehatCommandTest extends CommandTestCase
{

  /**
   * @var BehatCommand
   */
  protected $command;

  /**
   * @inheritdoc
   */
  public function setUp()
  {
    parent::setUp();

    $this->command = new BehatCommand();
    $this->command->setConfig($this->config);
    $this->command->setInspector($this->inspector);
    $this->command->setBuilder($this->builder);
  }

  /**
   * Tests the tests:behat command
   */
  public function testBehat() {
    $this->command->behat();
    $this->inspector->expects($this->once())->method('getLocalBehatConfig');
  }

  public function testLaunchSelenium() {}

  public function testLaunchPhantomJs() {}

  public function testSetupPhantomJs() {}

}
