<?php

namespace Acquia\Blt\Robo\Commands\Blt;

use Acquia\Blt\Robo\BltTasks;
use Symfony\Component\Yaml\Yaml;

/**
 * Defines commands for developing BLT.
 */
class DevCommand extends BltTasks {

  // Map host BLT path to guest BLT path so that symlinks work in guest.
  const DIRECTORY_MAPPING = [
    '../../packages/blt' => '/var/packages/blt',
    '../blt' => '/var/www/blt',
  ];

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
    $this->taskExec("composer config repositories.blt path {$options['blt-path']} && composer require acquia/blt:* --no-update")
      ->dir($this->getConfigValue('repo.root'))
      ->run();

    $this->taskExec('rm -rf vendor && composer update acquia/blt --with-dependencies')
      ->dir($this->getConfigValue('repo.root'))
      ->run();
    $projectDrupalVmConfigFile = $this->getConfigValue('vm.config');

    if ($projectDrupalVmConfigFile && isset(self::DIRECTORY_MAPPING[$options['blt-path']])) {
      $vm_config = Yaml::parse(file_get_contents($projectDrupalVmConfigFile));
      $existing_entry = array_filter($vm_config['vagrant_synced_folders'], function ($folder) {
        return in_array($folder['destination'], self::DIRECTORY_MAPPING);
      });
      if ($existing_entry) {
        return;
      }
      $vm_config['vagrant_synced_folders'][] = [
        'local_path' => $options['blt-path'],
        'destination' => self::DIRECTORY_MAPPING[$options['blt-path']],
        'type' => 'nfs',
      ];
      file_put_contents($projectDrupalVmConfigFile, Yaml::dump($vm_config, 4));
      $this->taskExec('vagrant halt && vagrant up')
        ->dir($this->getConfigValue('repo.root'))
        ->run();
    }
  }

}
