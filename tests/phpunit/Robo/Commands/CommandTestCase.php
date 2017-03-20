<?php

namespace Acquia\Blt\Tests\Robo\Commands;

use Acquia\Blt\Robo\Blt;
use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\Executor;
use Acquia\Blt\Robo\Config\DefaultConfig;
use Acquia\Blt\Robo\Config\YamlConfig;
use Acquia\Blt\Robo\Inspector\Inspector;
use League\Container\Container;
use Acquia\Blt\Robo\Config\BltConfig;
use Psr\Log\NullLogger;
use Robo\Collection\CollectionBuilder;
use Robo\Common\ProcessExecutor;
use Robo\Config;
use Robo\Robo;
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
   * @var Inspector|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $inspector;

  /**
   * @var CollectionBuilder
   */
  protected $builder;

  /**
   * @var BltTasks|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $command;

  /**
   * @var NullLogger|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $logger;

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
  public function setUp()
  {
    if (!$this->config) {
      $this->createDefaultConfig();
    }
    if (!$this->input) {
      $this->createMockInput();
    }
    if (!$this->container) {
      $this->createDefaultContainer();
    }
    if (!$this->logger) {
      $this->createMockLogger();
    }
  }

  /**
   *
   */
  public function setMockExecutor() {
    $mock_executor = $this->getMockBuilder(Executor::class)
      ->disableOriginalConstructor()
      ->getMock();
    $mock_process_executor = $this->getMockBuilder(ProcessExecutor::class)
      ->disableOriginalConstructor()
      ->getMock();
    $mock_executor->method('execute')->willReturn($mock_process_executor);
    $mock_process_executor->method('background')->willReturn($mock_process_executor);
    $mock_process_executor->method('printOutput')->willReturn($mock_process_executor);
    $mock_process_executor->method('dir')->willReturn($mock_process_executor);
    $mock_process_executor->method('run')->willReturn($mock_process_executor);
    $this->container->share('executor', $mock_executor);
    $this->executor = $mock_executor;
  }

  /**
   *
   */
  protected function createDefaultConfig() {
    $this->config = new DefaultConfig();
    $this->config->extend(new YamlConfig($this->config->get('blt.root') . '/phing/build.yml',
      $this->config->toArray()));
    $this->config->extend(new YamlConfig($this->config->get('blt.root') . '/template/blt/project.yml',
      $this->config->toArray()));
    $this->config->extend(new YamlConfig($this->config->get('blt.root') . '/template/blt/project.local.yml',
      $this->config->toArray()));
    $this->config->set(Config::SIMULATE, TRUE);
  }

  /**
   *
   */
  protected function createDefaultContainer() {
    $this->container = Robo::createDefaultContainer($this->input, NULL, NULL,
      $this->config);
    //Blt::configureContainer($this->container);
    $this->setMockExecutor();

    $inspector = $this->getMockBuilder(Inspector::class)
      ->disableOriginalConstructor()
      ->getMock();
    $inspector->method('getExecutor')->willReturn($this->executor);
    $this->container->share('inspector', $inspector);
  }

  /**
   *
   */
  protected function createMockInput() {
    // Always say yes to confirmations
    $this->input = $this->getMockBuilder(Input::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->input->method('hasOption')->with('yes')->willReturn(TRUE);
    $this->input->method('getOption')->with('yes')->willReturn(TRUE);
  }

  /**
   *
   */
  protected function createMockLogger() {
    // A lot of commands output to a logger.
    // To use this call `$command->setLogger($this->logger);` after you create your command to test.
    $this->logger = $this->getMockBuilder(NullLogger::class)
      ->setMethods(array('log'))
      ->getMock();
  }
}
