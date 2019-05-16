<?php

namespace Acquia\Blt\Robo\Commands\Blt;

use Acquia\Blt\Robo\BltTasks;
use Symfony\Component\Yaml\Yaml;

/**
 * Defines commands for developing BLT.
 */
class DevCommand extends BltTasks {

  /**
   * Links to a local BLT package via a Composer path repository.
   *
   * Also sets up DrupalVM NFS mounts to use this BLT path. This currently
   * requires the BLT path to be '../../packages/blt'.
   *
   * @command blt:dev:link-composer
   *
   * @param array $options
   */
  public function linkComposer($options = ['blt-path' => '../../packages/blt']) {
    $composer_json_filepath = $this->getConfigValue('repo.root') . '/composer.json';
    $composer_json = json_decode(file_get_contents($composer_json_filepath));
    $composer_json->repositories->blt = [
      'type' => 'path',
      'url' => $options['blt-path'],
    ];
    $composer_json->require->{'acquia/blt'} = '*';

    file_put_contents($composer_json_filepath, json_encode($composer_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $this->taskExec('rm -rf vendor && composer update acquia/blt --with-dependencies')
      ->dir($this->getConfigValue('repo.root'))
      ->run();

    $projectDrupalVmConfigFile = $this->getConfigValue('vm.config');
    // Map host BLT path to guest BLT path so that symlinks work in guest.
    $directoryMapping = [
      '../../packages/blt' => '/var/packages/blt',
      '../blt' => '/var/www/blt',
    ];
    if ($projectDrupalVmConfigFile && isset($directoryMapping[$options['blt-path']])) {
      $vm_config = Yaml::parse(file_get_contents($projectDrupalVmConfigFile));
      $vm_config['vagrant_synced_folders'][] = [
        'local_path' => $options['blt-path'],
        'destination' => $directoryMapping[$options['blt-path']],
        'type' => 'nfs',
      ];
      file_put_contents($projectDrupalVmConfigFile, Yaml::dump($vm_config, 4));
      $this->taskExec('vagrant halt && vagrant up')
        ->dir($this->getConfigValue('repo.root'))
        ->run();
    }
  }

}
