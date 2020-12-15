<?php

namespace Acquia\Blt\Robo\Commands\Recipes;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\YamlMunge;
use Acquia\Blt\Robo\Common\YamlWriter;
use Acquia\Blt\Robo\Exceptions\BltException;
use Grasmash\YamlExpander\Expander;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Defines commands in the "recipes:multisite:init" namespace.
 */
class MultisiteCommand extends BltTasks {

  /**
   * Generates a new multisite.
   *
   * @param array $options
   *   Options.
   *
   * @command recipes:multisite:init
   *
   * @aliases rmi multisite
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function generate(array $options = [
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

    $domain = $this->getNewSiteDomain($options, $site_name);
    $url = parse_url($domain);
    // @todo Validate uri, ensure includes scheme.
    $newDBSettings = $this->setLocalDbConfig($site_name);
    if ($this->getInspector()->isDrupalVmConfigPresent()) {
      $this->configureDrupalVm($url, $newDBSettings);
    }
    $default_site_dir = $this->getConfigValue('docroot') . '/sites/default';
    $this->createDefaultBltSiteYml($default_site_dir);
    $this->createSiteDrushAlias('default');
    $this->createNewSiteDir($default_site_dir, $new_site_dir);

    $remote_alias = $this->getNewSiteAlias($site_name, $options, 'remote');
    $local_alias = $this->getNewSiteAlias($site_name, $options, 'local');
    $this->createNewBltSiteYml($new_site_dir, $site_name, $url, $local_alias, $remote_alias, $newDBSettings);
    $this->createNewSiteConfigDir($site_name);
    $this->createSiteDrushAlias($site_name, $domain);
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
   * @param array $newDBSettings
   *   An array of database configuration options or empty array.
   */
  protected function configureDrupalVm(array $url, array $newDBSettings) {
    $configure_vm = $this->confirm("Would you like to generate new virtual host entry and database for this site inside Drupal VM?", TRUE);
    if ($configure_vm) {
      $yamlWriter = new YamlWriter($this->getConfigValue('vm.config'));
      $vm_config = $yamlWriter->getContents();
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
          'priv' => $newDBSettings['database'] . '%.*:ALL',
        ];
      }

      $yamlWriter->write($vm_config);
    }
  }

  /**
   * Prompts for and sets config for new database.
   *
   * @param string $site_name
   *   Site name.
   *
   * @return array
   *   Empty array if user did not want to configure local db. Populated array
   *   otherwise.
   */
  protected function setLocalDbConfig($site_name) {
    $config_local_db = $this->confirm("Would you like to configure the local database credentials?");
    $db = [];

    if ($config_local_db) {
      $default_db = $this->getConfigValue('drupal.db');
      $db['database'] = $this->askDefault("Local database name",
        $site_name);
      $db['username'] = $this->askDefault("Local database user",
        $site_name);
      $db['password'] = $this->askDefault("Local database password",
        $site_name);
      $db['host'] = $this->askDefault("Local database host",
        $default_db['host']);
      $db['port'] = $this->askDefault("Local database port",
        $default_db['port']);
      $this->getConfig()->set('drupal.db', $db);
    }

    return $db;
  }

  /**
   * Create yaml file.
   *
   * @param string $default_site_dir
   *   Default site dir.
   *
   * @return string
   *   Site dir.
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
   * Create new site.yml.
   *
   * @param string $new_site_dir
   *   New site dir.
   * @param string $site_name
   *   Site name.
   * @param string $url
   *   Site url.
   * @param string $local_alias
   *   Local alias.
   * @param string $remote_alias
   *   Remote alias.
   * @param array $newDbSettings
   *   New db settings.
   */
  protected function createNewBltSiteYml(
    $new_site_dir,
    $site_name,
    $url,
    $local_alias,
    $remote_alias,
    array $newDbSettings
  ) {
    $site_yml_filename = $new_site_dir . '/blt.yml';
    $site_yml['project']['machine_name'] = $site_name;
    $site_yml['project']['human_name'] = $site_name;
    $site_yml['project']['local']['protocol'] = $url['scheme'];
    $site_yml['project']['local']['hostname'] = $url['host'];
    $site_yml['drush']['aliases']['local'] = $local_alias;
    $site_yml['drush']['aliases']['remote'] = $remote_alias;
    $site_yml['drupal']['db'] = $newDbSettings;
    YamlMunge::mergeArrayIntoFile($site_yml, $site_yml_filename);
  }

  /**
   * Create new site dir.
   *
   * @param string $default_site_dir
   *   Default site dir.
   * @param string $new_site_dir
   *   New site dir.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function createNewSiteDir($default_site_dir, $new_site_dir) {
    $result = $this->taskCopyDir([
      $default_site_dir => $new_site_dir,
    ])
      ->exclude(['local.settings.php', 'files'])
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to create $new_site_dir.");
    }
  }

  /**
   * Create new config dir.
   *
   * @param string $site_name
   *   Site name.
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

  /**
   * Reset config.
   */
  protected function resetMultisiteConfig() {
    /** @var \Acquia\Blt\Robo\Config\DefaultConfig $config */
    $config = $this->getConfig();
    $config->set('multisites', []);
    $config->populateHelperConfig();
  }

  /**
   * Get new domain.
   *
   * @param array $options
   *   Options.
   * @param string $site_name
   *   Site name.
   *
   * @return string
   *   Domain.
   */
  protected function getNewSiteDomain(array $options, $site_name) {
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
   * Get new site name.
   *
   * @param array $options
   *   Options.
   *
   * @return string
   *   Site name.
   */
  protected function getNewSiteName(array $options) {
    if (empty($options['site-dir'])) {
      $site_name = $this->askRequired("Site machine name (e.g. 'example')");
    }
    else {
      $site_name = $options['site-dir'];
    }
    return $site_name;
  }

  /**
   * Get alias.
   *
   * @param string $site_name
   *   Site name.
   * @param array $options
   *   Options.
   * @param string $dest
   *   Destination.
   *
   * @return string
   *   Alias.
   */
  protected function getNewSiteAlias($site_name, array $options, $dest) {
    $option = $dest . '-alias';
    if (!empty($options[$option])) {
      return $options[$option];
    }
    else {
      $default = $site_name . '.' . $dest;
      return $this->askDefault("Default $dest drush alias", $default);
    }
  }

  /**
   * Create alias.
   *
   * @param string $site_name
   *   Site name.
   * @param string $site_url
   *   Site URL (optional). Defaults to $site_name.
   */
  protected function createSiteDrushAlias($site_name, $site_url = '') {
    $aliases = [
      'local' => [
        'root' => '${env.cwd}/docroot',
      ],
    ];
    if ($site_url) {
      $aliases['local']['uri'] = $site_url;
    }
    $defaultDrupalVmDrushAliasesFile = $this->getConfigValue('blt.root') . '/scripts/drupal-vm/drupal-vm.site.yml';
    if ($this->getInspector()->isDrupalVmConfigPresent() && file_exists($defaultDrupalVmDrushAliasesFile)) {
      $aliases = Expander::parse(file_get_contents($defaultDrupalVmDrushAliasesFile), $this->getConfig()->export());
    }

    $filename = $this->getConfigValue('drush.alias-dir') . "/$site_name.site.yml";
    YamlMunge::mergeArrayIntoFile($aliases, $filename);
  }

}
