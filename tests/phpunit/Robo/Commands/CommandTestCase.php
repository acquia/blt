<?php

namespace Acquia\Blt\Tests\Robo\Commands;

use Acquia\Blt\Robo\Common\Executor;
use Acquia\Blt\Robo\Config\BltConfig;
use Acquia\Blt\Robo\Config\DefaultConfig;
use Acquia\Blt\Robo\Config\YamlConfig;
use Acquia\Blt\Robo\Inspector\Inspector;
use League\Container\Container;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Robo\Common\ProcessExecutor;
use Robo\Config;
use Robo\Robo;
use Symfony\Component\Console\Input\Input;

/**
 * Class CommandTestCase.
 *
 * @package Pantheon\Terminus\UnitTests\Commands
 */
abstract class CommandTestCase extends TestCase {
  /**
   * The BLT configuration.
   *
   * @var \Acquia\Blt\Robo\BltConfig
   */
  protected $config;
  /**
   * The container.
   *
   * @var \League\Container\Container
   */
  protected $container;
  /**
   * The command input.
   *
   * @var \Symfony\Component\Console\Input\ArrayInput
   */
  protected $input;
  /**
   * The command output.
   *
   * @var \Symfony\Component\Console\Output\OutputInterface
   */
  protected $output;

  /**
   * The local environment inspector.
   *
   * @var \Acquia\Blt\Robo\Inspector\Inspector|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $inspector;

  /**
   * A copy of Robo's collection builder.
   *
   * @var \Robo\Collection\CollectionBuilder
   */
  protected $builder;

  /**
   * The Robo command class.
   *
   * @var \Acquia\Blt\Robo\BltTasks|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $command;

  /**
   * The logger.
   *
   * @var \Psr\Log\NullLogger|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $logger;

  /**
   * Gets $this->config.
   *
   * @return \Acquia\Blt\Robo\BltConfig
   *   The BLT configuration.
   */
  public function getConfig() {
    return $this->config;
  }

  /**
   * Sets $this->config.
   *
   * @param \Acquia\Blt\Robo\BltConfig $config
   *   The BLT configuration.
   *
   * @return CommandTestCase
   *   Self.
   */
  public function setConfig(BltConfig $config) {
    $this->config = $config;
    return $this;
  }

  /**
   * Gets $this->container.
   *
   * @return \League\Container\Container
   *   The container.
   */
  public function getContainer() {
    return $this->container;
  }

  /**
   * Sets $this->container.
   *
   * @param \League\Container\Container $container
   *   The container.
   *
   * @return CommandTestCase
   *   Self.
   */
  public function setContainer(Container $container) {
    $this->container = $container;
    return $this;
  }

  /**
   * Gets $this->status_code.
   *
   * @return int
   *   The status code for the command after execution.
   */
  public function getStatusCode() {
    return $this->status_code;
  }

  /**
   * Shared setup method for all commands.
   */
  public function setUp() {
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
   * Create a mock executor service.
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
   * Create default BLT configuration.
   */
  protected function createDefaultConfig() {
    $this->config = new DefaultConfig();
    $this->config->extend(new YamlConfig($this->config->get('blt.root') . '/phing/build.yml',
      $this->config->toArray()));
    $this->config->extend(new YamlConfig($this->config->get('blt.root') . '/template/blt/blt.yml',
      $this->config->toArray()));
    $this->config->extend(new YamlConfig($this->config->get('blt.root') . '/template/blt/local.yml',
      $this->config->toArray()));
    $this->config->set(Config::SIMULATE, TRUE);
  }

  /**
   * Create default container.
   */
  protected function createDefaultContainer() {
    $this->container = Robo::createDefaultContainer($this->input, NULL, NULL,
      $this->config);
    // Blt::configureContainer($this->container);.
    $this->setMockExecutor();

    $inspector = $this->getMockBuilder(Inspector::class)
      ->disableOriginalConstructor()
      ->getMock();
    $inspector->method('getExecutor')->willReturn($this->executor);
    $this->container->share('inspector', $inspector);
  }

  /**
   * Create mock user input.
   */
  protected function createMockInput() {
    // Always say yes to confirmations.
    $this->input = $this->getMockBuilder(Input::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->input->method('hasOption')->with('yes')->willReturn(TRUE);
    $this->input->method('getOption')->with('yes')->willReturn(TRUE);
  }

  /**
   * Create mock null logger.
   */
  protected function createMockLogger() {
    // A lot of commands output to a logger.
    // To use this call `$command->setLogger($this->logger);` after you create
    // your command to test.
    $this->logger = $this->getMockBuilder(NullLogger::class)
      ->setMethods(array('log'))
      ->getMock();
  }

}
