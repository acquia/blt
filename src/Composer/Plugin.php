<?php

namespace Acquia\Blt\Composer;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;
use Composer\Util\Filesystem;
use Composer\Util\ProcessExecutor;

/**
 * Composer plugin.
 */
class Plugin implements PluginInterface, EventSubscriberInterface {

  /**
   * Composer.
   *
   * @var \Composer\Composer
   */
  protected $composer;
  /**
   * IO.
   *
   * @var \Composer\IO\IOInterface
   */
  protected $io;
  /**
   * Dispatcher.
   *
   * @var \Composer\EventDispatcher\EventDispatcher
   */
  protected $eventDispatcher;
  /**
   * Process.
   *
   * @var \Composer\Util\ProcessExecutor
   */
  protected $executor;

  /**
   * BLT.
   *
   * @var \Composer\Package\PackageInterface
   */
  protected $bltPackage;

  /**
   * Apply plugin modifications to composer.
   *
   * @param \Composer\Composer $composer
   *   Composer.
   * @param \Composer\IO\IOInterface $io
   *   Io.
   */
  public function activate(Composer $composer, IOInterface $io) {
    $this->composer = $composer;
    $this->io = $io;
    $this->eventDispatcher = $composer->getEventDispatcher();
    ProcessExecutor::setTimeout(3600);
    $this->executor = new ProcessExecutor($this->io);
  }

  /**
   * {@inheritDoc}
   */
  public function deactivate(Composer $composer, IOInterface $io) {
  }

  /**
   * {@inheritDoc}
   */
  public function uninstall(Composer $composer, IOInterface $io) {
  }

  /**
   * Returns an array of event names this subscriber wants to listen to.
   */
  public static function getSubscribedEvents() {
    return [
      PackageEvents::POST_PACKAGE_INSTALL => "onPostPackageEvent",
      PackageEvents::POST_PACKAGE_UPDATE => "onPostPackageEvent",
      ScriptEvents::POST_UPDATE_CMD => "onPostCmdEvent",
      ScriptEvents::POST_INSTALL_CMD => "onPostCmdEvent",
    ];
  }

  /**
   * Marks blt to be processed after an install or update command.
   *
   * @param \Composer\Installer\PackageEvent $event
   *   Event.
   */
  public function onPostPackageEvent(PackageEvent $event) {
    $package = $this->getBltPackage($event->getOperation());
    if ($package) {
      // By explicitly setting the blt package, the onPostCmdEvent() will
      // process the update automatically.
      $this->bltPackage = $package;
    }
  }

  /**
   * Execute blt blt:update after update command has been executed.
   *
   * @throws \Exception
   */
  public function onPostCmdEvent() {
    // Only install the template files if acquia/blt was installed.
    if (isset($this->bltPackage)) {
      $this->executeBltUpdate();
    }
  }

  /**
   * Is windows.
   */
  public static function isWindows() {
    return DIRECTORY_SEPARATOR === '\\';
  }

  /**
   * Gets the acquia/blt package, if it is the package being operated on.
   *
   * @param mixed $operation
   *   Op.
   *
   * @return mixed
   *   Mixed.
   */
  protected function getBltPackage($operation) {
    if ($operation instanceof InstallOperation) {
      $package = $operation->getPackage();
    }
    elseif ($operation instanceof UpdateOperation) {
      $package = $operation->getTargetPackage();
    }
    if (isset($package) && $package instanceof PackageInterface && $package->getName() == 'acquia/blt') {
      return $package;
    }
    return NULL;
  }

  /**
   * Executes `blt blt:update` and `blt-console blt:update` commands.
   *
   * @throws \Exception
   */
  protected function executeBltUpdate() {
    $options = $this->getOptions();

    if ($this->isInitialInstall()) {
      $this->io->write('<info>Creating BLT template files...</info>');
      $command = $this->getVendorPath() . '/acquia/blt/bin/blt internal:add-to-project --ansi -n';
      $success = $this->executeCommand($command, [], TRUE);
      if (!$success) {
        $this->io->writeError("<error>BLT installation failed! Please execute <comment>$command --verbose</comment> to debug the issue.</error>");
        throw new \Exception('Installation aborted due to error');
      }
    }
    elseif ($options['blt']['update']) {
      $this->io->write('<info>Updating BLT template files...</info>');
      $success = $this->executeCommand('blt blt:update --ansi --no-interaction', [], TRUE);
      if (!$success) {
        $this->io->writeError("<error>BLT update script failed! Run `blt blt:update --verbose` to retry.</error>");
      }
    }
    else {
      $this->io->write('<comment>Skipping update of BLT template files</comment>');
    }
  }

  /**
   * Determine if BLT is being installed for the first time on this project.
   *
   * @return bool
   *   TRUE if this is the initial install of BLT.
   */
  protected function isInitialInstall() {
    if (!file_exists($this->getRepoRoot() . '/blt/.schema_version')) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Create a new directory.
   *
   * @param string $path
   *   Path to create.
   *
   * @return bool
   *   TRUE if directory exists or is created.
   */
  protected function createDirectory(string $path) {
    return is_dir($path) || mkdir($path);
  }

  /**
   * Returns the repo root's filepath, assumed to be one dir above vendor dir.
   *
   * @return string
   *   The file path of the repository root.
   */
  public function getRepoRoot() {
    return dirname($this->getVendorPath());
  }

  /**
   * Get the path to the 'vendor' directory.
   *
   * @return string
   *   String.
   */
  public function getVendorPath() {
    $config = $this->composer->getConfig();
    $filesystem = new Filesystem();
    $filesystem->ensureDirectoryExists($config->get('vendor-dir'));
    return $filesystem->normalizePath(realpath($config->get('vendor-dir')));
  }

  /**
   * Retrieve "extra" configuration.
   *
   * @return array
   *   Options.
   */
  protected function getOptions() {
    $defaults = [
      'update' => TRUE,
    ];
    $extra = $this->composer->getPackage()->getExtra() + ['blt' => []];
    $extra['blt'] = $extra['blt'] + $defaults;

    return $extra;
  }

  /**
   * Executes a shell command with escaping.
   *
   * Example usage: $this->executeCommand("test command %s", [ $value ]).
   *
   * @param string $cmd
   *   Cmd.
   * @param array $args
   *   Args.
   * @param bool $display_output
   *   Optional. Defaults to FALSE. If TRUE, command output will be displayed
   *   on screen.
   *
   * @return bool
   *   TRUE if command returns successfully with a 0 exit code.
   */
  protected function executeCommand($cmd, array $args = [], $display_output = FALSE) {
    // Shell-escape all arguments.
    foreach ($args as $index => $arg) {
      $args[$index] = escapeshellarg($arg);
    }
    // Add command as first arg.
    array_unshift($args, $cmd);
    // And replace the arguments.
    $command = call_user_func_array('sprintf', $args);
    $output = '';
    if ($this->io->isVerbose() || $display_output) {
      $this->io->write('<comment> > ' . $command . '</comment>');
      $io = $this->io;
      $output = function ($type, $buffer) use ($io) {
        $io->write($buffer, FALSE);
      };
    }
    return ($this->executor->execute($command, $output) == 0);
  }

}
