<?php

namespace Acquia\Blt\Robo\Wizards;

use Acquia\Blt\Robo\Common\StringManipulator;
use Acquia\Club\Configuration\ProjectConfiguration;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class NewSiteWizard.
 *
 * @package Acquia\Blt\Robo\Wizards
 */
class NewSiteWizard extends Wizard {

  /**
   * Wizard for setting initial configuration.
   */
  public function configureSite($options) {
    $recipe_filename = $options['recipe'];
    if ($recipe_filename) {
      $answers = $this->loadRecipeFile($recipe_filename);
    }
    else {
      $answers = $this->askForAnswers();
    }

    $this->say("<comment>You have entered the following values:</comment>");
    $this->printArrayAsTable($answers);
    if ($answers['vm']) {
      try {
        $this->invokeCommand('vm');
      }
      catch (\Exception $e) {
        $this->output->writeln("<error>Something went wrong with your VM initialization. Continuing setup...</error>");
      }
    }
    if (!empty($answers['ci']['provider'])) {
      $this->invokeCommand("ci:{$answers['ci']['provider']}:init");
    }
  }

  /**
   * @param string $filename
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
   * @return array
   */
  protected function askForAnswers() {
    $this->say("<info>Let's start by entering some information about your project.</info>");
    $answers['human_name'] = $this->ask("Project name (human readable):");
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
      $answers['ci']['provider'] = $this->askChoice('Choose a Continuous Integration provider:', $provider_options, [1]);
    }

    return $answers;
  }

  protected function updateProjectYml($answers) {
    $config_file = $this->getConfigValue('repo.root') . '/blt/project.yml';
    $config = Yaml::parse(file_get_contents($config_file));
    $config['project']['prefix'] = $answers['prefix'];
    $config['project']['machine_name'] = $answers['machine_name'];
    $config['project']['human_name'] = $answers['human_name'];
    // Hostname cannot contain underscores.
    $machine_name_safe = str_replace('_', '-', $answers['machine_name']);
    $config['project']['local']['hostname'] = str_replace('${project.machine_name}', $machine_name_safe, $config['project']['local']['hostname']);
    $this->fs->dumpFile($config_file, Yaml::dump($config));
  }

}
