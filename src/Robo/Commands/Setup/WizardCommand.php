<?php

namespace Acquia\Blt\Robo\Commands\Setup;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\StringManipulator;
use Acquia\Blt\Robo\Common\YamlMunge;
use Acquia\Blt\Robo\Config\ProjectConfiguration;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class WizardCommand.
 *
 * @package Acquia\Blt\Robo\Wizards
 */
class WizardCommand extends BltTasks {

  /**
   * Wizard for setting initial configuration.
   *
   * @command wizard
   */
  public function wizard($options = [
    'recipe' => InputOption::VALUE_REQUIRED,
  ]) {
    $recipe_filename = $options['recipe'];
    if ($recipe_filename) {
      $answers = $this->loadRecipeFile($recipe_filename);
    }
    else {
      $answers = $this->askForAnswers();
    }

    $this->say("<comment>You have entered the following values:</comment>");
    $this->printArrayAsTable($answers);
    $continue = $this->confirm("Continue?");
    if (!$continue) {
      return 1;
    }

    $this->updateProjectYml($answers);

    if (!empty($answers['ci']['provider'])) {
      $this->invokeCommand("ci:{$answers['ci']['provider']}:init");
    }

    if ($answers['vm']) {
      $this->invokeCommand('vm', [
        [
          'no-boot' => '--yes',
        ],
      ]);
    }
  }

  /**
   * Load a recipe.
   *
   * @param string $filename
   *   The recipe filename.
   *
   * @return array
   */
  protected function loadRecipeFile($filename) {
    if (!file_exists($filename)) {
      throw new FileNotFoundException($filename);
    }
    $recipe = Yaml::parse(
      file_get_contents($filename)
    );
    $configs = [$recipe];
    $processor = new Processor();
    $configuration_tree = new ProjectConfiguration();
    $processed_configuration = $processor->processConfiguration(
      $configuration_tree,
      $configs
    );

    return $processed_configuration;
  }

  /**
   * Prompts the user for information.
   *
   * @return array
   *   An array of answers.
   */
  protected function askForAnswers() {
    $this->say("<info>Let's start by entering some information about your project.</info>");
    $answers['human_name'] = $this->askDefault("Project name (human readable):", $this->getConfigValue('project.human_name'));
    $default_machine_name = StringManipulator::convertStringToMachineSafe($answers['human_name']);
    $answers['machine_name'] = $this->askDefault("Project machine name:", $default_machine_name);
    $default_prefix = StringManipulator::convertStringToPrefix($answers['human_name']);
    $answers['prefix'] = $this->askDefault("Project prefix:", $default_prefix);

    $this->say("<info>Great. Now let's make some choices about how your project will be set up.</info>");
    $answers['vm'] = $this->confirm('Do you want to create a VM?');
    $ci = $this->confirm('Do you want to use Continuous Integration?');
    if ($ci) {
      $provider_options = [
        'pipelines' => 'Acquia Pipelines',
        'travis' => 'Travis CI',
      ];
      $answers['ci']['provider'] = $this->askChoice('Choose a Continuous Integration provider:', $provider_options, 'travis');
    }

    $cm = $this->confirm('Do you want use Drupal core configuration management?');
    if ($cm) {
      $strategy_options = [
        'config-split' => 'Config Split (recommended)',
        'features' => 'Features',
        'core-only' => 'Core only',
      ];
      $answers['cm']['strategy'] = $this->askChoice('Choose a configuration management strategy:', $strategy_options, 'config-split');
    }
    else {
      $answers['cm']['strategy'] = 'none';
    }

    $profile_options = [
      'lightning' => 'Lightning',
      'minimal' => 'Minimal',
      'standard' => 'Standard',
    ];
    $this->say("You may change the installation profile later.");
    $answers['profile'] = $this->askChoice('Choose an installation profile:', $profile_options, 'lightning');

    return $answers;
  }

  /**
   * Updates project.yml with values from askForAnswers().
   *
   * @param array $answers
   *   Answers from $this->askForAnswers().
   */
  protected function updateProjectYml($answers) {
    $config_file = $this->getConfigValue('blt.config-files.project');
    $config = YamlMunge::parseFile($config_file);
    $config['project']['prefix'] = $answers['prefix'];
    $config['project']['machine_name'] = $answers['machine_name'];
    $config['project']['human_name'] = $answers['human_name'];
    $config['project']['profile']['name'] = $answers['profile'];
    // Hostname cannot contain underscores.
    $machine_name_safe = str_replace('_', '-', $answers['machine_name']);
    $config['project']['local']['hostname'] = str_replace('${project.machine_name}', $machine_name_safe, $config['project']['local']['hostname']);

    if (isset($answers['cm']['strategy'])) {
      $config['cm']['strategy'] = $answers['cm']['strategy'];
    }

    YamlMunge::writeFile($config_file, $config);
    $this->say("<info>$config_file updated.</info>");
  }

}
