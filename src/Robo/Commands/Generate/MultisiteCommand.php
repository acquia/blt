<?php

namespace Acquia\Blt\Robo\Commands\Generate;

use Acquia\Blt\Robo\BltTasks;
use function file_exists;
use function file_put_contents;
use Symfony\Component\Yaml\Yaml;

/**
 * Defines commands in the "generate:multisite" namespace.
 */
class MultisiteCommand extends BltTasks {

  /**
   * Generates a new multisite.
   *
   * @command generate:multisite
   *
   */
  public function generate() {
    $this->say("This will generate a new site in the docroot/sites directory.");
    $site_yml = [];
    $site_name = $this->ask("Machine name");
    $domain = parse_url($this->ask("Local domain name"));
    $site_yml['project']['local']['hostname'] = $domain['host'];

    $config_local_db = $this->ask("Would you like to configure the local database credentials?");
    if ($config_local_db) {
      $default_db = $this->getDefaultDbCreds();
      $db = [];
      $db['username'] = $this->askDefault("Local database user", $default_db['username']);
      $db['password'] = $this->askDefault("Local database password", $default_db['password']);
      $db['host'] = $this->askDefault("Local database host", $default_db['host']);
      $db['port'] = $this->askDefault("Local database port", $default_db['port']);
      $this->getConfig()->set('drupal.db', $db);
    }

    $this->logger->warning("Automatically configuring your Drupal VM instance will remove  formatting and comments from your config.yml file.");
    $configure_vm = $this->ask("Would you like to generate a new virtual host entry for this site inside Drupal VM?");
    if ($configure_vm) {
      $this->projectDrupalVmConfigFile = $this->getConfigValue('vm.config');
      $vm_config = Yaml::parse(file_get_contents($this->projectDrupalVmConfigFile));
      $vm_config['apache_vhosts'][] = [
        'servername' => $domain['host'],
        'documentroot' => $vm_config['apache_vhosts'][0]['documentroot'],
        'extra_parameters' => $vm_config['apache_vhosts'][0]['extra_parameters'],
      ];
      $vm_config['mysql_databases'][] = [
        'name' => $site_name,
        'encoding' => $vm_config['mysql_databases'][0]['encoding'],
        'collation' => $vm_config['mysql_databases'][0]['collation'],
      ];
      file_put_contents($this->projectDrupalVmConfigFile, Yaml::dump($vm_config));
    }

    $new_site_dir = $this->getConfigValue('docroot') . '/sites/' . $site_name;
    $this->taskFilesystemStack()
      ->mkdir($new_site_dir)
      ->mkdir($this->getConfigValue('docroot') . '/config/' . $site_name)
      ->run();
    $this->taskWriteToFile($new_site_dir . '/site.yml')
      ->text(Yaml::dump($site_yml))
      ->run();
    $this->invokeCommand('setup:settings');
  }

  /**
   * @return mixed|null
   */
  protected function getDefaultDbCreds() {
    $default_local_settings_file = $this->getConfigValue('docroot') . '/sites/default/settings/local.settings.php';
    if (file_exists($default_local_settings_file)) {
      require $default_local_settings_file;
      return $databases['default']['default'];
    }

    return $this->getConfigValue('drupal.db');
  }

}
