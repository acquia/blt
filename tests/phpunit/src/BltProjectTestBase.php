<?php

namespace Acquia\Blt\Tests;

use Acquia\Blt\Robo\Blt;
use Acquia\Blt\Robo\Common\YamlMunge;
use Acquia\Blt\Robo\Config\ConfigInitializer;
use PHPUnit\Framework\TestCase;
use Robo\Robo;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Class BltProjectTestBase.
 *
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
   * {@inheritdoc}
   */
  public function setUp(): void {
    $this->output = new ConsoleOutput();
    $this->printTestName();
    $this->bltDirectory = realpath(dirname(__FILE__) . '/../../../');
    $this->fs = new Filesystem();
    $this->execute('./bin/orca fixture:reset -f', getenv('ORCA_ROOT'));
    $this->sandboxInstance = getenv('ORCA_FIXTURE_DIR');

    $ci_config = YamlMunge::mungeFiles($this->sandboxInstance . "/blt/ci.blt.yml", $this->bltDirectory . "/scripts/blt/ci/internal/ci.yml");
    YamlMunge::writeFile($this->sandboxInstance . "/blt/ci.blt.yml", $ci_config);

    // Config is overwritten for each $this->blt execution.
    $this->reInitializeConfig($this->createBltInput(NULL, []));
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
   * @param mixed $command
   *   Command.
   * @param mixed $cwd
   *   CWD.
   * @param bool $stop_on_error
   *   Stop on error.
   *
   * @return \Symfony\Component\Process\Process
   *   Process
   *
   * @throws \Exception
   */
  protected function execute($command, $cwd = NULL, $stop_on_error = TRUE) {
    if (!$cwd) {
      $cwd = $this->sandboxInstance;
    }

    $process = new Process($command, $cwd);
    $process->setTimeout(NULL);
    $output = new ConsoleOutput();
    if (getenv('BLT_PRINT_COMMAND_OUTPUT')) {
      $output->writeln("");
      $output->writeln("Executing <comment>$command</comment>...");
      if (!$stop_on_error) {
        $output->writeln("Command failure is permitted.");
      }
      $output->writeln("<comment>------Begin command output-------</comment>");
      $process->run(function ($type, $buffer) use ($output) {
        $output->write($buffer);
      });
    }
    else {
      $process->run();
    }
    if (getenv('BLT_PRINT_COMMAND_OUTPUT')) {
      $output->writeln("<comment>------End command output---------</comment>");
      $output->writeln("");
    }

    if (!$process->isSuccessful() && $stop_on_error) {
      throw new \Exception("Command exited with non-zero exit code.");
    }

    return $process;
  }

  /**
   * Drush.
   *
   * @param string $command
   *   Command.
   * @param mixed $root
   *   Root.
   * @param bool $stop_on_error
   *   Stop on error.
   *
   * @return string
   *   String.
   *
   * @throws \Exception
   */
  protected function drush($command, $root = NULL, $stop_on_error = TRUE) {
    if (!$root) {
      $root = $this->config->get('docroot');
    }
    $drush_bin = $this->sandboxInstance . '/vendor/bin/drush';
    $command_string = "$drush_bin $command --root=$root --no-interaction --ansi";
    $process = $this->execute($command_string, $root, $stop_on_error);
    $output = $process->getOutput();

    return $output;
  }

  /**
   * Drush JSON.
   *
   * @param string $command
   *   Command.
   * @param mixed $root
   *   Root.
   * @param bool $stop_on_error
   *   Stop on error.
   *
   * @return mixed
   *   Mixed.
   *
   * @throws \Exception
   */
  protected function drushJson($command, $root = NULL, $stop_on_error = TRUE) {
    $output = $this->drush($command . " --format=json", $root, $stop_on_error);
    $array = json_decode($output, TRUE);

    return $array;
  }

  /**
   *
   * @throws \Exception
   */
  protected function installDrupalMinimal() {
    return $this->blt('setup', [
      '--define' => [
        'project.profile.name=minimal',
      ],
      '-D' => [
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
