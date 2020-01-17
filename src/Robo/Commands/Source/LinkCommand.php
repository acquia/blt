<?php

namespace Acquia\Blt\Robo\Commands\Source;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\YamlWriter;
use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * Defines commands for linking packages for development.
 */
class LinkCommand extends BltTasks {

  /**
   * Link a package into your Drupal installation for development purposes.
   *
   * Use this command when developing a Drupal module or other Composer package
   * a separate working directory from your Drupal application.
   *
   * This command will link the package into your Drupal application using a
   * Composer path repository and mount your package in a DrupalVM or Lando
   * environment if one is present.
   *
   * Due to limitations of NFS mounts, your package should exist either in a
   * sibling or cousin directory to your Drupal application. In other words, if
   * your package is named "foo", the only valid package paths relative to your
   * Drupal application are `../foo` or `../../[*]/foo`.
   *
   * Other structures might work but are untested.
   *
   * @param array $options
   *   The package name, path, and version to link.
   *
   * @command source:link
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function linkComposer(array $options = [
    'package-name' => 'acquia/blt',
    'package-path' => '../../packages/blt',
    'package-version' => '*',
  ]) {
    $path_parts = explode('/', $options['package-path']);
    $path_counts = array_count_values($path_parts);
    $levels = $path_counts['..'];
    if ($path_parts[0] != '..') {
      throw new BltException("Package must exist outside of the current directory.");
    }
    if (!file_exists($options['package-path'] . '/composer.json')) {
      throw new BltException("Could not find a valid Composer project at {$options['package-path']}. Please provide a valid package-path argument.");
    }

    // Set up composer path repository.
    $this->taskExec("composer config repositories." . end($path_parts) . " path {$options['package-path']} && composer require {$options['package-name']}:{$options['package-version']} --no-update")
      ->dir($this->getConfigValue('repo.root'))
      ->run();

    // Remove any patches.
    $this->taskExec("composer config --unset extra.patches.{$options['package-name']}")
      ->dir($this->getConfigValue('repo.root'))
      ->run();

    // Nuke and reinitialize Composer to pick up changes.
    $this->taskExec("rm -rf vendor && composer update {$options['package-name']} --with-dependencies")
      ->dir($this->getConfigValue('repo.root'))
      ->run();

    // Mount local BLT in DrupalVM.
    if ($this->getInspector()->isDrupalVmConfigPresent()) {
      $yamlWriter = new YamlWriter($this->getConfigValue('vm.config'));
      $vm_config = $yamlWriter->getContents();
      switch ($levels) {
        case 1:
          $destination = '/var/www/' . end($path_parts);
          break;

        case 2:
          $destination = '/var/' . $path_parts[2] . '/' . $path_parts[3];
          break;

        default:
          $destination = '/' . implode('/', $path_parts);
      }
      $vm_config['vagrant_synced_folders'][] = [
        'local_path' => $options['package-path'],
        'destination' => $destination,
        'type' => 'nfs',
      ];
      $yamlWriter->write($vm_config);
      $this->taskExec('vagrant halt && vagrant up')
        ->dir($this->getConfigValue('repo.root'))
        ->run();
    }

    // Mount local BLT in Lando.
    if ($this->getInspector()->isLandoConfigPresent()) {
      $yamlWriter = new YamlWriter($this->getConfigValue('repo.root') . '/.lando.yml');
      $lando_config = $yamlWriter->getContents();
      switch ($levels) {
        case 1:
          $destination = '/' . end($path_parts);
          break;

        case 2:
          $destination = '/' . $path_parts[2] . '/' . $path_parts[3];
          break;

        default:
          $destination = '/' . implode('/', $path_parts);
      }
      if (!isset($lando_config['services']['appserver']['overrides']['volumes'])) {
        $lando_config['services']['appserver']['overrides']['volumes'] = [];
      }
      $lando_config['services']['appserver']['overrides']['volumes'][] = $options['package-path'] . ':' . $destination;
      $yamlWriter->write($lando_config);
      $this->taskExec('lando rebuild -y')
        ->dir($this->getConfigValue('repo.root'))
        ->run();
    }
  }

}
