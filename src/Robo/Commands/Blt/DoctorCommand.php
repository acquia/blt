<?php

namespace Acquia\Blt\Robo\Commands\Blt;

use Acquia\Blt\Robo\BltTasks;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Defines commands in the "blt:doctor" namespace.
 */
class DoctorCommand extends BltTasks {

  /**
   * @command doctor
   */
  public function doctor() {

    if ($this->getInspector()->isDrupalVmLocallyInitialized()) {
      $drupal_vm_config = Yaml::parse(file_get_contents($this->getConfigValue('repo.root') . '/box/config.yml'));
      $repo_root = $drupal_vm_config['vagrant_synced_folders'][0]['destination'];
      $this->say("Drupal VM was detected. Running blt doctor inside of VM...");
      $result = $this->taskExec("vagrant exec '$repo_root/vendor/bin/drush cc drush && ./vendor/bin/drush --include=$repo_root/vendor/acquia/blt/drush blt-doctor -r $repo_root/docroot'")
        ->dir($this->getConfigValue('repo.root'))
        ->detectInteractive()
        ->run();
    }

    $drush_bin = $this->getConfigValue('composer.bin') . '/drush';
    $include_dir = $this->getConfigValue('blt.root') . '/drush';
    $alias = $this->getConfigValue('drush.alias');
    $this->taskExec("$drush_bin @$alias --include=$include_dir blt-doctor")
      ->dir($this->getConfigValue('docroot'))
      ->detectInteractive()
      ->run();
  }

}
