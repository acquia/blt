<?php

/**
 * @file
 * Defines class that powers `drush blt-doctor` command.
 */

namespace Acquia\Blt\Drush\Command;

use Dflydev\DotAccessData\Data;
use Symfony\Component\Yaml\Yaml;
use Drupal\Core\Installer\Exception\AlreadyInstalledException;

class BltDoctor {

  /** @var string */
  protected $localSettingsPath;
  /** @var array */
  protected $statusTable = [];
  /** @var bool */
  protected $ciEnabled = FALSE;
  /** @var array */
  protected $composerJson = [];
  /** @var bool */
  protected $drupalVmEnabled = FALSE;
  /** @var array */
  protected $drupalVmConfig = [];
  /** @var bool */
  protected $devDesktopEnabled = FALSE;
  /** @var array */
  protected $config = [];
  /** @var array */
  protected $drushAliases = [];
  /** @var bool */
  protected $passed = TRUE;

  /**
   * BoltDoctor constructor.
   */
  public function __construct() {
    $this->setStatusTable();

    if (!$this->checkDocrootExists()) {
      return FALSE;
    }

    $this->docroot = $this->statusTable['root'];
    $this->repoRoot = $this->statusTable['root'] . '/..';

    if (!$this->coreExists()) {
      return FALSE;
    }

    $this->siteRoot = $this->docroot . '/' . $this->statusTable['site'];
    $this->uri = $this->getUri();
    $this->localSettingsPath = $this->siteRoot . '/settings/' . 'local.settings.php';
    $this->localDrushRcPath = $this->siteRoot . '/local.drushrc.php';
    $this->setProjectConfig();
    $this->setCiEnabled();
    $this->setStacksEnabled();
    $this->setComposerJson();
  }

  /**
   * Sets $this->statusTable using drush internals.
   *
   * @return array
   */
  public function setStatusTable() {
    $status_table = drush_core_status();
    $status_table['php-mysql'] = ini_get('pdo_mysql.default_socket');
    $this->statusTable = $status_table;

    return $status_table;
  }

  /**
   * Sets $this->config from project.yml.
   *
   * @return array|bool|mixed
   */
  protected function setProjectConfig() {
    $filepath = $this->repoRoot . '/project.yml';
    if (!file_exists($filepath)) {
      $this->logError("project.yml is missing from the repository root directory!");

      return [];
    }

    $this->config = Yaml::parse(file_get_contents($filepath));

    $filepath = $this->repoRoot . '/project.local.yml';
    if (file_exists($filepath)) {
      $this->config = array_replace_recursive($this->config, Yaml::parse(file_get_contents($filepath)));
    }

    return $this->config;
  }

  /**
   * Sets $this->composerJson using root composer.json file.
   *
   * @return array
   */
  protected function setComposerJson() {
    if (file_exists($this->repoRoot . '/composer.json')) {
      $composer_json = json_decode(file_get_contents($this->repoRoot . '/composer.json'), TRUE);
      $this->composerJson = $composer_json;

      return $composer_json;
    }

    return [];
  }

  /**
   * Gets site URI from drush status table.
   *
   * @return mixed|null
   */
  public function getUri() {
    if (!empty($this->statusTable['uri'])) {
      return $this->statusTable['uri'];
    }
    return NULL;
  }

  /**
   * Sets which local *AMP stacks are initialized.
   */
  protected function setStacksEnabled() {
    $file_contents = file_get_contents($this->docroot . '/sites/default/settings.php');
    if (strstr($file_contents, 'DDSETTINGS')) {
      $this->devDesktopEnabled = TRUE;
    }

    if (file_exists($this->repoRoot . '/Vagrantfile')
      && file_exists($this->repoRoot . '/project.local.yml')
      && $this->config['drush']['aliases']['local'] != 'self') {
      $this->drupalVmEnabled = TRUE;
    }
  }

  /**
   * Sets which CI solutions are initialized.
   */
  protected function setCiEnabled() {
    if (file_exists($this->repoRoot . '/.travis.yml')) {
      $this->travisCiEnabled = TRUE;
      $this->ciEnabled = TRUE;
    }
    if (file_exists($this->repoRoot . '/acquia-pipelines.yml') || file_exists($this->repoRoot . '/acquia-pipelines.yaml')) {
      $this->pipelinesEnabled = TRUE;
      $this->ciEnabled = TRUE;
    }
  }

  /**
   * Performs all checks.
   */
  public function checkAll() {
    $this->logErrorDetail();

    $this->checkCoreExists();
    if (!$this->coreExists()) {
      return FALSE;
    }

    $this->checkSettingsFile();
    $this->checkLocalSettingsFile();
    $this->checkLocalDrushFile();

    if ($this->localDrushFileExists()) {
      $this->checkUriResponse();
      $this->checkHttps();
    }

    $this->checkFileSystem();
    $this->checkDbConnection();
    $this->checkDrupalBootstrapped();
    $this->checkDrupalInstalled();
    $this->checkCachingConfig();
    $this->checkNvmExists();
    $this->checkDevDesktopConfig();
    $this->checkCiConfig();
    $this->checkComposerConfig();
    $this->checkBehatConfig();
    $this->checkProjectYml();
    $this->checkAcsfConfig();
    $this->checkDrushAliases();
    $this->checkDrupalVmConfig();

    //$this->checkDatabaseUpdates();
    // @todo Check error_level.
    // @todo Check if theme dependencies have been built.
    // @todo Check that if drupal/acsf is in composer.json, acsf is initialized.
    // @todo If using lightning, check lightning.extend.yml exists, check for $config['profile'] = 'lighting';
    // @todo Check is PhantomJS bin matches OS.
    // @todo Check global drush version.

    if ($this->passed) {
      drush_log("Everything looks good enough!\n", "success");
    }
  }

  protected function logError($message = '') {
    $this->passed = FALSE;
    drush_set_error($message);
  }

  protected function logErrorDetail($message = '') {
    drush_print($message, 2);
  }

  protected function logNewLine() {
    drush_print();
  }

  protected function checkDocrootExists() {
    if (empty($this->statusTable['root'])) {
      $this->logError('Drush could not find the docroot!');

      return FALSE;
    }

    return TRUE;
  }

  /**
   * Checks that local settings file exists.
   */
  protected function checkLocalSettingsFile() {
    if (!file_exists($this->localSettingsPath)) {
      $this->logError("Could not find local settings file!");
      $this->logErrorDetail("Your local settings file should exist at $this->localSettingsPath");
      $this->logErrorDetail();
    }
    else {
      // drush_log("Found your local settings file at:", 'notice');
      // $this->logErrorDetail($this->localSettingsPath);
      // $this->logErrorDetail();
    }
  }

  /**
   * Checks active settings.php file.
   */
  protected function checkSettingsFile() {
    if (!file_exists($this->statusTable['drupal-settings-file'])) {
      $this->logError("Could not find settings.php for this site!");
    }

    $settings_file_path = $this->docroot . '/' . $this->statusTable['drupal-settings-file'];
    $settings_file_contents = file_get_contents($settings_file_path);
    if (strstr($settings_file_contents, '/../vendor/acquia/blt/settings/blt.settings.php')) {
      // drush_log("BLT settings are included in settings file:", 'notice');
      // $this->logErrorDetail($settings_file_path);
    }
    else {
      $this->logError("BLT settings are not included in your settings file.");
      $this->logErrorDetail();
    }
    if (strstr($settings_file_contents, '/sites/default/settings/blt.settings.php')) {
      $this->logError("Your settings file contains a deprecated statement for including BLT settings.");
      $this->logErrorDetail("Please remove the line containing \"/sites/default/settings/blt.settings.php\" in $settings_file_path.");
      $this->logErrorDetail();
    }
  }

  /**
   * Indicates whether a local.drushrc.php file exists.
   *
   * @return bool
   */
  protected function localDrushFileExists() {
    return file_exists($this->localDrushRcPath);
  }

  /**
   * Checks for local.drushrc.php file and prints messaging to screen.
   */
  protected function checkLocalDrushFile() {
    if (!$this->localDrushFileExists()) {
      $this->logError("Local drushrc file does not exist.");
      $this->logErrorDetail("Create $this->localDrushRcPath.");
      $this->logErrorDetail("Run `blt setup:drush:settings` to generate it automatically, or run `blt setup` to run the entire setup process.");
      $this->logErrorDetail();
    }
    else {
      // drush_log("Found your local drush settings file at:", 'notice');
      // $this->logErrorDetail($this->localDrushRcPath);
      // $this->logErrorDetail();
    }
  }

  /**
   * Checks that configured URI responds to requests.
   */
  protected function checkUriResponse() {
    if (!$this->uri) {
      $this->logError("Site URI is not set");
      $this->logErrorDetail("Is \$options['uri'] set correctly in $this->localDrushRcPath?");

      return FALSE;
    }
    else {
      drush_log("\$options['uri'] is set correctly.", 'notice');
    }

    $site_available = drush_shell_exec("curl -I --insecure %s", $this->uri);
    if (!$site_available) {
      $this->logError("Did not get a response from $this->uri");
      $this->logErrorDetail("Is your *AMP stack running?");
      $this->logErrorDetail("Is your web server configured to serve this URI from $this->docroot?");
      $this->logErrorDetail("Is \$options['uri'] set correctly in $this->localDrushRcPath?");
      $this->logErrorDetail();
      $this->logErrorDetail("To generate settings files and install Drupal, run `blt local:setup`");
      $this->logErrorDetail();
    }
    else {
      // drush_log("Received a response from site:", 'notice');
      // $this->logErrorDetail($this->statusTable['uri']);
      // $this->logErrorDetail();
    }
  }

  /**
   * Checks that SSL cert is valid for configured URI.
   */
  protected function checkHttps() {
    if (strstr($this->statusTable['uri'], 'https')) {
      if (!drush_shell_exec('curl -cacert %s', $this->statusTable['uri'])) {
        $this->logError('The SSL certificate for your local site appears to be invalid:');
        $this->logErrorDetail($this->statusTable['uri']);
        $this->logErrorDetail();
      }
      else {
        drush_log("The SSL certificate for your local site appears valid.", 'notice');
      }
    }
  }

  /**
   * Checks that drush is able to connect to database.
   */
  protected function checkDbConnection() {

    $connection = @mysqli_connect($this->statusTable['db-hostname'], $this->statusTable['db-username'], $this->statusTable['db-password'], $this->statusTable['db-database'], $this->statusTable['db-port']);
    if ($connection) {
      mysqli_close($connection);
      drush_log('Connected to database.', 'notice');

      return TRUE;
    }

    $this->logError('Could not connect to MySQL database.');
    $this->logErrorDetail("Is your *AMP stack running?");
    $this->logErrorDetail('Are your database credentials correct?');
    drush_blt_print_status_rows($this->statusTable, array(
      'db-driver',
      'db-hostname',
      'db-username',
      'db-password',
      'db-name',
      'db-port',
    ));

    if ($this->statusTable['db-driver'] == 'mysql') {
      $this->logErrorDetail("To verify your mysql credentials, run `mysql -u {$this->statusTable['db-username']} -h {$this->statusTable['db-hostname']} -p{$this->statusTable['db-password']} -P {$this->statusTable['db-port']}`");
      $this->logErrorDetail();
    }

    $this->logErrorDetail('Are you using the correct PHP binary?');
    $this->logErrorDetail('Is PHP using the correct MySQL socket?');
    drush_blt_print_status_rows($this->statusTable, array(
      'php-os',
      'php-bin',
      'php-conf',
      'php-mysql'
    ));
    $this->logErrorDetail("To verify, run `drush sqlc`");
    $this->logErrorDetail();

    $this->logErrorDetail('Are you using the correct site and settings.php file?');
    drush_blt_print_status_rows($this->statusTable, array(
      'site',
      'drupal-settings-file',
    ));
    $this->logErrorDetail();
  }

  /**
   * Checks if database updates are pending.
   */
  protected function checkDatabaseUpdates() {
    drush_include_engine('drupal', 'update');
    $pending = update_main();

    if ($pending) {
      $this->logError("There are pending database updates");
      $this->logErrorDetail("Run `drush updb` to execute the updates.");
    }
    else {
      drush_log("There are no pending database updates.", 'notice');
    }
    $this->logErrorDetail();
  }

  /**
   * Checks that NVM exists.
   *
   * Note that this does not check if `nvm use` has been invoked for the correct
   * node version.
   */
  protected function checkNvmExists() {
    $home = getenv("HOME");
    if (!file_exists("$home/.nvm")) {
      drush_log('NVM does not exist.', 'warning');
      $this->logErrorDetail('It is recommended that you use NVM to manage multiple versions of NodeJS on one machine.');
      $this->logErrorDetail('Instructions for installing NVM can be found at:');
      $this->logErrorDetail('https://github.com/creationix/nvm#installation', 4);
      $this->logErrorDetail();
    }
    else {
      drush_log("NVM exists.", 'notice');
    }
  }

  /**
   * Indicates whether Drupal core files exist in the docroot.
   *
   * @return bool
   */
  protected function coreExists() {
    return file_exists($this->docroot . '/core/includes/install.core.inc');
  }

  /**
   * Checks that Drupal core files exist in the docroot.
   */
  protected function checkCoreExists() {
    if (!$this->coreExists()) {
      $this->logError("Drupal core is missing!");
      $this->logErrorDetail("Looked for docroot in {$this->docroot}.");
      $this->logErrorDetail("Check and re-install your composer dependencies.");
      $this->logErrorDetail();
    }
    else {
      drush_log("Drupal core exists.", 'notice');
    }
  }

  /**
   * Checks that drush is able to bootstrap Drupal Core.
   *
   * This is only possible if Drupal is installed.
   */
  protected function checkDrupalBootstrapped() {
    if (empty($this->statusTable['bootstrap']) || $this->statusTable['bootstrap'] != 'Successful') {
      drush_log('Could not bootstrap Drupal via drush without alias.', 'warning');
    }
    else {
      drush_log('Bootstrapped Drupal via drush.', 'notice');
    }
  }

  /**
   * Checks that Drupal is installed.
   */
  protected function checkDrupalInstalled() {
    require $this->docroot . '/core/includes/install.core.inc';
    require $this->docroot . '/core/includes/install.inc';
    require $this->docroot . '/core/modules/system/system.install';
    try {
      install_verify_database_ready();
      drush_log("Drupal is not installed.", 'warning');
      $this->logErrorDetail('Run `blt local:setup` to install Drupal locally.');
    }
    catch (AlreadyInstalledException $e) {
      drush_log("Drupal is installed.", 'notice');
    }
    catch (\Exception $e) {

    }
  }

  /**
   * Checks that configured file system paths exist and are writable.
   */
  protected function checkFileSystem() {
    $paths = [
      '%files' => 'Public files directory',
      '%private' => 'Private files directory',
      '%temp' => 'Temporary files directory',
    ];

    foreach ($paths as $key => $title) {
      if (empty($this->statusTable['%paths'][$key])) {
        $this->logError("$title is not set.");

        continue;
      }

      $path = $this->statusTable['%paths'][$key];
      if (substr($path, 0, 1) == '/') {
        $full_path = $path;
      }
      else {
        $full_path = $this->docroot . "/$path";
      }

      if (file_exists($full_path)) {
        drush_log("$title exists.", 'notice');

        if (is_writable($full_path)) {
          drush_log("$title is writable.", 'notice');
        }
        else {
          $this->logError("$title is not writable.");
          $this->logErrorDetail("Change the permissions on $full_path.");
          $this->logErrorDetail("Run `chmod 750 $full_path`.");
          $this->logErrorDetail();
        }
      }
      else {
        $this->logError("$title does not exist.");
        $this->logErrorDetail("Create $full_path.");
        if (in_array($key, ['%files', '%private'])) {
          $this->logErrorDetail("Installing Drupal will create this directory for you.");
          $this->logErrorDetail("Run `blt setup:drupal:install` to install Drupal, or run `blt setup` to run the entire setup process.");
          $this->logErrorDetail("Otherwise, run `mkdir -p $full_path`.");
          $this->logErrorDetail();
        }
      }
    }
  }

  /**
   * Checks that Dev Desktop is configured correctly.
   */
  protected function checkDevDesktopConfig() {
    if ($this->devDesktopEnabled) {
      if (empty($ENV['DEVDESKTOP_DRUPAL_SETTINGS_DIR'])) {
        $this->logError("DevDesktop usage is enabled, but \$DEVDESKTOP_DRUPAL_SETTINGS_DIR is not set in your environmental variables.");
        $this->logErrorDetail("Add `export DEVDESKTOP_DRUPAL_SETTINGS_DIR=\"\$HOME/.acquia/DevDesktop/DrupalSettings\"` to ~/.bash_profile or equivalent for your system.`");
        $this->logErrorDetail();
      }
      elseif (strstr($ENV['DEVDESKTOP_DRUPAL_SETTINGS_DIR'], '~')) {
        $this->logError("\$DEVDESKTOP_DRUPAL_SETTINGS_DIR contains a '~'. This does not always expand to your home directory.");
        $this->logErrorDetail("Add `export DEVDESKTOP_DRUPAL_SETTINGS_DIR=\"\$HOME/.acquia/DevDesktop/DrupalSettings\"` to ~/.bash_profile or equivalent for your system.`");
        $this->logErrorDetail();
      }
      else {
        drush_log("\$DEVDESKTOP_DRUPAL_SETTINGS_DIR is set.", 'notice');
      }

      $variables_order = ini_get('variables_order');
      $php_ini_file = php_ini_loaded_file();
      if (!strstr($variables_order, 'E')) {
        $this->logError("DevDesktop usage is enabled, but variables_order does support environmental variables.");
        $this->logErrorDetail("Define variables_order = \"EGPCS\" in $php_ini_file");
        $this->logErrorDetail();
      }
      else {
        drush_log("variables_order allows environment variables in php.ini.", 'notice');
      }
    }
  }

  /**
   * Checks Drupal VM configuration.
   */
  protected function checkDrupalVmConfig() {
    if ($this->drupalVmEnabled) {
      $passed = TRUE;
      if (!file_exists($this->repoRoot . '/box/config.yml')) {
        $this->logError("You have DrupalVM initialized, but box/config.yml is missing.");
        $this->logErrorDetail();
        $passed = FALSE;
      }
      else {
        $this->setDrupalVmConfig();
      }
      if ($this->drushAliasesFileExists()) {
        $local_alias_id = $this->config['drush']['aliases']['local'];
        if ($local_alias_id !== 'self') {
          if (empty($this->drushAliases[$local_alias_id])) {
            $this->logError("The drush alias assigned to drush.aliases.local does not exist in your drush aliases file.");
            $this->logErrorDetail("drush.aliases.local is set to @$local_alias_id");
            $this->logErrorDetail("Looked in " . $this->repoRoot . '/drush/site-aliases/aliases.drushrc.php');
            $this->logErrorDetail();
            $passed = FALSE;
          }
          else {
            $local_alias = $this->drushAliases[$local_alias_id];
            if ($local_alias['remote-host'] != $this->drupalVmConfig['vagrant_hostname']) {
              $this->logError("remote-host for @$local_alias_id drush alias does not match vagrant_hostname for DrupalVM.");
              $this->logErrorDetail("remote-host is set to {$local_alias['remote-host']} for @$local_alias_id");
              $this->logErrorDetail("vagrant_hostname is set to {$this->drupalVmConfig['vagrant_hostname']} for DrupalVM.");
              $this->logErrorDetail("{$local_alias['remote-host']} != {$this->drupalVmConfig['vagrant_hostname']}");
              $this->logErrorDetail();
              $passed = FALSE;
            }
            $parsed_uri = parse_url($local_alias['uri']);
            if ($parsed_uri['host'] != $this->drupalVmConfig['vagrant_hostname']) {
              $this->logError("uri for @$local_alias_id drush alias does not match vagrant_hostname for DrupalVM.");
              $this->logErrorDetail("uri is set to {$local_alias['uri']} for @$local_alias_id");
              $this->logErrorDetail("vagrant_hostname is set to {$this->drupalVmConfig['vagrant_hostname']} for DrupalVM.");
              $this->logErrorDetail("{$local_alias['uri']} != {$this->drupalVmConfig['vagrant_hostname']}");
              $this->logErrorDetail();
              $passed = FALSE;
            }
            $expected_root = $this->drupalVmConfig['drupal_composer_install_dir'] . '/docroot';
            if ($local_alias['root'] != $expected_root) {
              $this->logError("root for @$local_alias_id drush alias does not match docroot for DrupalVM.");
              $this->logErrorDetail("root is set to {$local_alias['root']} for @$local_alias_id");
              $this->logErrorDetail("docroot is set to $expected_root for DrupalVM.");
              $this->logErrorDetail("{$local_alias['root']} != $expected_root");
              $this->logErrorDetail();
              $passed = FALSE;
            }
          }
        }
      }
    }
    if ($passed) {
      drush_log("Drupal VM is configured correctly.", 'notice');
    }
  }

  protected function setDrupalVmConfig() {
    $this->drupalVmConfig  = Yaml::parse(file_get_contents($this->repoRoot . '/box/config.yml'));

    return $this->drupalVmConfig;
  }

  protected function drushAliasesFileExists() {
    return file_exists($this->repoRoot . '/drush/site-aliases/aliases.drushrc.php');
  }

  protected function checkDrushAliases() {
    $file_path = $this->repoRoot . '/drush/site-aliases/aliases.drushrc.php';
    if (!$this->drushAliasesFileExists()) {
      $this->logError("drush alias file does not exist!");
      $this->logErrorDetail("Create $file_path");
      $this->logErrorDetail();
    }
    else {
      require $file_path;
      $this->drushAliases = $aliases;
    }
  }

  /**
   * Checks Behat configuration in local.yml.
   *
   * @return bool
   */
  protected function checkBehatConfig() {
    if (!file_exists($this->repoRoot . '/tests/behat/local.yml')) {
      $this->logError("tests/behat/local.yml is missing!");
      $this->logErrorDetail("Run `blt setup:behat` to generate it from example.local.yml, or run `blt setup` to run the entire setup process.");
      $this->logErrorDetail();

      return FALSE;
    }
    else {
      drush_log("Behat local settings file exists.", 'notice');
    }

    $this->behatDefaultLocalConfig = Yaml::parse(file_get_contents($this->repoRoot . '/tests/behat/local.yml'));
    if ($this->drupalVmEnabled) {
      $behat_drupal_root = $this->behatDefaultLocalConfig['local']['extensions']['Drupal\DrupalExtension']['drupal']['drupal_root'];
      if (!strstr($behat_drupal_root, '/var/www/')) {
        $this->logError("You have DrupalVM initialized, but drupal_root in tests/behat/local.yml does not reference the DrupalVM docroot.");
        $this->logErrorDetail("Behat drupal_root is $behat_drupal_root.");
        $this->logErrorDetail();
      }
      else {
        drush_log("Behat drupal_root is set correctly for Drupal VM.", 'notice');
      }
    }

    $behat_base_url = $this->behatDefaultLocalConfig['local']['extensions']['Behat\MinkExtension']['base_url'];
    if ($behat_base_url != $this->getUri()) {
      $this->logError("base_url in tests/behat/local.yml does not match the site URI.");
      $this->logErrorDetail("Behat base_url is set to \"$behat_base_url\".");
      $this->logErrorDetail("Drush site URI is set to {$this->getUri()}.");
      $this->logErrorDetail();
    }
    else {
      drush_log("Behat base_url matches drush URI.", 'notice');
    }
  }


  /**
   * Checks TravisCI configuration.
   */
  protected function checkTravisCiConfig() {
    // @todo Check that known hosts is set.
    // @todo Check that deployment is enabled.
  }

  /**
   * Check that general CI configuration is set correctly.
   */
  protected function checkCiConfig() {
    if ($this->ciEnabled) {
      if (empty($this->config['git']['remotes'])) {
        $this->logError("Git repositories are not defined in project.yml.");
        $this->logErrorDetail("Add values for git.remotes to project.yml to enabled automated deployment.");
        $this->logErrorDetail();
      }
      else {
        drush_log("Git remotes are set in project.yml.", 'notice');
      }
    }
  }

  /**
   * Checks that composer.json is configured correctly.
   */
  protected function checkComposerConfig() {
    if (!empty($this->composerJson['require-dev']['acquia/blt'])) {
      $this->logError("acquia/blt is defined as a development dependency in composer.json");
      $this->logErrorDetail("Move acquia/blt out of the require-dev object and into the require object in composer.json.");
      $this->logErrorDetail("This is necessary for BLT settings files to be available at runtime in production.");
      $this->logErrorDetail();
    }
    else {
      drush_log("acquia/blt is in composer.json's require object.", 'notice');
    }

    $prestissimo_intalled = drush_shell_exec("composer global show | grep hirak/prestissimo");
    if (!$prestissimo_intalled) {
      drush_log("hirak/prestissimo plugin for composer is not installed.", 'warning');
      $this->logErrorDetail("Run `composer global require hirak/prestissimo:^0.3` to install it.");
      $this->logErrorDetail("This will improve composer install/update performance by parallelizing the download of dependency information.");
      $this->logErrorDetail();
    }
    else {
      drush_log("hirak/prestissimo plugin for composer is installed.", 'notice');
    }
  }

  protected function checkAcsfConfig() {
    $file_path = $this->repoRoot . '/factory-hooks/pre-settings-php/includes.php';
    if (file_exists($file_path)) {
      $file_contents = file_get_contents($file_path);
      if (!strstr($file_contents, '/../vendor/acquia/blt/settings/blt.settings.php')) {
        $this->logError("BLT settings are not included in your pre-settings-php include.");
        $this->logErrorDetail("Add a require statement for \"/../vendor/acquia/blt/settings/blt.settings.php\" to $file_path");
        $this->logErrorDetail();
      }
      else {
        drush_log("BLT settings are included in your pre-settings-php include.", 'notice');
      }
    }
  }

  /**
   * Checks that caching is configured for local development.
   */
  protected function checkCachingConfig() {
    if (drush_bootstrap_max(DRUSH_BOOTSTRAP_DRUPAL_FULL)) {
      global $conf;

      if ($conf['cache']) {
        drush_log('Drupal cache is enabled. It is suggested that you disable this for local development.', 'warning');
      }
      else {
        drush_log('Drupal cache is disabled.', 'notice');
      }
      if ($conf['preprocess_css']) {
        drush_log('CSS preprocessing enabled. It is suggested that you disable this for local development.', 'warning');
      }
      else {
        drush_log('CSS preprocessing is disabled.', 'notice');
      }
      if ($conf['preprocess_js']) {
        drush_log('JS preprocessing is enabled. It is suggested that you disable this for local development.', 'warning');
      }
      else {
        drush_log('JS preprocessing is disabled.', 'notice');
      }
    }
  }

  /**
   * Check that the contributed modules directory exists.
   */
  protected function checkContribExists() {
    if (!file_exists($this->docroot . '/sites/all/modules/contrib')) {
      $this->logError("Contributed module dependencies are missing.");
      $this->logErrorDetail("Run `blt setup:build to build all contributed dependencies.");
      $this->logErrorDetail();
    }
    else {
      drush_log("Contributed module dependencies are present.", 'notice');
    }
  }

  /**
   * Checks that is configured correctly at a high level.
   */
  protected function checkProjectYml() {
    $deprecated_keys = [
      'project.hash_salt',
      'project.profile.contrib',
      'project.vendor',
      'project.description',
      'project.themes',
      'hosting',
    ];

    $config = new Data($this->config);
    $deprecated_keys_exist = FALSE;
    foreach ($deprecated_keys as $deprecated_key) {
      if ($config->get($deprecated_key)) {
        drush_log("The $deprecated_key key is deprecated. Please remove it from project.yml.", 'warning');
        $this->logErrorDetail();
        $deprecated_keys_exist = TRUE;
      }
    }

    if (!$deprecated_keys_exist) {
      drush_log("project.yml has no deprecated keys.", 'notice');
    }
  }
}
