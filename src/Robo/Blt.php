<?php

namespace Acquia\Blt\Robo;

use Acquia\Blt\Robo\Common\Executor;
use Acquia\Blt\Robo\Datastore\FileStore;
use Acquia\Blt\Robo\Filesets\FilesetManager;
use Acquia\Blt\Robo\Inspector\Inspector;
use Acquia\Blt\Robo\Inspector\InspectorAwareInterface;
use Acquia\Blt\Robo\Log\BltLogStyle;
use Acquia\Blt\Robo\Wizards\SetupWizard;
use Acquia\Blt\Robo\Wizards\TestsWizard;
use Acquia\Blt\Update\Updater;
use Consolidation\AnnotatedCommand\CommandFileDiscovery;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Robo\Collection\CollectionBuilder;
use Robo\Common\ConfigAwareTrait;
use Robo\Config\Config;
use Robo\Robo;
use Robo\Runner as RoboRunner;
use Symfony\Bridge\Twig\Command\LintCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * The BLT Robo application.
 */
class Blt implements ContainerAwareInterface, LoggerAwareInterface {

  use ConfigAwareTrait;
  use ContainerAwareTrait;
  use LoggerAwareTrait;

  /**
   * The BLT version.
   */
  const VERSION = '9.0.0';

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
   */
  public function __construct(
    Config $config,
    InputInterface $input = NULL,
    OutputInterface $output = NULL
  ) {

    $this->setConfig($config);
    $application = new Application('BLT', Blt::VERSION);
    $container = Robo::createDefaultContainer($input, $output, $application,
      $config);
    $this->setContainer($container);
    $this->addDefaultArgumentsAndOptions($application);
    $this->configureContainer($container);
    $this->addBuiltInCommandsAndHooks();
    $this->addPluginsCommandsAndHooks();
    $this->addSymfonyCommands($application);
    $this->runner = new RoboRunner();
    $this->runner->setContainer($container);

    $this->setLogger($container->get('logger'));
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
   * Registers custom commands and hooks defined project.
   */
  private function addPluginsCommandsAndHooks() {
    $commands = $this->getCommands([
      'path' => $this->getConfig()->get('repo.root') . '/blt/src/Commands',
      'namespace' => 'Acquia\Blt\Custom\Commands',
    ]);
    $hooks = $this->getHooks([
      'path' => $this->getConfig()->get('repo.root') . '/blt/src/Hooks',
      'namespace' => 'Acquia\Blt\Custom\Hooks',
    ]);
    $plugin_commands_hooks = array_merge($commands, $hooks);
    $this->commands = array_merge($this->commands, $plugin_commands_hooks);
  }

  /**
   * Adds Symfony (non-Robo) command classes to the application.
   *
   * @param \Acquia\Blt\Robo\Application $application
   */
  protected function addSymfonyCommands(Application $application) {
    $twig = new Environment(new FilesystemLoader());

    $repo_root = $this->getConfig()->get('repo.root');
    $extension_file_contents = file_get_contents($repo_root . '/docroot/core/lib/Drupal/Core/Template/TwigExtension.php');

    // Get any custom defined Twig filters to be ignored by linter.
    $twig_filters = (array) $this->getConfig()->get('validate.twig.filters');
    // Add Twig filters from Drupal TwigExtension to be ignored.
    $drupal_filters = [];
    if ($matches_count = preg_match_all("#new \\\\Twig_SimpleFilter\('([^']+)',#", $extension_file_contents, $matches)) {
      $drupal_filters = $matches[1];
    }
    $twig_filters = array_merge($twig_filters, $drupal_filters);
    foreach ($twig_filters as $filter) {
      $twig->addFilter(new \Twig_SimpleFilter($filter, function () {}));
    }

    // Get any custom defined Twig functions to be ignored by linter.
    $twig_functions = (array) $this->getConfig()->get('validate.twig.functions');
    // Add Twig functions from Drupal TwigExtension to be ignored.
    $drupal_functions = [];
    if ($matches_count = preg_match_all("#new \\\\Twig_SimpleFunction\('([^']+)',#", $extension_file_contents, $matches)) {
      $drupal_functions = $matches[1];
    }
    $twig_functions = array_merge($twig_functions, $drupal_functions);
    foreach ($twig_functions as $function) {
      $twig->addFunction(new \Twig_SimpleFunction($function, function () {}));
    }

    $command = new LintCommand('validate:twig:files');
    $command->setTwigEnvironment($twig);
    $application->add($command);
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
      ->setSearchLocations([])
      ->addExclude('Internal');
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
    $app->getDefinition()->addOption(new InputOption('--yes', '-y',
      InputOption::VALUE_NONE, 'Answer all confirmations with "yes"'));
    $app->getDefinition()
      ->addOption(
        new InputOption('--define', '-D', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Define a configuration item value.', [])
      );
  }

  /**
   * Register the necessary classes for BLT.
   */
  public function configureContainer($container) {
    $container->share('logStyler', BltLogStyle::class);

    // We create our own builder so that non-command classes are able to
    // implement task methods, like taskExec(). Yes, there are now two builders
    // in the container. "collectionBuilder" used for the actual command that
    // was executed, and "builder" to be used with non-command classes.
    $blt_tasks = new BltTasks();
    $builder = new CollectionBuilder($blt_tasks);
    $blt_tasks->setBuilder($builder);
    $container->add('builder', $builder);
    $container->add('executor', Executor::class)
      ->withArgument('builder');

    $container->share('inspector', Inspector::class)
      ->withArgument('executor');

    $container->inflector(InspectorAwareInterface::class)
      ->invokeMethod('setInspector', ['inspector']);

    $container->add(SetupWizard::class)
      ->withArgument('executor');
    $container->add(TestsWizard::class)
      ->withArgument('executor');

    $container->share('filesetManager', FilesetManager::class);

    $updater = new Updater('Acquia\Blt\Update\Updates', $this->getConfig()->get('repo.root'));
    $container->share('updater', $updater);

    /** @var \Consolidation\AnnotatedCommand\AnnotatedCommandFactory $factory */
    $factory = $container->get('commandFactory');
    // Tell the command loader to only allow command functions that have a
    // name/alias.
    $factory->setIncludeAllPublicMethods(FALSE);
    $factory->addCommandInfoAlterer(new BltCommandInfoAlterer());

    // Install our command cache into the command factory.
    $commandCacheDir = $this->getConfig()->get('blt.command-cache-dir');
    if (file_exists($commandCacheDir) && is_writable($commandCacheDir)) {
      $commandCacheDataStore = new FileStore($commandCacheDir);
      $factory->setDataStore($commandCacheDataStore);
    }
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

    return $status_code;
  }

}
