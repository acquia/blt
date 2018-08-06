<?php

namespace Acquia\Blt\Robo\Commands\Generate;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\YamlMunge;
use Acquia\Blt\Robo\Exceptions\BltException;
use Grasmash\YamlExpander\Expander;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

/**
 * Defines commands in the "recipes:multisite:init" namespace.
 */
class MultisiteCommand extends BltTasks {

  /**
   * Generates a new multisite.
   *
   * @command recipes:multisite:init
   *
   * @aliases rmi multisite
   *
   */
  public function generate($options = [
    'site-dir' => InputOption::VALUE_REQUIRED,
    'site-uri' => InputOption::VALUE_REQUIRED,
    'remote-alias' => InputOption::VALUE_REQUIRED,
  ]) {
    $this->say("This will generate a new site in the docroot/sites directory.");

    $site_name = $this->getNewSiteName($options);
    $new_site_dir = $this->getConfigValue('docroot') . '/sites/' . $site_name;

    if (file_exists($new_site_dir)) {
      throw new BltException("Cannot generate new multisite, $new_site_dir already exists!");
    }

    $domain = $this->getNewSiteDoman($options, $site_name);
    $url = parse_url($domain);
    // @todo Validate uri, ensure includes scheme.

    $newDBSettings = $this->setLocalDbConfig();
    if ($this->getInspector()->isDrupalVmConfigPresent()) {
      $this->configureDrupalVm($url, $site_name, $newDBSettings);
    }
    $default_site_dir = $this->getConfigValue('docroot') . '/sites/default';
    $this->createDefaultBltSiteYml($default_site_dir);
    $this->createSiteDrushAlias('default');
    $this->createNewSiteDir($default_site_dir, $new_site_dir);

    $remote_alias = $this->getNewSiteRemoteAlias($site_name, $options);
    $this->createNewBltSiteYml($new_site_dir, $site_name, $url, $remote_alias);
    $this->createNewSiteConfigDir($site_name);
    $this->createSiteDrushAlias($site_name);
    $this->resetMultisiteConfig();

    $this->invokeCommand('blt:init:settings');

    $this->say("New site generated at <comment>$new_site_dir</comment>");
    $this->say("Drush aliases generated:");
    if (!file_exists($default_site_dir . "/blt.yml")) {
      $this->say("  * @default.local");
    }
    $this->say("  * @$remote_alias");
    $this->say("Config directory created for new site at <comment>config/$site_name</comment>");
  }

  /**
   * Updates box/config.yml with settings for new multisite.
   *
   * @param array $url
   *   The local URL for the site.
   * @param string $site_name
   *   The machine name of the site.
   * @param array $newDBSettings
   *   An array of database configuration options or empty array.
   */
  protected function configureDrupalVm($url, $site_name, $newDBSettings) {
    $this->logger->warning("Automatically configuring your Drupal VM instance will remove formatting and comments from your config.yml file.");
    $configure_vm = $this->confirm("Would you like to generate new virtual host entry and database for this site inside Drupal VM?");
    if ($configure_vm) {
      $this->projectDrupalVmConfigFile = $this->getConfigValue('vm.config');
      $vm_config = Yaml::parse(file_get_contents($this->projectDrupalVmConfigFile));
      $vm_config['apache_vhosts'][] = [
        'servername' => $url['host'],
        'documentroot' => $vm_config['apache_vhosts'][0]['documentroot'],
        'extra_parameters' => $vm_config['apache_vhosts'][0]['extra_parameters'],
      ];

      // Set up the database and database user if the user opted to have the DB
      // configured in the VM.
      if ($newDBSettings) {
        $vm_config['mysql_databases'][] = [
          'name' => $newDBSettings['database'],
          'encoding' => $vm_config['mysql_databases'][0]['encoding'],
          'collation' => $vm_config['mysql_databases'][0]['collation'],
        ];

        $vm_config['mysql_users'][] = [
          'name' => $newDBSettings['username'],
          'host' => '%',
          'password' => $newDBSettings['password'],
          'priv' => $newDBSettings['database'] . '*:ALL',
        ];
      }
      file_put_contents($this->projectDrupalVmConfigFile,
        Yaml::dump($vm_config, 4));
    }
  }

  /**
   * Prompts for and sets config for new database.
   *
   * @return array
   *   Empty array if user did not want to configure local db. Populated array
   *   otherwise.
   */
  protected function setLocalDbConfig() {
    $config_local_db = $this->confirm("Would you like to configure the local database credentials?");
    $db = [];

    if ($config_local_db) {
      $default_db = $this->getConfigValue('drupal.db');
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

    return $db;
  }

  /**
   * @return string
   */
  protected function createDefaultBltSiteYml($default_site_dir) {
    if (!file_exists($default_site_dir . "/blt.yml")) {
      $initial_perms = fileperms($default_site_dir);
      chmod($default_site_dir, 0777);
      // Move project.local.hostname from blt.yml to
      // sites/default/blt.yml.
      $default_site_yml = [];
      $default_site_yml['project']['local']['hostname'] = $this->getConfigValue('project.local.hostname');
      $default_site_yml['project']['local']['protocol'] = $this->getConfigValue('project.local.protocol');
      $default_site_yml['project']['machine_name'] = $this->getConfigValue('project.machine_name');
      $default_site_yml['drush']['aliases']['local'] = $this->getConfigValue('drush.aliases.local');
      $default_site_yml['drush']['aliases']['remote'] = $this->getConfigValue('drush.aliases.remote');
      YamlMunge::writeFile($default_site_dir . "/blt.yml",
        $default_site_yml);
      $project_yml = YamlMunge::parseFile($this->getConfigValue('blt.config-files.project'));
      unset($project_yml['project']['local']['hostname']);
      unset($project_yml['project']['local']['protocol']);
      unset($project_yml['project']['machine_name']);
      unset($project_yml['drush']['aliases']['local']);
      unset($project_yml['drush']['aliases']['remote']);
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
    $url,
    $remote_alias
  ) {
    $site_yml_filename = $new_site_dir . '/blt.yml';
    $site_yml['project']['machine_name'] = $site_name;
    $site_yml['project']['human_name'] = $site_name;
    $site_yml['project']['local']['protocol'] = $url['scheme'];
    $site_yml['project']['local']['hostname'] = $url['host'];
    $site_yml['drush']['aliases']['local'] = $site_name . ".local";
    $site_yml['drush']['aliases']['remote'] = $remote_alias;
    YamlMunge::mergeArrayIntoFile($site_yml, $site_yml_filename);
  }

  /**
   * @param $default_site_dir
   * @param $new_site_dir
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function createNewSiteDir($default_site_dir, $new_site_dir) {
    $result = $this->taskCopyDir([
      $default_site_dir => $new_site_dir,
    ])
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
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
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
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
    if (empty($options['site-dir'])) {
      $site_name = $this->askRequired("Site machine name");
    }
    else {
      $site_name = $options['site-dir'];
    }
    return $site_name;
  }

  /**
   * @param $options
   *
   * @return string
   */
  protected function getNewSiteRemoteAlias($site_name, $options) {
    if (empty($options['remote-alias'])) {
      $default = $site_name . '.local';
      $alias = $this->askDefault("Default remote drush alias", $default);
    }
    else {
      $alias = $options['remote-alias'];
    }
    return $alias;
  }

  /**
   * @param $site_name
   */
  protected function createSiteDrushAlias($site_name) {
    $aliases = [
      'local' => [
        'uri' => $site_name,
      ],
    ];
    if ($this->getInspector()->isDrupalVmConfigPresent()) {
      $this->defaultDrupalVmDrushAliasesFile = $this->getConfigValue('blt.root') . '/scripts/drupal-vm/drupal-vm.site.yml';
      $new_aliases = Expander::parse(file_get_contents($this->defaultDrupalVmDrushAliasesFile), $this->getConfig()->export());
      $aliases = array_merge($new_aliases, $aliases);
    }

    $filename = $this->getConfigValue('drush.alias-dir') . "/$site_name.site.yml";
    YamlMunge::mergeArrayIntoFile($aliases, $filename);
  }

}
