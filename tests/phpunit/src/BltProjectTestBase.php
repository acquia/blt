<?php

namespace Acquia\Blt\Tests;

use Acquia\Blt\Robo\Blt;
use Acquia\Blt\Robo\Common\Executor;
use Acquia\Blt\Robo\Config\ConfigInitializer;
use Acquia\Blt\Robo\Inspector\Inspector;
use League\Container\Container;
use PHPUnit\Framework\TestCase;
use Robo\Collection\CollectionBuilder;
use Robo\Config\Config;
use Robo\Robo;
use Robo\Tasks;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Base class for all tests that are executed within a blt project.
 */
abstract class BltProjectTestBase extends TestCase {

  /**
   * @var string
   */
  protected $sandboxInstance;
  /**
   * @var \Acquia\Blt\Robo\Config\DefaultConfig
   */
  protected $config = NULL;
  /**
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  protected $fs;
  /**
   * @var string
   *
   * This fixture is shared between tests, but regenerated each time PHPUnit
   * is bootstrapped. Setting BLT_RECREATE_SANDBOX_MASTER=0 will prevent this.
   */
  protected $dbDump;

  /**
   * @var \Acquia\Blt\Robo\Common\Executor
   */
  protected $executor;

  /**
   * @var \Symfony\Component\Console\Output\ConsoleOutput
   */
  protected $output;

  /**
   * @var string
   */
  protected $bltDirectory;

  /**
   * @var bool
   *
   * Track whether our master sandbox has been initialized.
   */
  protected static $initialized = FALSE;

  /**
   * @var \Acquia\Blt\Robo\Inspector\Inspector
   */
  protected $inspector;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    $this->output = new ConsoleOutput();
    $this->printTestName();
    $this->bltDirectory = realpath(dirname(__FILE__) . '/../../../');
    $this->fs = new Filesystem();
    $this->sandboxInstance = getenv('ORCA_FIXTURE_DIR');
    // Config is overwritten for each $this->blt execution.
    $this->reInitializeConfig($this->createBltInput(NULL, []));
    $this->fs->copy($this->bltDirectory . "/scripts/blt/ci/internal/ci.yml", $this->sandboxInstance . "/blt/ci.blt.yml", TRUE);
    $this->fs->copy($this->bltDirectory . "/tests/phpunit/fixtures/drush.yml", $this->sandboxInstance . "/drush/drush.yml", TRUE);
    $this->fs->copy($this->bltDirectory . "/tests/phpunit/fixtures/local.drush.yml", $this->sandboxInstance . "/docroot/sites/default/local.drush.yml", TRUE);

    // Inject executor and inspector for test execution.
    $this->config = new Config();
    $this->blt = new Blt($this->config);
    $container = new Container();
    $this->blt->setContainer($container);
    $this->commandFile = new Tasks();
    $this->builder = new CollectionBuilder($this->commandFile);
    $this->executor = new Executor($this->builder);
    $this->inspector = new Inspector($this->executor);

    $this->executor->execute(['./bin/orca', 'fixture:reset', '-f'], getenv('ORCA_ROOT'));
    $this->dbDump = $this->sandboxInstance . "/bltDbDump.sql";
    parent::setUp();
  }

  /**
   * @param mixed $input
   *   Input.
   */
  protected function reInitializeConfig($input) {
    unset($this->config);
    $config_initializer = new ConfigInitializer($this->sandboxInstance, $input);
    $this->config = $config_initializer->initialize();
  }

  /**
   *
   * @throws \Exception
   */
  protected function installDrupalMinimal() {
    return $this->blt('setup', [
      '--define' => [
        'project.profile.name=minimal',
        'cm.strategy=core',
      ],
    ]);
  }

  /**
   * Executes a BLT command.
   *
   * @param string $command
   *   Command.
   * @param array $args
   *   Args.
   * @param bool $stop_on_error
   *   Stop on error.
   *
   * @return array
   *   Array.
   *
   * @throws \Exception
   *
   * @internal param null $cwd
   */
  protected function blt($command, array $args = [], $stop_on_error = TRUE) {
    chdir($this->sandboxInstance);
    $input = $this->createBltInput($command, $args);

    if (getenv('BLT_PRINT_COMMAND_OUTPUT')) {
      $command_string = (string) $input;
      $output = new BufferedConsoleOutput();
      $output->writeln("");
      $output->writeln("Executing <comment>blt $command_string</comment> in " . $this->sandboxInstance);
      $output->writeln("<comment>------Begin BLT output-------</comment>");
    }
    else {
      $output = new BufferedOutput();
    }

    $config_initializer = new ConfigInitializer($this->sandboxInstance, $input);
    $config = $config_initializer->initialize();

    // Execute command.
    $repo_root = getenv('ORCA_FIXTURE_DIR');
    $classLoader = require $repo_root . '/vendor/autoload.php';
    $blt = new Blt($config, $input, $output, $classLoader);
    $status_code = (int) $blt->run($input, $output);
    Robo::unsetContainer();

    if (getenv('BLT_PRINT_COMMAND_OUTPUT')) {
      $output->writeln("<comment>------End BLT output---------</comment>");
      $output->writeln("");
    }

    if ($status_code && $stop_on_error) {
      throw new \Exception("BLT command exited with non-zero exit code.");
    }

    return [$status_code, $output->fetch(), $config];
  }

  /**
   * Write full width line.
   *
   * @param string $message
   *   Message.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   Output.
   */
  protected function writeFullWidthLine($message, OutputInterface $output) {
    $terminal_width = (new Terminal())->getWidth();
    $padding_len = ($terminal_width - strlen($message)) / 2;
    $pad = $padding_len > 0 ? str_repeat('-', $padding_len) : '';
    $output->writeln("<comment>{$pad}{$message}{$pad}</comment>");
  }

  /**
   * Create input.
   *
   * @param string $command
   *   Command.
   * @param array $args
   *   Args.
   *
   * @return \Symfony\Component\Console\Input\InputInterface
   *   Input interface.
   */
  protected function createBltInput($command = '', array $args = []) {
    $defaults = [
      '-vvv' => '',
      '--no-interaction' => '',
      '--environment' => 'ci',
    ];

    $args = array_merge($args, $defaults);
    $prepend = [
      'command' => $command,
    ];
    $args = $prepend + $args;
    $input = new ArrayInput($args);
    $input->setInteractive(FALSE);

    return $input;
  }

  protected function printTestName() {
    if (getenv('BLT_PRINT_COMMAND_OUTPUT')) {
      $this->output->writeln("");
      $this->writeFullWidthLine(get_class($this) . "::" . $this->getName(),
        $this->output);
    }
  }

}
