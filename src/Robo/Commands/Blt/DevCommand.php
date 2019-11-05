<?php

namespace Acquia\Blt\Robo\Commands\Blt;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\YamlWriter;

/**
 * Defines commands for developing BLT.
 */
class DevCommand extends BltTasks {

  // Map host BLT path to guest BLT path so that symlinks work in guest.
  const VM_DIRECTORY_MAPPING = [
    '../../packages/blt' => '/var/packages/blt',
    '../blt' => '/var/www/blt',
  ];
  const LANDO_DIRECTORY_MAPPING = [
    '../../packages/blt' => '/packages/blt',
    '../blt' => '/blt',
  ];

  /**
   * Links to a local BLT package via a Composer path repository.
   *
   * Also sets up DrupalVM NFS mounts to use this BLT path. This currently
   * requires the BLT path to be '../../packages/blt'.
   *
   * @param array $options
   *   Options.
   *
   * @command blt:dev:link-composer
   */
  public function linkComposer(array $options = ['blt-path' => '../../packages/blt']) {
    if (!file_exists($options['blt-path'] . '/src/Robo/Blt.php')) {
      $this->logger->error("Could not find BLT at {$options['blt-path']}. Please provide a valid blt-path argument.");
      return;
    }

    $symlink = $this->getConfigValue('repo.root') . '/vendor/acquia/blt';
    if (filetype($symlink) !== 'link') {
      // Switch to local BLT version.
      $this->taskExec("composer config repositories.blt path {$options['blt-path']} && composer require acquia/blt:* --no-update")
        ->dir($this->getConfigValue('repo.root'))
        ->run();

      // Remove any patches.
      $this->taskExec("composer config --unset extra.patches.acquia/blt")
        ->dir($this->getConfigValue('repo.root'))
        ->run();

      // Nuke and reinitialize Composer to pick up changes.
      $this->taskExec('rm -rf vendor && composer update acquia/blt --with-dependencies')
        ->dir($this->getConfigValue('repo.root'))
        ->run();
    }

    // Mount local BLT in DrupalVM.
    if ($this->getInspector()->isDrupalVmConfigPresent()) {
      if (!isset(self::VM_DIRECTORY_MAPPING[$options['blt-path']])) {
        $this->logger->info('BLT path is not valid for usage with DrupalVM.');
        return;
      }
      $yamlWriter = new YamlWriter($this->getConfigValue('vm.config'));
      $vm_config = $yamlWriter->getContents();
      $existing_entry = array_filter($vm_config['vagrant_synced_folders'], function ($folder) {
        return in_array($folder['destination'], self::VM_DIRECTORY_MAPPING);
      });
      if ($existing_entry) {
        return;
      }
      $vm_config['vagrant_synced_folders'][] = [
        'local_path' => $options['blt-path'],
        'destination' => self::VM_DIRECTORY_MAPPING[$options['blt-path']],
        'type' => 'nfs',
      ];
      $yamlWriter->write($vm_config);
      $this->taskExec('vagrant halt && vagrant up')
        ->dir($this->getConfigValue('repo.root'))
        ->run();
    }

    // Mount local BLT in Lando.
    if ($this->getInspector()->isLandoConfigPresent()) {
      if (!isset(self::LANDO_DIRECTORY_MAPPING[$options['blt-path']])) {
        $this->logger->info('BLT path is not valid for usage with Lando.');
        return;
      }
      $yamlWriter = new YamlWriter($this->getConfigValue('repo.root') . '/.lando.yml');
      $lando_config = $yamlWriter->getContents();
      if (isset($lando_config['services']['appserver']['overrides']['volumes'])) {
        $existing_entry = array_filter($lando_config['services']['appserver']['overrides']['volumes'], function ($folder) {
          list(, $dest) = explode(':', $folder);
          return in_array($dest, self::LANDO_DIRECTORY_MAPPING);
        });
        if ($existing_entry) {
          return;
        }
      }
      $lando_config['services']['appserver']['overrides']['volumes'] = [
        $options['blt-path'] . ':' . self::LANDO_DIRECTORY_MAPPING[$options['blt-path']],
      ];
      $yamlWriter->write($lando_config);
      $this->taskExec('lando rebuild -y')
        ->dir($this->getConfigValue('repo.root'))
        ->run();
    }
  }

}
