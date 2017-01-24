<?php

namespace Acquia\Blt\Tests\Robo\Commands;

use Acquia\Blt\Robo\BltTasks;
use League\Container\Container;
use Acquia\Blt\Robo\Config\BltConfig;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CommandTestCase
 * @package Pantheon\Terminus\UnitTests\Commands
 */
abstract class CommandTestCase extends \PHPUnit_Framework_TestCase
{
  /**
   * @var BltConfig
   */
  protected $config;
  /**
   * @var Container
   */
  protected $container;
  /**
   * @var ArrayInput
   */
  protected $input;
  /**
   * @var OutputInterface
   */
  protected $output;

  /**
   * @var BltTasks
   */
  protected $command;

  /**
   * @return BltConfig
   */
  public function getConfig()
  {
    return $this->config;
  }

  /**
   * @param BltConfig $config
   * @return CommandTestCase
   */
  public function setConfig($config)
  {
    $this->config = $config;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getContainer()
  {
    return $this->container;
  }

  /**
   * @param mixed $container
   * @return CommandTestCase
   */
  public function setContainer($container)
  {
    $this->container = $container;
    return $this;
  }

  /**
   * @return int
   */
  public function getStatusCode()
  {
    return $this->status_code;
  }

  /**
   * @inheritdoc
   */
  protected function setUp()
  {
    if (!$this->config) {
      $this->config = new BltConfig();
    }

    if (!$this->container) {
      $this->container = new Container();
    }

    // builder
    // executor
    // inspector
    // wizards

    // Always say yes to confirmations
    $this->input = $this->getMockBuilder(Input::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->input->method('hasOption')->with('yes')->willReturn(true);
    $this->input->method('getOption')->with('yes')->willReturn(true);

    // A lot of commands output to a logger.
    // To use this call `$command->setLogger($this->logger);` after you create your command to test.
    $this->logger = $this->getMockBuilder(NullLogger::class)
      ->setMethods(array('log'))
      ->getMock();
  }
}
