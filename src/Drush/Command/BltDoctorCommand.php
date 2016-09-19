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
  /** @var bool */
  protected $devDesktopEnabled = FALSE;
  /** @var array */
  protected $config = [];

  /**
   * BoltDoctor constructor.
   */
  public function __construct() {
    $this->setStatusTable();
    $this->docroot = $this->statusTable['root'];
    $this->repoRoot = $this->statusTable['root'] . '/../';

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
      drush_set_error("project.yml is missing from the repository root directory!");

      return [];
    }

    $this->config = Yaml::parse(file_get_contents($filepath));

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

    if (file_exists($this->repoRoot . '/Vagrantfile')) {
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
    $this->checkCoreExists();
    if (!$this->coreExists()) {
      return FALSE;
    }

    $this->checkSettingsFile();
    $this->checkLocalSettingsFile();
    $this->checkLocalDrushFile();
    $this->checkUriResponse();
    $this->checkHttps();
    $this->checkFileSystem();
    $this->checkDbConnection();
    $this->checkDrupalBootstrapped();
    $this->checkDrupalInstalled();
    //$this->checkDatabaseUpdates();
    $this->checkCachingConfig();
    $this->checkNvmExists();
    $this->checkDevDesktopConfig();
    $this->checkCiConfig();
    $this->checkComposerConfig();
    $this->checkBehatConfig();
    $this->checkProjectYml();

    // @todo Check error_level.
    // @todo Check if theme dependencies have been built.
    // @todo Check that if drupal/acsf is in composer.json, acsf is initialized.
    // @todo If using lightning, check lightning.extend.yml exists, check for $config['profile'] = 'lighting';
    // @todo Check for existence of deprecated BLT files.
    // @todo Check is PhantomJS bin matches OS.
  }

  /**
   * Checks that local settings file exists.
   */
  protected function checkLocalSettingsFile() {
    if (!file_exists($this->localSettingsPath)) {
      drush_set_error("Could not find local settings file!");
      drush_print("Your local settings file should exist at $this->localSettingsPath", 2);
    }
    else {
      drush_log("Found your local settings file at:", 'success');
      drush_print($this->localSettingsPath, 2);
    }
    drush_print();
  }

  /**
   * Checks active settings.php file.
   */
  protected function checkSettingsFile() {
    if (!file_exists($this->statusTable['drupal-settings-file'])) {
      drush_set_error("Could not find settings.php for this site!");
    }

    $settings_file_path = $this->docroot . '/' . $this->statusTable['drupal-settings-file'];
    $settings_file_contents = file_get_contents($settings_file_path);
    if (strstr($settings_file_contents, '/../vendor/acquia/blt/settings/blt.settings.php')) {
      drush_log("BLT settings are included in settings file:", 'success');
      drush_print($settings_file_path, 2);
    }
    if (strstr($settings_file_contents, '/sites/default/settings/blt.settings.php')) {
      drush_set_error("Your settings file contains a deprecated statement for including BLT settings.");
      drush_print("Please remove the line containing \"/sites/default/settings/blt.settings.php\" in $settings_file_path.", 2);
    }
    drush_print();
  }

  /**
   * Checks local.drushrc.php file.
   */
  protected function checkLocalDrushFile() {
    if (!file_exists($this->localDrushRcPath)) {
      drush_set_error("$this->localDrushRcPath does not exist");
      drush_print("Run `blt setup:drush` to generate it automatically.", 2);
    }
    else {
      drush_log("Found your local drush settings file at:", 'success');
      drush_print($this->localDrushRcPath, 2);
    }
    drush_print();
  }

  /**
   * Checks that configured URI responds to requests.
   */
  protected function checkUriResponse() {
    if (!$this->uri) {
      drush_set_error("Site URI is not set");
      drush_print("Is \$options['uri'] set correctly in $this->localDrushRcPath?", 2);

      return FALSE;
    }

    $site_available = drush_shell_exec("curl -I --insecure %s", $this->uri);
    if (!$site_available) {
      drush_set_error("Did not get a response from $this->uri");
      drush_print("Is your *AMP stack running?", 2);
      drush_print("Is your web server configured to serve this URI from $this->docroot?", 2);
      drush_print("Is \$options['uri'] set correctly in $this->localDrushRcPath?", 2);
      drush_print();
      drush_print("To generate settings files and install Drupal, run `blt local:setup`", 2);
    }
    else {
      drush_log("Received a response from site:", 'success');
      drush_print($this->statusTable['uri'], 2);
    }
    drush_print();
  }

  /**
   * Checks that SSL cert is valid for configured URI.
   */
  protected function checkHttps() {
    if (strstr($this->statusTable['uri'], 'https')) {
      if (!drush_shell_exec('curl -cacert %s', $this->statusTable['uri'])) {
        drush_set_error('The SSL certificate for your local site appears to be invalid:');
        drush_print($this->statusTable['uri'], 2);
        drush_print();
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
      drush_log('Connected to database.', 'success');

      return TRUE;
    }

    drush_set_error('Could not connect to MySQL database.');
    drush_print("Is your *AMP stack running?", 2);
    drush_print('Are your database credentials correct?', 2);
    drush_blt_print_status_rows($this->statusTable, array(
      'db-driver',
      'db-hostname',
      'db-username',
      'db-password',
      'db-name',
      'db-port',
    ));

    if ($this->statusTable['db-driver'] == 'mysql') {
      drush_print("To verify your mysql credentials, run `mysql -u {$this->statusTable['db-username']} -h {$this->statusTable['db-hostname']} -p{$this->statusTable['db-password']} -P {$this->statusTable['db-port']}`", 2);
      drush_print();
    }

    drush_print('Are you using the correct PHP binary?', 2);
    drush_print('Is PHP using the correct MySQL socket?', 2);
    drush_blt_print_status_rows($this->statusTable, array(
      'php-os',
      'php-bin',
      'php-conf',
      'php-mysql'
    ));
    drush_print("To verify, run `drush sqlc`", 2);
    drush_print();

    drush_print('Are you using the correct site and settings.php file?');
    drush_blt_print_status_rows($this->statusTable, array(
      'site',
      'drupal-settings-file',
    ));

    drush_print();
  }

  /**
   * Checks if database updates are pending.
   */
  protected function checkDatabaseUpdates() {
    drush_include_engine('drupal', 'update');
    $pending = update_main();

    if ($pending) {
      drush_set_error("There are pending database updates");
      drush_print("Run `drush updb` to execute the updates.", 2);
    }
    else {
      drush_log("There are no pending database updates.", 'success');
    }
    drush_print();
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
      drush_log('NVM does not exist. Using NVM will help you manage multiple versions of NodeJS on one machine. Install using the following commands:', 'warning');
      drush_print('curl -o- https://raw.githubusercontent.com/creationix/nvm/v0.31.0/install.sh | bash', 2);
      drush_print('source ~/.bashrc', 2);
      drush_print('nvm install 0.12.7', 2);
      drush_print('nvm use 0.12.7', 2);
      drush_print();
    }
    else {
      drush_log("NVM exists.", 'success');
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
      drush_set_error("Drupal core is missing!");
      drush_print("Check and re-install your composer dependencies.", 2);
    }
    else {
      drush_log("Drupal core exists.", 'success');
    }
  }

  /**
   * Checks that drush is able to bootstrap Drupal Core.
   *
   * This is only possible if Drupal is installed.
   */
  protected function checkDrupalBootstrapped() {
    if (empty($this->statusTable['bootstrap']) || $this->statusTable['bootstrap'] != 'Successful') {
      drush_log('Could not bootstrap Drupal via drush.', 'warning');
    }
    else {
      drush_log('Bootstrapped Drupal via drush.', 'success');
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
      drush_print('Run `blt local:setup` to install Drupal locally.', 2);
    }
    catch (AlreadyInstalledException $e) {
      drush_log("Drupal is installed.", 'success');
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
      '%tmp' => 'Temporary files directory',
    ];

    foreach ($paths as $key => $title) {
      $path = $this->statusTable['%paths'][$key];
      if (substr($path, 0, 1) == '/') {
        $full_path = $path;
      }
      else {
        $full_path = $this->docroot . "/$path";
      }

      if (file_exists($full_path)) {
        drush_log("$title exists.", 'success');

        if (is_writable($full_path)) {
          drush_log("$title is writable.", 'success');
        }
        else {
          drush_set_error("$title is not writable.");
          drush_print("Change the permissions on $full_path", 2);
        }
      }
      else {
        drush_set_error("$title does not exist.");
        drush_print("Create $full_path", 2);
      }
    }
  }

  /**
   * Checks that Dev Desktop is configured correctly.
   */
  protected function checkDevDesktopConfig() {
    if ($this->devDesktopEnabled) {
      if (empty($ENV['DEVDESKTOP_DRUPAL_SETTINGS_DIR'])) {
        drush_set_error("DevDesktop usage is enabled, but \$DEVDESKTOP_DRUPAL_SETTINGS_DIR is not set in your environmental variables.");
        drush_print("Add `export DEVDESKTOP_DRUPAL_SETTINGS_DIR=\"\$HOME/.acquia/DevDesktop/DrupalSettings\"` to ~/.bash_profile or equivalent for your system.`", 2);
        drush_print();
      }
      elseif (strstr($ENV['DEVDESKTOP_DRUPAL_SETTINGS_DIR'], '~')) {
        drush_set_error("\$DEVDESKTOP_DRUPAL_SETTINGS_DIR contains a '~'. This does not always expand to your home directory.");
        drush_print("Add `export DEVDESKTOP_DRUPAL_SETTINGS_DIR=\"\$HOME/.acquia/DevDesktop/DrupalSettings\"` to ~/.bash_profile or equivalent for your system.`", 2);
        drush_print();
      }

      $variables_order = ini_get('variables_order');
      $php_ini_file = php_ini_loaded_file();
      if (!strstr($variables_order, 'E')) {
        drush_set_error("DevDesktop usage is enabled, but variables_order does support environmental variables.");
        drush_print("Define variables_order = \"EGPCS\" in $php_ini_file", 2);
        drush_print();
      }
    }
  }

  /**
   * Checks Drupal VM configuration.
   */
  protected function checkDrupalVmConfig() {
    if ($this->drupalVmEnabled) {
      // @todo Verify that box directory exists and contains config.
      // @todo Check config.
    }
  }

  /**
   * Checks Behat configuration in local.yml.
   *
   * @return bool
   */
  protected function checkBehatConfig() {
    if (!file_exists($this->repoRoot . '/tests/behat/local.yml')) {
      drush_set_error("tests/behat/local.yml is missing!");

      drush_print("Run `blt setup:behat` to generate it from example.local.yml.", 2);
      return FALSE;
    }

    $this->behatDefaultLocalConfig = Yaml::parse(file_get_contents($this->repoRoot . '/tests/behat/local.yml'));
    if ($this->drupalVmEnabled) {
      $behat_drupal_root = $this->behatDefaultLocalConfig['local']['extensions']['Drupal\DrupalExtension']['drupal']['drupal_root'];
      if (strstr($behat_drupal_root, '/var/www/')) {
        drush_set_error("You have DrupalVM initialized, but drupal_root in tests/behat/local.yml does not reference the DrupalVM docroot.");
      }
    }

    $behat_base_url = $this->behatDefaultLocalConfig['local']['extensions']['Behat\MinkExtension']['base_url'];
    if ($behat_base_url != $this->getUri()) {
      drush_set_error("base_url in tests/behat/local.yml does not match the site URI. It is set to \"$behat_base_url\".");
      drush_print("Set base_url to {$this->getUri()}", 2);
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
        drush_set_error("Git repositories are not defined in project.yml.");
        drush_print("Add values for git.remotes to project.yml to enabled automated deployment.", 2);
      }
    }
  }

  /**
   * Checks that composer.json is configured correctly.
   */
  protected function checkComposerConfig() {
    if (!empty($this->composerJson['require-dev']['acquia/blt'])) {
      drush_set_error("acquia/blt is defined as a development dependency in composer.json");
      drush_print("Move acquia/blt out of the require-dev object and into the require object in composer.json.", 2);
      drush_print("This is necessary for BLT settings files to be available at runtime in production.", 2);
    }

    $prestissimo_intalled = drush_shell_exec("composer global show | grep hirak/prestissimo");
    if (!$prestissimo_intalled) {
      drush_log("prestissimo plugin for composer is not installed.");
      drush_print("Run `composer global require hirak/prestissimo:^0.3` to install it.", 2);
      drush_print("This will improve composer install/update performance by parallelizing the download of dependency information.", 2);
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
        drush_log('Drupal cache is disabled.', 'success');
      }
      if ($conf['preprocess_css']) {
        drush_log('CSS preprocessing enabled. It is suggested that you disable this for local development.', 'warning');
      }
      else {
        drush_log('CSS preprocessing is disabled.', 'success');
      }
      if ($conf['preprocess_js']) {
        drush_log('JS preprocessing is enabled. It is suggested that you disable this for local development.', 'warning');
      }
      else {
        drush_log('JS preprocessing is disabled.', 'success');
      }
    }
  }

  /**
   * Check that the contributed modules directory exists.
   */
  protected function checkContribExists() {
    if (!file_exists($this->docroot . '/sites/all/modules/contrib')) {
      drush_set_error("Contributed module dependencies are missing.");
      drush_print("Run `blt setup:build to build all contributed dependencies.", 2);
      drush_print();
    }
    else {
      drush_log("Contributed module dependencies are present.");
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
    ];

    $config = new Data($this->config);
    foreach ($deprecated_keys as $deprecated_key) {
      if ($config->get($deprecated_key)) {
        drush_log("The $deprecated_key key is deprecated. Please remove it from project.yml.", 'warning');
      }
    }
  }
}
