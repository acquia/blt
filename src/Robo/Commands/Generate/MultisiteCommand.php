<?php

namespace Acquia\Blt\Robo\Commands\Generate;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
use function file_exists;
use function file_put_contents;
use Symfony\Component\Console\Input\InputOption;
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
  public function generate($options = [
    'site-name' => InputOption::VALUE_REQUIRED,
    'site-uri' => InputOption::VALUE_REQUIRED,
  ]) {
    $this->say("This will generate a new site in the docroot/sites directory.");
    $site_yml = [];

    if (empty($options['site-name'])) {
      $site_name = $this->askRequired("Site machine name");
    }
    else {
      $site_name = $options['site-name'];
    }
    $new_site_dir = $this->getConfigValue('docroot') . '/sites/' . $site_name;

    if (file_exists($new_site_dir)) {
      throw new BltException("Cannot generate new multisite, $new_site_dir already exists!");
    }

    if (empty($options['site-uri'])) {
      $domain = $this->askDefault("Local domain name", "http://local.$site_name.com");
    }
    else {
      $domain = $options['site-uri'];
    }
    $url = parse_url($domain);
    $site_yml['project']['machine_name'] = $site_name;
    $site_yml['project']['human_name'] = $site_name;
    $site_yml['project']['local']['hostname'] = $url['host'];
    $site_yml['project']['local']['hostname'] = $url['host'];

    $config_local_db = $this->confirm("Would you like to configure the local database credentials?");
    if ($config_local_db) {
      $default_db = $this->getConfigValue('drupal.db');
      $db = [];
      $db['database'] = $this->askDefault("Local database name", $default_db['database']);
      $db['username'] = $this->askDefault("Local database user", $default_db['username']);
      $db['password'] = $this->askDefault("Local database password", $default_db['password']);
      $db['host'] = $this->askDefault("Local database host", $default_db['host']);
      $db['port'] = $this->askDefault("Local database port", $default_db['port']);
      $this->getConfig()->set('drupal.db', $db);
    }

    if ($this->getInspector()->isDrupalVmConfigPresent()) {
      $this->configureDrupalVm($url, $site_name);
    }

    $default_site_dir = $this->getConfigValue('docroot') . '/sites/default';
    $this->taskCopyDir([$default_site_dir => $new_site_dir]);
    $result = $this->taskFilesystemStack()
      ->mkdir($this->getConfigValue('docroot') . '/' . $this->getConfigValue('cm.core.path') . '/' . $site_name)
      ->run();
    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to create $new_site_dir.");
    }

    $site_yml_filename = $new_site_dir . '/site.yml';
    $result = $this->taskWriteToFile($site_yml_filename)
      ->text(Yaml::dump($site_yml, 4))
      ->run();
    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to write $site_yml_filename.");
    }

    $this->getConfig()->set('multisites', []);
    $this->getConfig()->populateHelperConfig();
    $this->invokeCommand('setup:settings');
  }

  /**
   * Updates box/config.yml with settings for new multisite.
   *
   * @param string $url
   *   The local URL for the site.
   * @param string $site_name
   *   The machine name of the site.
   */
  protected function configureDrupalVm($url, $site_name) {
    $this->logger->warning("Automatically configuring your Drupal VM instance will remove formatting and comments from your config.yml file.");
    $configure_vm = $this->confirm("Would you like to generate a new virtual host entry for this site inside Drupal VM?");
    if ($configure_vm) {
      $this->projectDrupalVmConfigFile = $this->getConfigValue('vm.config');
      $vm_config = Yaml::parse(file_get_contents($this->projectDrupalVmConfigFile));
      $vm_config['apache_vhosts'][] = [
        'servername' => $url['host'],
        'documentroot' => $vm_config['apache_vhosts'][0]['documentroot'],
        'extra_parameters' => $vm_config['apache_vhosts'][0]['extra_parameters'],
      ];
      $vm_config['mysql_databases'][] = [
        'name' => $site_name,
        'encoding' => $vm_config['mysql_databases'][0]['encoding'],
        'collation' => $vm_config['mysql_databases'][0]['collation'],
      ];
      file_put_contents($this->projectDrupalVmConfigFile,
        Yaml::dump($vm_config, 4));
    }
  }

}
