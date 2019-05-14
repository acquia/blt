<?php

namespace Acquia\Blt\Robo\Commands\Blt;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
use Symfony\Component\Console\Input\InputOption;
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
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function linkComposer($options = ['blt-path' => InputOption::VALUE_REQUIRED]) {
    if (!$options['blt-path']) {
      throw new BltException("Argument blt-path is required.");
    }
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
    if ($projectDrupalVmConfigFile && $options['blt-path'] == '../../packages/blt') {
      // @todo Support paths other than '../../packages/blt'.
      $vm_config = Yaml::parse(file_get_contents($projectDrupalVmConfigFile));
      $vm_config['vagrant_synced_folders'][] = [
        'local-path' => $options['blt-path'],
        'destination' => '/var/packages/blt',
        'type' => 'nfs',
      ];
      file_put_contents($projectDrupalVmConfigFile, Yaml::dump($vm_config, 4));
      $this->taskExec('vagrant halt && vagrant up');
    }
  }

}
