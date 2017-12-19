<?php

namespace Acquia\Blt\Robo\Commands\Vm;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\YamlMunge;
use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * Defines commands in the "vm:lando" namespace.
 */
class LandoCommand extends BltTasks {

  /**
   * Configures and boots a Lando VM.
   *
   * @command vm:lando:init
   *
   * @throws \BltException
   */
  public function vm() {
    if (!$this->getInspector()->commandExists('lando')) {
      throw new BltException("lando must be installed on this machine.");
    }

    // Change default drupal.db.host value to "database".
    $project_yml = YamlMunge::parseFile($this->getConfigValue('blt.config-files.project'));
    $project_yml['drupal']['db']['host'] = 'database';
    YamlMunge::writeFile($this->getConfigValue('blt.config-files.project'), $project_yml);

    // Re-generate local.settings.php to use new drupal.db.host value.
    $site = $this->getConfigValue('site');
    $local_settings_file = "$site/settings/local.settings.php";
    $this->taskFilesystemStack()->remove($local_settings_file);
    $this->invokeCommand('setup:settings');

    // Copy default .lando.yml file into place.
    $this->taskFilesystemStack()
      ->copy(
        $this->getConfigValue('blt.root') . '/scripts/lando/.lando.yml',
        $this->getConfigValue('repo.root') . '/.lando.yml'
      )
      ->run();
    $this->getConfig()->expandFileProperties($this->getConfigValue('repo.root') . '/.lando.yml');[]

    // Make "blt" bin accessible to $PATH in Lando container.
    $this->taskExecStack()
      ->dir($this->getConfigValue('repo.root'))
      ->exec('lando ssh -u root -c "ln -s /app/vendor/bin/blt /usr/bin/blt"')
      ->run();
  }

}
