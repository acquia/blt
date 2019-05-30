<?php

namespace Acquia\Blt\Robo\Commands\Blt;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\YamlWriter;

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
    if (!file_exists($options['blt-path'] . '/src/Robo/Blt.php')) {
      $this->logger->error("Could not find BLT at {$options['blt-path']}. Please provide a valid blt-path argument.");
      return;
    }
    $this->taskExec("composer config repositories.blt path {$options['blt-path']} && composer require acquia/blt:* --no-update")
      ->dir($this->getConfigValue('repo.root'))
      ->run();

    $this->taskExec('rm -rf vendor && composer update acquia/blt --with-dependencies')
      ->dir($this->getConfigValue('repo.root'))
      ->run();

    $projectDrupalVmConfigFile = $this->getConfigValue('vm.config');
    if ($projectDrupalVmConfigFile && isset(self::DIRECTORY_MAPPING[$options['blt-path']])) {
      $yamlWriter = new YamlWriter($projectDrupalVmConfigFile);
      $vm_config = $yamlWriter->getContents();
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
      $yamlWriter->write($vm_config);
      $this->taskExec('vagrant halt && vagrant up')
        ->dir($this->getConfigValue('repo.root'))
        ->run();
    }
  }

}
