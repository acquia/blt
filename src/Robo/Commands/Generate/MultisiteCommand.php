<?php

namespace Acquia\Blt\Robo\Commands\Generate;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\YamlMunge;
use Acquia\Blt\Robo\Exceptions\BltException;
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

    $site_name = $this->getNewSiteName($options);
    $new_site_dir = $this->getConfigValue('docroot') . '/sites/' . $site_name;

    if (file_exists($new_site_dir)) {
      throw new BltException("Cannot generate new multisite, $new_site_dir already exists!");
    }

    $domain = $this->getNewSiteDoman($options, $site_name);
    $url = parse_url($domain);

    $this->setLocalDbConfig();
    if ($this->getInspector()->isDrupalVmConfigPresent()) {
      $this->configureDrupalVm($url, $site_name);
    }
    $default_site_dir = $this->getConfigValue('docroot') . '/sites/default';
    $this->createDefaultBltSiteYml($default_site_dir);
    $this->createNewSiteDir($default_site_dir, $new_site_dir);
    $this->createNewSiteConfigDir($site_name);
    $this->createNewBltSiteYml($new_site_dir, $site_name, $url);
    $this->resetMultisiteConfig();

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

  protected function setLocalDbConfig() {
    $config_local_db = $this->confirm("Would you like to configure the local database credentials?");
    if ($config_local_db) {
      $default_db = $this->getConfigValue('drupal.db');
      $db = [];
      $db['database'] = $this->askDefault("Local database name",
        $default_db['database']);
      $db['username'] = $this->askDefault("Local database user",
        $default_db['username']);
      $db['password'] = $this->askDefault("Local database password",
        $default_db['password']);
      $db['host'] = $this->askDefault("Local database host",
        $default_db['host']);
      $db['port'] = $this->askDefault("Local database port",
        $default_db['port']);
      $this->getConfig()->set('drupal.db', $db);
    }
  }

  /**
   * @return string
   */
  protected function createDefaultBltSiteYml($default_site_dir) {
    if (!file_exists($default_site_dir . "/blt.site.yml")) {
      $initial_perms = fileperms($default_site_dir);
      chmod($default_site_dir, 0777);
      // Move project.local.hostname from project.yml to
      // sites/default/blt.site.yml.
      $default_site_yml = [];
      $default_site_yml['project']['local']['hostname'] = $this->getConfigValue('project.local.hostname');
      YamlMunge::writeFile($default_site_dir . "/blt.site.yml",
        $default_site_yml);
      $project_yml = YamlMunge::parseFile($this->getConfigValue('blt.config-files.project'));
      unset($project_yml['project']['local']['hostname']);
      YamlMunge::writeFile($this->getConfigValue('blt.config-files.project'),
        $project_yml);
      chmod($default_site_dir, $initial_perms);
    }
    return $default_site_dir;
  }

  /**
   * @param $new_site_dir
   * @param $site_name
   * @param $url
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function createNewBltSiteYml(
    $new_site_dir,
    $site_name,
    $url
  ) {
    $site_yml_filename = $new_site_dir . '/blt.site.yml';
    $site_yml['project']['machine_name'] = $site_name;
    $site_yml['project']['human_name'] = $site_name;
    $site_yml['project']['local']['protocol'] = $url['scheme'];
    $site_yml['project']['local']['hostname'] = $url['host'];
    $result = $this->taskWriteToFile($site_yml_filename)
      ->text(Yaml::dump($site_yml, 4))
      ->run();
    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to write $site_yml_filename.");
    }
  }

  /**
   * @param $default_site_dir
   * @param $new_site_dir
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function createNewSiteDir($default_site_dir, $new_site_dir) {
    $result = $this->taskCopyDir([$default_site_dir => $new_site_dir])->run();
    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to create $new_site_dir.");
    }
  }

  /**
   * @param $site_name
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function createNewSiteConfigDir($site_name) {
    $config_dir = $this->getConfigValue('docroot') . '/' . $this->getConfigValue('cm.core.path') . '/' . $site_name;
    $result = $this->taskFilesystemStack()
      ->mkdir($config_dir)
      ->run();
    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to create $config_dir.");
    }
  }

  protected function resetMultisiteConfig() {
    $this->getConfig()->set('multisites', []);
    $this->getConfig()->populateHelperConfig();
  }

  /**
   * @param $options
   * @param $site_name
   *
   * @return string
   */
  protected function getNewSiteDoman($options, $site_name) {
    if (empty($options['site-uri'])) {
      $domain = $this->askDefault("Local domain name",
        "http://local.$site_name.com");
    }
    else {
      $domain = $options['site-uri'];
    }
    return $domain;
  }

  /**
   * @param $options
   *
   * @return string
   */
  protected function getNewSiteName($options) {
    if (empty($options['site-name'])) {
      $site_name = $this->askRequired("Site machine name");
    }
    else {
      $site_name = $options['site-name'];
    }
    return $site_name;
  }

}
