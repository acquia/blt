<?php

namespace Acquia\Blt\Robo\Commands\Blt;

use Acquia\Blt\Robo\BltTasks;
use Symfony\Component\Console\Input\InputOption;

/**
 * Defines commands for developing BLT.
 */
class DevCommand extends BltTasks {

  /**
   * Links to a local BLT package via a Composer path repository.
   *
   * @command blt:dev:link-composer
   * @param array $options
   */
  public function linkComposer($options = ['blt-path' => InputOption::VALUE_REQUIRED]) {
    $composer_json_filepath = $this->getConfigValue('repo.root') . '/composer.json';
    $composer_json = json_decode(file_get_contents($composer_json_filepath));
    $composer_json->repositories->blt = [
      'type' => 'path',
      'url' => $options['blt-path'],
    ];
    $composer_json->require->{'acquia/blt'} = '*';

    file_put_contents($composer_json_filepath, json_encode($composer_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $this->taskExec('composer update acquia/blt --with-dependencies')
      ->dir($this->getConfigValue('repo.root'))
      ->run();
  }

}
