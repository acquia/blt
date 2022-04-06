<?php

namespace Acquia\Blt\Robo;

use Acquia\Blt\Robo\Common\EnvironmentDetector;
use Acquia\Blt\Robo\Common\Executor;
use Acquia\Blt\Robo\Common\IO;
use Acquia\Blt\Robo\Common\UserConfig;
use Acquia\Blt\Robo\Filesets\FilesetManager;
use Acquia\Blt\Robo\Inspector\Inspector;
use Acquia\Blt\Robo\Inspector\InspectorAwareInterface;
use Acquia\Blt\Robo\Log\BltLogStyle;
use Acquia\Blt\Robo\Wizards\SetupWizard;
use Acquia\Blt\Update\Updater;
use Composer\Autoload\ClassLoader;
use Composer\InstalledVersions;
use Consolidation\AnnotatedCommand\CommandFileDiscovery;
use League\Container\Container;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Robo\Collection\CollectionBuilder;
use Robo\Common\ConfigAwareTrait;
use Robo\Config\Config;
use Robo\Robo;
use Robo\Runner as RoboRunner;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zumba\Amplitude\Amplitude;

/**
 * The BLT Robo application.
 */
class Blt implements ContainerAwareInterface, LoggerAwareInterface {

  use ConfigAwareTrait;
  use ContainerAwareTrait;
  use LoggerAwareTrait;
  use IO;

  /**
   * The Robo task runner.
   *
   * @var \Robo\Runner
   */
  private $runner;

  /**
   * An array of Robo commands available to the application.
   *
   * @var string[]
   */
  private $commands = [];

  /**
   * Object constructor.
   *
   * @param \Robo\Config\Config $config
   *   The BLT configuration.
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   The input.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   The output.
   * @param \Composer\Autoload\ClassLoader $classLoader
   *   The Composer classLoader.
   */
  public function __construct(
    Config $config,
    InputInterface $input = NULL,
    OutputInterface $output = NULL,
    ClassLoader $classLoader = NULL
  ) {

    $this->setConfig($config);
    $application = new Application('BLT', Blt::getVersion());
    $container = new Container();
    Robo::configureContainer($container, $application, $config, $input, $output, $classLoader);
    if (self::usingLegacyContainer()) {
      $application->setDispatcher($container->get('eventDispatcher'));
    }
    else {
      Robo::finalizeContainer($container);
    }
    $this->setContainer($container);
    $this->addDefaultArgumentsAndOptions($application);
    $this->configureContainer($container);
    $this->addBuiltInCommandsAndHooks();
    $this->runner = new RoboRunner();
    if (isset($classLoader)) {
      $this->runner->setClassLoader($classLoader);
    }
    $this->runner->setContainer($container);
    $this->runner->setRelativePluginNamespace('Blt\Plugin');

    $this->setLogger($container->get('logger'));

    $this->initializeAmplitude();
  }

  /**
   * Get installed BLT version.
   *
   * @return mixed|null
   *   BLT version.
   */
  public static function getVersion() {
    if (InstalledVersions::isInstalled('acquia/blt') && $version = InstalledVersions::getVersion('acquia/blt')) {
      return $version;
    }

    return 'Unknown';
  }

  /**
   * Initializes Amplitude.
   */
  private function initializeAmplitude() {
    $userConfig = new UserConfig(self::configDir());
    $amplitude = Amplitude::getInstance();
    $amplitude->init('dfd3cba7fa72065cde9edc2ca22d0f37')
      ->setDeviceId(EnvironmentDetector::getMachineUuid());
    if (!$userConfig->isTelemetryEnabled()) {
      $amplitude->setOptOut(TRUE);
    }
    $amplitude->logQueuedEvents();
  }

  /**
   * Add the commands and hooks which are shipped with core BLT.
   */
  private function addBuiltInCommandsAndHooks() {
    $commands = $this->getCommands([
      'path' => __DIR__ . '/Commands',
      'namespace' => 'Acquia\Blt\Robo\Commands',
    ]);
    $hooks = $this->getHooks([
      'path' => __DIR__ . '/Hooks',
      'namespace' => 'Acquia\Blt\Robo\Hooks',
    ]);
    $this->commands = array_merge($commands, $hooks);
  }

  /**
   * Discovers command classes using CommandFileDiscovery.
   *
   * @param string[] $options
   *   Elements as follow
   *    string path      The full path to the directory to search for commands
   *    string namespace The full namespace for the command directory.
   *
   * @return array
   *   An array of Command classes
   */
  private function getCommands(
    array $options = ['path' => NULL, 'namespace' => NULL]
  ) {
    $discovery = new CommandFileDiscovery();
    $discovery
      ->setSearchPattern('*Command.php')
      ->setSearchLocations([]);
    return $discovery->discover($options['path'], $options['namespace']);
  }

  /**
   * Discovers hooks using CommandFileDiscovery.
   *
   * @param string[] $options
   *   Elements as follow
   *    string path      The full path to the directory to search for commands
   *    string namespace The full namespace for the command directory.
   *
   * @return array
   *   An array of Hook classes
   */
  private function getHooks(
    array $options = ['path' => NULL, 'namespace' => NULL]
  ) {
    $discovery = new CommandFileDiscovery();
    $discovery->setSearchPattern('*Hook.php')->setSearchLocations([]);
    return $discovery->discover($options['path'], $options['namespace']);
  }

  /**
   * Add any global arguments or options that apply to all commands.
   *
   * @param \Acquia\Blt\Robo\Application $app
   *   The Symfony application.
   */
  private function addDefaultArgumentsAndOptions(Application $app) {
    $app->getDefinition()
      ->addOption(
        new InputOption('--define', '-D', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Define a configuration item value.', [])
      );
    $app->getDefinition()
      ->addOption(
        new InputOption('--environment', NULL, InputOption::VALUE_REQUIRED, 'Set the environment to load config from blt/[env].yml file.', [])
      );
    $app->getDefinition()
      ->addOption(
        new InputOption('--site', NULL, InputOption::VALUE_REQUIRED, 'The multisite to execute this command against.', [])
      );
  }

  /**
   * Register the necessary classes for BLT.
   */
  public function configureContainer($container) {
    // We create our own builder so that non-command classes are able to
    // implement task methods, like taskExec(). Yes, there are now two builders
    // in the container. "collectionBuilder" used for the actual command that
    // was executed, and "builder" to be used with non-command classes.
    $blt_tasks = new BltTasks();
    $builder = new CollectionBuilder($blt_tasks);
    $blt_tasks->setBuilder($builder);
    $container->add('builder', $builder);
    $updater = new Updater('Acquia\Blt\Update\Updates', $this->getConfig()->get('repo.root'));

    if (self::usingLegacyContainer()) {
      // Logic for league/container 3.x.
      $container->share('logStyler', BltLogStyle::class);
      $container->share('inspector', Inspector::class)
        ->addArgument('executor');
      $container->share('filesetManager', FilesetManager::class);
      $container->share('updater', $updater);
    }
    else {
      // Logic for league/container 4.x.
      $container->addShared('logStyler', BltLogStyle::class);
      $container->addShared('inspector', Inspector::class)
        ->addArgument('executor');
      $container->addShared('filesetManager', FilesetManager::class);
      $container->addShared('updater', $updater);
    }

    $container->add(SetupWizard::class)
      ->addArgument('executor');
    $container->add('executor', Executor::class)
      ->addArgument('builder');
    $container->inflector(InspectorAwareInterface::class)
      ->invokeMethod('setInspector', ['inspector']);

    /** @var \Consolidation\AnnotatedCommand\AnnotatedCommandFactory $factory */
    $factory = $container->get('commandFactory');
    // Tell the command loader to only allow command functions that have a
    // name/alias.
    $factory->setIncludeAllPublicMethods(FALSE);
  }

  /**
   * Runs the instantiated BLT application.
   *
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   An input object to run the application with.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   An output object to run the application with.
   *
   * @return int
   *   The exiting status code of the application
   */
  public function run(InputInterface $input, OutputInterface $output) {
    $application = $this->getContainer()->get('application');
    $status_code = $this->runner->run($input, $output, $application, $this->commands);

    $userConfig = new UserConfig(self::configDir());
    $event_properties = $userConfig->getTelemetryUserData();
    $event_properties['exit_code'] = $status_code;
    $event_properties['command'] = $input->getFirstArgument();
    Amplitude::getInstance()->queueEvent('run command', $event_properties);

    return $status_code;
  }

  /**
   * Common config directory.
   *
   * @return string
   *   Config directory path.
   */
  public static function configDir() {
    return getenv('HOME') . DIRECTORY_SEPARATOR . '.config' . DIRECTORY_SEPARATOR . 'blt';
  }

  /**
   * Determine if the legacy version of league/container is in use.
   *
   * @return bool
   *   TRUE if using the legacy container, FALSE otherwise.
   */
  public static function usingLegacyContainer() {
    return method_exists(Container::class, 'share');
  }

}
