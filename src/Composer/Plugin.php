<?php

/**
 * @file
 * Provides a way to patch Composer packages after installation.
 */

namespace Acquia\Blt\Composer;

use Acquia\Blt\Update\Updater;
use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Package\AliasPackage;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Installer\PackageEvents;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Script\PackageEvent;
use Composer\Util\ProcessExecutor;
use Composer\Util\Filesystem;
use Symfony\Component\Process\Process;

class Plugin implements PluginInterface, EventSubscriberInterface {

  /**
   * @var Composer $composer
   */
  protected $composer;
  /**
   * @var IOInterface $io
   */
  protected $io;
  /**
   * @var EventDispatcher $eventDispatcher
   */
  protected $eventDispatcher;
  /**
   * @var ProcessExecutor $executor
   */
  protected $executor;

  /**
   * @var \Composer\Package\PackageInterface
   */
  protected $bltPackage;

  /** @var string */
  protected $blt_prior_version;

  /**
   * Apply plugin modifications to composer
   *
   * @param Composer    $composer
   * @param IOInterface $io
   */
  public function activate(Composer $composer, IOInterface $io) {
    $this->composer = $composer;
    $this->io = $io;
    $this->eventDispatcher = $composer->getEventDispatcher();
    $this->executor = new ProcessExecutor($this->io);
  }

  /**
   * Returns an array of event names this subscriber wants to listen to.
   */
  public static function getSubscribedEvents() {
    return array(
      PackageEvents::PRE_PACKAGE_INSTALL => "onPrePackageEvent",
      PackageEvents::PRE_PACKAGE_UPDATE => "onPrePackageEvent",
      PackageEvents::POST_PACKAGE_INSTALL => "onPostPackageEvent",
      PackageEvents::POST_PACKAGE_UPDATE => "onPostPackageEvent",
      ScriptEvents::POST_UPDATE_CMD => 'onPostCmdEvent'
    );
  }

  /**
   * Marks initial blt version before install or update command.
   *
   * @param \Composer\Installer\PackageEvent $event
   */
  public function onPrePackageEvent(\Composer\Installer\PackageEvent $event){
    $package = $this->getBltPackage($event->getOperation());
    if ($package) {
      $this->blt_prior_version = $package->getVersion();
      // We write this to disk because the blt_prior_version property does not persist.
      file_put_contents($this->getVendorPath() . '/blt_prior_version.txt', $this->blt_prior_version);
    }
  }
  /**
   * Marks blt to be processed after an install or update command.
   *
   * @param \Composer\Installer\PackageEvent $event
   */
  public function onPostPackageEvent(\Composer\Installer\PackageEvent $event){
    $package = $this->getBltPackage($event->getOperation());
    if ($package) {
      // By explicitly setting the blt package, the onPostCmdEvent() will
      // process the update automatically.
      $this->bltPackage = $package;
    }
  }

  /**
   * Execute blt update after update command has been executed, if applicable.
   *
   * @param \Composer\Script\Event $event
   */
  public function onPostCmdEvent(\Composer\Script\Event $event) {
    // Only install the template files if acquia/blt was installed.
    if (isset($this->bltPackage)) {
      $version = $this->bltPackage->getVersion();
      $this->executeBltUpdate($version);
    }
  }

  /**
   * Gets the acquia/blt package, if it is the package that is being operated on.
   * @param $operation
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
   * Executes `blt update` and `blt-console blt:update` commands.
   * @param $version
   */
  protected function executeBltUpdate($version) {
    $options = $this->getOptions();
    if ($options['blt']['update']) {
      $this->io->write('<info>Updating BLT templated files...</info>');

      // Rsyncs, updates composer.json, project.yml.
      $this->executeCommand('blt update');

      if (file_exists($this->getVendorPath() . '/blt_prior_version.txt')) {
        $this->blt_prior_version = file_get_contents($this->getVendorPath() . '/blt_prior_version.txt');
        unlink($this->getVendorPath() . '/blt_prior_version.txt');
      }

      // Execute update hooks for this specific version delta.
      if (isset($this->blt_prior_version)) {
        $this->io->write("<info>Executing scripted updates for BLT version delta {$this->blt_prior_version} -> $version ...</info>");
        // $this->executeCommand("blt blt:update-delta -Dblt.prior_version={$this->blt_prior_version} -Dblt.version=$version");
        // @todo Allow prompt here.
        $this->executeCommand("blt-console blt:update {$this->blt_prior_version} $version {$this->getRepoRoot()} -y");
      }
      else {
        $this->io->write("<comment>Could not detect prior BLT version. Skipping scripted updates.</comment>");
      }

      $this->io->write('<comment>This may have modified your composer.json and require a subsequent `composer update`</comment>');

      // @todo check if require or require-dev changed. If so, run `composer update`.
      // @todo if require and require-dev did not change, but something else in composer.json changed, execute `composer update --lock`.
    }
    else {
      $this->io->write('<comment>Skipping update of BLT templated files</comment>');
    }
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
      'composer-exclude-merge' => [],
    ];
    $extra = $this->composer->getPackage()->getExtra() + ['blt' => []];
    $extra['blt'] = $extra['blt'] + $defaults;

    return $extra;
  }

  /**
   * Executes a shell command with escaping.
   *
   * @param string $cmd
   * @return bool
   */
  protected function executeCommand($cmd) {
    // Shell-escape all arguments except the command.
    $args = func_get_args();
    foreach ($args as $index => $arg) {
      if ($index !== 0) {
        $args[$index] = escapeshellarg($arg);
      }
    }
    // And replace the arguments.
    $command = call_user_func_array('sprintf', $args);
    $output = '';
    if ($this->io->isVerbose()) {
      $this->io->write('<comment> > ' . $command . '</comment>');
      $io = $this->io;
      $output = function ($type, $buffer) use ($io) {
        if ($type == Process::ERR) {
          $io->write('<error>' . $buffer . '</error>');
        }
        else {
          // @todo Figure out how to preserve color!
          $io->write($buffer);
        }
      };
    }
    return ($this->executor->execute($command, $output) == 0);
  }

}
