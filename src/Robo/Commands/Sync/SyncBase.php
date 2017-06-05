<?php

namespace Acquia\Blt\Robo\Commands\Sync;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Config\YamlConfigProcessor;
use Robo\Config\YamlConfigLoader;

/**
 * Shared base class for sync:* commands.
 */
class SyncBase extends BltTasks {

  /**
   * @param $multisite_name
   */
  protected function loadMultisiteConfig($multisite_name) {
    $this->config->set('site', $multisite_name);
    $this->config->set('drush.uri', $multisite_name);

    // After having set site, this should now return the multisite
    // specific config.
    $site_config_file = $this->getConfigValue('blt.config-files.multisite');

    // Load multisite-specific config.
    $loader = new YamlConfigLoader();
    $processor = new YamlConfigProcessor();
    $processor->add($this->getConfig()->export());
    $processor->extend($loader->load($site_config_file));
    $this->getConfig()->import($processor->export());
  }

}
