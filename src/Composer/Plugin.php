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
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Util\Filesystem;
use Composer\Util\ProcessExecutor;

/**
 * Composer plugin.
 */
class Plugin implements PluginInterface, EventSubscriberInterface {

  /**
   * Package name.
   */
  const PACKAGE_NAME = 'acquia/blt';

  /**
   * BLT config directory.
   */
  const BLT_DIR = 'blt';

  /**
   * Priority that plugin uses to register callbacks.
   */
  const CALLBACK_PRIORITY = 60000;

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
   * @var \Composer\Script\EventDispatcher
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
   * Returns an array of event names this subscriber wants to listen to.
   */
  public static function getSubscribedEvents() {
    return [
      ScriptEvents::POST_AUTOLOAD_DUMP => "onPostAutoloadDump",
      PackageEvents::POST_PACKAGE_INSTALL => "onPostPackageEvent",
      PackageEvents::POST_PACKAGE_UPDATE => "onPostPackageEvent",
      ScriptEvents::POST_UPDATE_CMD => [
        ['onPostCmdEvent'],
      ],
    ];
  }

  /**
   * Modify vendor/composer/installed.json so that composer/installers is first.
   *
   * @param \Composer\Script\Event $event
   *   Event.
   */
  public static function onPostAutoloadDump(Event $event) {
    $composer = $event->getComposer();
    // This workaround is only necessary for Composer versions before 1.9.0.
    if (version_compare($composer::VERSION, '1.9.0', '>=')) {
      return;
    }
    $vendor_dir = $composer->getConfig()->get('vendor-dir');
    $installed_json = realpath($vendor_dir) . "/composer/installed.json";
    $installed = json_decode(file_get_contents($installed_json));
    foreach ($installed as $key => $package) {
      if ($package->name == 'composer/installers') {
        unset($installed[$key]);
        array_unshift($installed, $package);
      }
    }
    file_put_contents($installed_json, json_encode($installed, 448));
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
   * @param \Composer\Script\Event $event
   *   Event.
   */
  public function onPostCmdEvent(Event $event) {
    // Only install the template files if acquia/blt was installed.
    if (isset($this->bltPackage)) {
      $version = $this->bltPackage->getVersion();
      $this->executeBltUpdate($version);
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
   * @param string $version
   *   Version.
   */
  protected function executeBltUpdate($version) {
    $options = $this->getOptions();

    if ($this->isInitialInstall()) {
      $this->io->write('<info>Creating BLT templated files...</info>');
      if ($this->isNewProject()) {
        // The BLT command will not work because the .git dir doesn't exist yet.
        $command = $this->getVendorPath() . '/acquia/blt/bin/blt internal:create-project --ansi';
      }
      else {
        $command = $this->getVendorPath() . '/acquia/blt/bin/blt internal:add-to-project --ansi -n';
      }
      $success = $this->executeCommand($command, [], TRUE);
      if (!$success) {
        $this->io->writeError("<error>BLT installation failed! Please execute <comment>$command --verbose</comment> to debug the issue.</error>");
        throw new \Exception('Installation aborted due to error');
      }
    }
    elseif ($options['blt']['update']) {
      $this->io->write('<info>Updating BLT templated files...</info>');
      $success = $this->executeCommand('blt blt:update --ansi --no-interaction', [], TRUE);
      if (!$success) {
        $this->io->writeError("<error>BLT update script failed! Run `blt blt:update --verbose` to retry.</error>");
      }
    }
    else {
      $this->io->write('<comment>Skipping update of BLT templated files</comment>');
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
   * Determine if this is a project being newly created.
   *
   * This would execute in the context of
   * `composer create-project acquia/blt-project`.
   *
   * @return bool
   *   TRUE if this is a newly create project.
   */
  protected function isNewProject() {
    $composer_json = json_decode(file_get_contents($this->getRepoRoot() . '/composer.json'), TRUE);
    if (isset($composer_json['name']) && in_array($composer_json['name'], ['acquia/blt-project', 'acquia/blted8'])) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Create a new directory.
   *
   * @return bool
   *   TRUE if directory exists or is created.
   */
  protected function createDirectory($path) {
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
    $vendorPath = $filesystem->normalizePath(realpath($config->get('vendor-dir')));

    return $vendorPath;
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
