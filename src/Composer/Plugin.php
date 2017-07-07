<?php

namespace Acquia\Blt\Composer;

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

/**
 *
 */
class Plugin implements PluginInterface, EventSubscriberInterface {

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
      ScriptEvents::PRE_INSTALL_CMD => 'checkInstallerPaths',
      ScriptEvents::POST_UPDATE_CMD => 'onPostCmdEvent',
    );
  }

  /**
   * Verify that composer.json contains correct values for installer-paths.
   *
   * Unfortunately, these values cannot be placed in composer.required.json.
   *
   * @see https://github.com/wikimedia/composer-merge-plugin/issues/139
   *
   * @param \Composer\Script\Event $event
   */
  public function checkInstallerPaths(Event $event) {
    $extra = $this->composer->getPackage()->getExtra();
    if (empty($extra['installer-paths'])) {
      $this->io->write('<error>Error: extras.installer-paths is missing from your composer.json file.</error>');
    }
    else {
      $composer_required_json_filename = $this->getVendorPath() . '/acquia/blt/template/composer.json';
      if (file_exists($composer_required_json_filename)) {
        $composer_required_json = json_decode(file_get_contents($composer_required_json_filename), TRUE);
        if ($composer_required_json['extra']['installer-paths'] != $extra['installer-paths']) {
          $this->io->write('<warning>Warning: The value for extras.installer-paths in composer.json differs from BLT\'s recommended values.</warning>');
          $this->io->write('<warning>See https://github.com/acquia/blt/blob/8.x/template/composer.json</warning>');
        }
      }
    }
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
   * Execute blt update after update command has been executed, if applicable.
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
   * Executes `blt update` and `blt-console blt:update` commands.
   *
   * @param $version
   */
  protected function executeBltUpdate($version) {
    $options = $this->getOptions();

    if ($this->isInitialInstall()) {
      $this->io->write('<info>Creating BLT templated files...</info>');
      if ($this->isNewProject()) {
        // The BLT command will not work at this point because the .git dir doesn't exist yet.
        $success = $this->executeCommand($this->getVendorPath() . '/acquia/blt/bin/blt internal:create-project --ansi', [], TRUE);
      }
      else {
        $success = $this->executeCommand($this->getVendorPath() . '/acquia/blt/bin/blt internal:add-to-project --ansi -y', [], TRUE);
      }
    }
    elseif ($options['blt']['update']) {
      $this->io->write('<info>Updating BLT templated files...</info>');
      $success = $this->executeCommand('blt update --ansi -y', [], TRUE);
      if (!$success) {
        $this->io->write("<error>BLT update script failed! Run `blt update -verbose` to retry.</error>");
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
    if (!file_exists($this->getRepoRoot() . '/blt/project.yml')
      && !file_exists($this->getRepoRoot() . '/blt/.schema-version')
      ) {
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
    if (!empty($composer_json['name'] && $composer_json['name'] == 'acquia/blt-project')) {
      return TRUE;
    }
    return FALSE;
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
