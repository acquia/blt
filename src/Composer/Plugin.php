<?php

namespace Acquia\Blt\Composer;

use Acquia\Blt\Robo\Common\ArrayManipulator;
use Acquia\Blt\Update\Updater;
use Composer\Script\Event;
use Composer\Installer\PackageEvent;
use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Installer\PackageEvents;
use Composer\Script\ScriptEvents;
use Composer\Util\ProcessExecutor;
use Composer\Util\Filesystem;
use function in_array;

/**
 *
 */
class Plugin implements PluginInterface, EventSubscriberInterface {

  /**
   * Package name
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
   * @var Composer
   */
  protected $composer;
  /**
   * @var IOInterface
   */
  protected $io;
  /**
   * @var EventDispatcher
   */
  protected $eventDispatcher;
  /**
   * @var ProcessExecutor
   */
  protected $executor;

  /**
   * @var \Composer\Package\PackageInterface
   */
  protected $bltPackage;

  /**
   * @var string*/
  protected $blt_prior_version;

  /**
   * Apply plugin modifications to composer.
   *
   * @param Composer $composer
   * @param IOInterface $io
   */
  public function activate(Composer $composer, IOInterface $io) {
    $this->composer = $composer;
    $this->io = $io;
    $this->eventDispatcher = $composer->getEventDispatcher();
    if (self::isWindows() && $this->isInitialInstall()) {
      $this->io->writeError(
        '<error>BLT can be installed in Windows only under WSL. Please check https://blt.readthedocs.io/en/latest/windows-install/ for updates and workarounds.</error>'
      );
      throw new \Exception('BLT installation aborted');
    }

    ProcessExecutor::setTimeout(3600);
    $this->executor = new ProcessExecutor($this->io);
  }

  /**
   * Returns an array of event names this subscriber wants to listen to.
   */
  public static function getSubscribedEvents() {
    return array(
      PackageEvents::POST_PACKAGE_INSTALL => "onPostPackageEvent",
      PackageEvents::POST_PACKAGE_UPDATE => "onPostPackageEvent",
      ScriptEvents::POST_UPDATE_CMD => array(
        array('onPostCmdEvent'),
      ),
    );
  }

  /**
   * Marks blt to be processed after an install or update command.
   *
   * @param \Composer\Installer\PackageEvent $event
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
   * Execute blt blt:update after update command has been executed, if applicable.
   *
   * @param \Composer\Script\Event $event
   */
  public function onPostCmdEvent(Event $event) {
    // Only install the template files if acquia/blt was installed.
    if (isset($this->bltPackage)) {
      $version = $this->bltPackage->getVersion();
      $this->executeBltUpdate($version);
    }
  }

  public static function isWindows() {
    return DIRECTORY_SEPARATOR === '\\';
  }

  /**
   * Gets the acquia/blt package, if it is the package that is being operated on.
   *
   * @param $operation
   *
   * @return mixed
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
   * @param $version
   */
  protected function executeBltUpdate($version) {
    $options = $this->getOptions();

    if ($this->isInitialInstall()) {
      $this->io->write('<info>Creating BLT templated files...</info>');
      if ($this->isNewProject()) {
        // The BLT command will not work at this point because the .git dir doesn't exist yet.
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
   * This would execute in the context of `composer create-project acquia/blt-project`.
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
   * @param array $args
   * @param bool $display_output
   *   Optional. Defaults to FALSE. If TRUE, command output will be displayed
   *   on screen.
   *
   * @return bool
   *   TRUE if command returns successfully with a 0 exit code.
   */
  protected function executeCommand($cmd, $args = [], $display_output = FALSE) {
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
