<?php

namespace Acquia\Blt\Drush\Command;

use Dflydev\DotAccessData\Data;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Yaml\Yaml;
use Drupal\Core\Installer\Exception\AlreadyInstalledException;
use Drush\Commands\core\StatusCommands;

/**
 * Provides drush `blt-doctor` command.
 */
class BltDoctor {

  /**
   * @var string*/
  protected $localSettingsPath;
  /**
   * @var array*/
  protected $statusTable = [];
  /**
   * @var bool*/
  protected $ciEnabled = FALSE;
  /**
   * @var array*/
  protected $composerJson = [];
  /**
   * @var array*/
  protected $composerLock = [];
  /**
   * @var bool*/
  protected $drupalVmEnabled = FALSE;
  /**
   * @var array*/
  protected $drupalVmConfig = [];
  /**
   * @var bool*/
  protected $devDesktopEnabled = FALSE;
  /**
   * @var array*/
  protected $config = [];
  /**
   * @var array*/
  protected $drushAliases = [];
  /**
   * @var bool*/
  protected $passed = TRUE;
  /**
   * @var bool*/
  protected $SimpleSamlPhpEnabled = FALSE;
  /**
   * @var \Symfony\Component\Console\Output\ConsoleOutput*/
  protected $output;
  /**
   * @var string*/
  protected $bltVersion;
  /**
   * @var array*/
  protected $outputTable = [];

  /**
   * BoltDoctor constructor.
   */
  public function __construct() {
    $this->output = new ConsoleOutput();
    $this->output->setFormatter(new OutputFormatter(TRUE));

    $this->setStatusTable();

    if (!$this->docrootExists()) {
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
    $this->setComposerJson();
    $this->setComposerLock();
    $this->setBltVersion();
    $this->statusTable['blt-version'] = $this->bltVersion;
    $this->statusTable['php-mysql'] = ini_get('pdo_mysql.default_socket');
    $this->statusTable['shell'] = $_ENV['SHELL'];

    $this->setProjectConfig();
    $this->setCiEnabled();
    $this->setStacksEnabled();
    $this->setSimpleSamlPhpEnabled();
  }

  /**
   * Sets $this->statusTable using drush internals.
   *
   * @return array
   */
  public function setStatusTable() {
    if (function_exists('drush_core_status')) {
      $status_table = drush_core_status();
    }
    else {
      $status_table = StatusCommands::getPropertyList([]);
    }
    $this->statusTable = $status_table;

    return $status_table;
  }

  /**
   *
   */
  protected function setBltVersion() {
    foreach ($this->composerLock['packages'] as $package) {
      if ($package['name'] == 'acquia/blt') {
        $this->bltVersion = $package['version'];

        return $package['version'];
      }
    }
  }

  /**
   * Sets $this->config from project.yml.
   *
   * @return array|bool|mixed
   */
  protected function setProjectConfig() {
    $filepath = $this->repoRoot . '/blt/project.yml';
    if (!file_exists($filepath)) {
      $this->output->write("<error>project.yml is missing from the repository!</error>");

      return [];
    }

    $this->config = Yaml::parse(file_get_contents($filepath));

    $filepath = $this->repoRoot . '/blt/project.local.yml';
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
   * Sets $this->composerJson using root composer.lock file.
   *
   * @return array
   */
  protected function setComposerLock() {
    if (file_exists($this->repoRoot . '/composer.lock')) {
      $composer_lock = json_decode(file_get_contents($this->repoRoot . '/composer.lock'), TRUE);
      $this->composerLock = $composer_lock;

      return $composer_lock;
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
    $this->statusTable['dev-desktop-enabled'] = $this->devDesktopEnabled;

    if (file_exists($this->repoRoot . '/Vagrantfile')) {
      $this->drupalVmEnabled = TRUE;
    }

    $this->statusTable['drupal-vm-enabled'] = $this->drupalVmEnabled;
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
   * Sets SimpleSAMLphp enabled status.
   */
  protected function setSimpleSamlPhpEnabled() {
    if ($this->config['simplesamlphp']) {
      $this->SimpleSamlPhpEnabled = TRUE;
    }
  }

  /**
   * Performs all checks.
   */
  public function checkAll() {
    if (!$this->checkDocrootExists()) {
      return FALSE;
    }

    $this->checkCoreExists();
    if (!$this->coreExists()) {
      return FALSE;
    }

    $this->checkSettingsFile();
    $this->checkLocalSettingsFile();
    $this->checkLocalDrushFile();

    if ($this->localDrushFileExists()) {
      if ($this->checkUri()) {
        $this->checkUriResponse();
        $this->checkHttps();
      }
    }

    $this->checkFileSystem();
    $this->checkDbConnection();
    $this->checkDrupalBootstrapped();
    $this->checkDrupalInstalled();
    $this->checkCaching();
    $this->checkDevDesktop();
    $this->checkCiConfig();
    $this->checkComposer();
    $this->checkBehat();
    $this->checkProjectYml();
    $this->checkAcsfConfig();
    $this->checkDrushAliases();
    $this->checkDrupalVm();
    $this->checkSimpleSamlPhp();
    $this->checkPhpDateTimezone();

    ksort($this->statusTable);
    $this->printArrayAsTable($this->statusTable);
    $this->printArrayAsTable($this->outputTable, ['Check', 'Outcome']);

    // @todo Check error_level.
    // @todo Check if theme dependencies have been built.
    // @todo Check that if drupal/acsf is in composer.json, acsf is initialized.
    // @todo If using lightning, check lightning.extend.yml exists, check for $config['profile'] = 'lighting';
    // @todo Check global drush version.
    // @todo test with missing local.drushrc.php.
    // @todo check if deprecated files from blt cleanup exist
  }

  /**
   *
   */
  protected function logOutcome($check, $outcome, $type) {
    $this->outputTable["<$type>$check</$type>"] = $outcome;
    if ($type == 'error') {
      $this->passed = FALSE;
    }
  }

  /**
   * @param $array
   */
  protected function printArrayAsTable($array, $headers = array('Property', 'Value')) {
    $rowGenerator = function () use ($array) {
      $rows = [];
      $max_line_length = 80;
      foreach ($array as $key => $value) {
        if (is_array($value)) {

          if (is_numeric(key($value))) {
            $row_contents = implode("\n", $value);
            $rows[] = [$key, wordwrap($row_contents, $max_line_length, "\n", TRUE)];
          }
          else {
            $rows[] = [$key, ''];
            foreach ($value as $sub_key => $sub_value) {
              $rows[] = [' - ' . $sub_key, wordwrap($sub_value, $max_line_length, "\n", TRUE)];
            }
          }

          if (count($value) > 1) {
            // $rows[] = new TableSeparator();
          }
        }
        else {
          if (is_bool($value)) {
            if ($value) {
              $value = 'true';
            }
            else {
              $value = 'false';
            }
          }
          $contents = wordwrap($value, $max_line_length, "\n", TRUE);
          $rows[] = [$key, $contents];
        }
      }

      return $rows;
    };

    $table = new Table($this->output);
    $table->setHeaders($headers)
      ->setRows($rowGenerator())
      ->render();
  }

  /**
   *
   */
  protected function docrootExists() {
    return !empty($this->statusTable['root']);
  }

  /**
   * @return bool
   */
  protected function checkDocrootExists() {
    if (!$this->docrootExists()) {
      $this->output->writeln("<error>Drush could not find the docroot.</error>");

      return FALSE;
    }

    $this->logOutcome(__FUNCTION__, "Found docroot.", 'info');

    return TRUE;
  }

  /**
   * Checks that local settings file exists.
   */
  protected function checkLocalSettingsFile() {
    if (!file_exists($this->localSettingsPath)) {
      $this->logOutcome(__FUNCTION__, [
        'Could not find local settings file.',
        "Your local settings file should exist at $this->localSettingsPath.",
      ], 'error');
    }
    else {
      $this->logOutcome(__FUNCTION__, "Found your local settings file.", 'info');
      $this->statusTable['local-settings'] = $this->localSettingsPath;
    }
  }

  /**
   * Checks active settings.php file.
   */
  protected function checkSettingsFile() {
    if (!file_exists($this->statusTable['drupal-settings-file'])) {
      $this->logOutcome(__FUNCTION__, "Could not find settings.php for this site.", 'error');
    }
    else {
      $settings_file_path = $this->docroot . '/' . $this->statusTable['drupal-settings-file'];
      $this->logOutcome(__FUNCTION__, "Found settings file.", 'info');

      $settings_file_contents = file_get_contents($settings_file_path);
      if (strstr($settings_file_contents, '/../vendor/acquia/blt/settings/blt.settings.php')) {
        $this->logOutcome(__FUNCTION__, "BLT settings are included in settings file.", 'info');
      }
      else {
        $this->logOutcome(__FUNCTION__, "BLT settings are not included in settings file.", 'error');
      }
      if (strstr($settings_file_contents, '/sites/default/settings/blt.settings.php')) {
        $this->logOutcome(__FUNCTION__, [
          'Your settings file contains a deprecated statement for including BLT settings.',
          "Please remove the line containing \"/sites/default/settings/blt.settings.php\" in $settings_file_path.",
        ], 'error');
      }
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
      $this->logOutcome(__FUNCTION__, [
        "Local drushrc file does not exist.",
        "Create $this->localDrushRcPath.",
        "Run `blt setup:drush:settings` to generate it automatically, or run `blt setup` to run the entire setup process.",
      ], 'error');
    }
    else {
      $this->logOutcome(__FUNCTION__, "Found your local drush settings file.", 'info');
      $this->statusTable['local-drushrc'] = $this->localDrushRcPath;
    }
  }

  /**
   * @return bool
   */
  protected function checkUri() {
    if (!$this->uri || $this->uri == 'default') {
      $this->logOutcome(__FUNCTION__, [
        "Site URI is not set",
        "",
        "Is \$options['uri'] set correctly in $this->localDrushRcPath?",
        "",
      ], 'error');

      return FALSE;
    }
    else {
      $this->logOutcome(__FUNCTION__, "\$options['uri'] is set.", 'info');

      return TRUE;
    }
  }

  /**
   * Checks that configured URI responds to requests.
   */
  protected function checkUriResponse() {
    $site_available = drush_shell_exec("curl -I --insecure %s", $this->uri);
    if (!$site_available) {
      $this->logOutcome(__FUNCTION__, [
        "Did not get a response from $this->uri",
        "",
        "Is your *AMP stack running?",
        "Is your web server configured to serve this URI from $this->docroot?",
        "Is \$options['uri'] set correctly in $this->localDrushRcPath?",
        "",
        "To generate settings files and install Drupal, run `blt local:setup`",
        "",
      ], 'error');
    }
    else {
      $this->logOutcome(__FUNCTION__, [
        "Received a response from site {$this->statusTable['uri']}.",
      ], 'info');
    }
  }

  /**
   * Checks that SSL cert is valid for configured URI.
   */
  protected function checkHttps() {
    if (strstr($this->statusTable['uri'], 'https')) {
      if (!drush_shell_exec('curl -cacert %s', $this->statusTable['uri'])) {
        $this->logOutcome(__FUNCTION__, [
          "The SSL certificate for your local site appears to be invalid for {$this->statusTable['uri']}.",
        ], 'error');
      }
      else {
        $this->logOutcome(__FUNCTION__, [
          "The SSL certificate for your local site appears valid.",
        ], 'info');
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
      $this->logOutcome(__FUNCTION__, [
        "Connected to database.",
      ], 'info');

      return TRUE;
    }

    $outcome = [
      'Could not connect to MySQL database.',
      "",
      "Is your *AMP stack running?",
      'Are your database credentials correct?',
      "  db-driver: {$this->statusTable['db-driver']}",
      "  db-hostname: {$this->statusTable['db-hostname']}",
      "  db-username: {$this->statusTable['db-username']}",
      "  db-password: {$this->statusTable['db-password']}",
      "  db-name: {$this->statusTable['db-name']}",
      "  db-port: {$this->statusTable['db-port']}",
      "",
    ];

    if ($this->statusTable['db-driver'] == 'mysql') {
      $outcome[] = "To verify your mysql credentials, run `mysql -u {$this->statusTable['db-username']} -h {$this->statusTable['db-hostname']} -p{$this->statusTable['db-password']} -P {$this->statusTable['db-port']}`";
      $outcome[] = "";
    }

    $php_conf = is_array($this->statusTable['php-conf']) ? implode(', ', $this->statusTable['php-conf']) : $this->statusTable['php-conf'];
    $outcome = array_merge($outcome, [
      'Are you using the correct PHP binary?',
      'Is PHP using the correct MySQL socket?',
      "  php-os: {$this->statusTable['php-os']}",
      "  php-bin: {$this->statusTable['php-bin']}",
      "  php-conf: $php_conf",
      "  php-mysql: {$this->statusTable['php-mysql']}",
      '',
      'Are you using the correct site and settings.php file?',
      "  site: {$this->statusTable["site"]}",
      "  drupal-settings-file: {$this->statusTable["drupal-settings-file"]}",
      "",
      "To verify, run `drush sqlc`",
      "",
    ]);

    $this->logOutcome(__FUNCTION__, $outcome, 'error');
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
      $this->logOutcome(__FUNCTION__, [
        "Drupal core is missing!",
        "",
        "  Looked for docroot in {$this->docroot}.",
        "Check and re-install your composer dependencies.",
      ], 'error');
    }
    else {
      $this->logOutcome(__FUNCTION__, "Drupal core exists", 'info');
    }
  }

  /**
   * Checks that drush is able to bootstrap Drupal Core.
   *
   * This is only possible if Drupal is installed.
   */
  protected function checkDrupalBootstrapped() {
    if (empty($this->statusTable['bootstrap']) || $this->statusTable['bootstrap'] != 'Successful') {
      $this->logOutcome(__FUNCTION__, [
        'Could not bootstrap Drupal via drush without alias.',
      ], 'comment');
    }
    else {
      $this->logOutcome(__FUNCTION__, [
        'Bootstrapped Drupal via drush.',
      ], 'info');
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
      $this->logOutcome(__FUNCTION__, [
        "Drupal is not installed.",
        "",
        'Run `blt local:setup` to install Drupal locally.',
      ], 'error');
    }
    catch (AlreadyInstalledException $e) {
      $this->logOutcome(__FUNCTION__, "Drupal is installed.", 'info');
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
        $this->logOutcome(__FUNCTION__ . ":$key", "$title is not set.", 'error');

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
        $this->logOutcome(__FUNCTION__ . ":$key", "$title exists.", 'info');

        if (is_writable($full_path)) {
          $this->logOutcome(__FUNCTION__ . ":$key", "$title is writable.", 'info');
        }
        else {
          $this->logOutcome(__FUNCTION__ . ":$key", [
            "$title is not writable.",
            "",
            "Change the permissions on $full_path.",
            "Run `chmod 750 $full_path`.",
          ], 'error');
        }
      }
      else {
        $outcome = [
          "$title does not exist.",
          "",
          "Create $full_path.",
        ];

        if (in_array($key, ['%files', '%private'])) {
          $outcome[] = "Installing Drupal will create this directory for you.";
          $outcome[] = "Run `blt setup:drupal:install` to install Drupal, or run `blt setup` to run the entire setup process.";
          $outcome[] = "Otherwise, run `mkdir -p $full_path`.";
          $outcome[] = "";
        }

        $this->logOutcome(__FUNCTION__ . ":$key", $outcome, 'error');
      }

    }
  }

  /**
   * Checks that Dev Desktop is configured correctly.
   */
  protected function checkDevDesktop() {
    if ($this->devDesktopEnabled) {
      if (empty($_ENV['DEVDESKTOP_DRUPAL_SETTINGS_DIR'])) {
        $this->logOutcome(__FUNCTION__, [
          "DevDesktop usage is enabled, but \$DEVDESKTOP_DRUPAL_SETTINGS_DIR is not set in your environmental variables.",
          "",
          "Add `export DEVDESKTOP_DRUPAL_SETTINGS_DIR=\"\$HOME/.acquia/DevDesktop/DrupalSettings\"` to ~/.bash_profile or equivalent for your system.`",
        ], 'error');
      }
      elseif (strstr($_ENV['DEVDESKTOP_DRUPAL_SETTINGS_DIR'], '~')) {
        $this->logOutcome(__FUNCTION__, [
          "\$DEVDESKTOP_DRUPAL_SETTINGS_DIR contains a '~'. This does not always expand to your home directory.",
          "",
          "Add `export DEVDESKTOP_DRUPAL_SETTINGS_DIR=\"\$HOME/.acquia/DevDesktop/DrupalSettings\"` to ~/.bash_profile or equivalent for your system.`",
        ], 'error');
      }
      else {
        $this->logOutcome(__FUNCTION__, "\$DEVDESKTOP_DRUPAL_SETTINGS_DIR is set.", 'info');
      }

      $variables_order = ini_get('variables_order');
      $php_ini_file = php_ini_loaded_file();
      if (!strstr($variables_order, 'E')) {
        $this->logOutcome(__FUNCTION__, [
          "DevDesktop usage is enabled, but variables_order does support environmental variables.",
          "",
          "Define variables_order = \"EGPCS\" in $php_ini_file",
        ], 'error');
      }
      else {
        $this->logOutcome(__FUNCTION__, "variables_order allows environment variables in php.ini.", 'info');
      }
    }
  }

  /**
   * Checks Drupal VM configuration.
   */
  protected function checkDrupalVm() {
    if ($this->drupalVmEnabled) {
      $passed = TRUE;
      $drupal_vm_config = $this->getDrupalVmConfigFile();
      if (!file_exists($this->repoRoot . '/' . $drupal_vm_config)) {
        $this->logOutcome(__FUNCTION__ . ':init', "You have DrupalVM initialized, but $drupal_vm_config is missing.", 'error');

        $passed = FALSE;
      }
      else {
        $this->setDrupalVmConfig();
      }
      if ($this->drushAliasesFileExists()) {
        $local_alias_id = $this->config['drush']['aliases']['local'];
        if ($local_alias_id !== 'self') {
          if (empty($this->drushAliases[$local_alias_id])) {
            $this->logOutcome(__FUNCTION__ . ":alias", [
              "The drush alias assigned to drush.aliases.local does not exist in your drush aliases file.",
              "  drush.aliases.local is set to @$local_alias_id",
              "  Looked in " . $this->repoRoot . '/drush/site-aliases/aliases.drushrc.php',
            ], 'error');
            $passed = FALSE;
          }
          else {
            $this->logOutcome(__FUNCTION__ . ':alias', "drush.aliases.local exists your drush aliases file.", 'info');
            $local_alias = $this->drushAliases[$local_alias_id];
            if ('vagrant' != $_SERVER['USER'] && $local_alias['remote-host'] != $this->drupalVmConfig['vagrant_hostname']) {
              $this->logOutcome(__FUNCTION__ . ":remote-host", [
                "remote-host for @$local_alias_id drush alias does not match vagrant_hostname for DrupalVM.",
                "  remote-host is set to {$local_alias['remote-host']} for @$local_alias_id",
                "  vagrant_hostname is set to {$this->drupalVmConfig['vagrant_hostname']} for DrupalVM.",
                "  {$local_alias['remote-host']} != {$this->drupalVmConfig['vagrant_hostname']}",
              ], 'error');
              $passed = FALSE;
            }
            $parsed_uri = parse_url($local_alias['uri']);
            if ($parsed_uri['host'] != $this->drupalVmConfig['vagrant_hostname']) {
              $this->logOutcome(__FUNCTION__ . ":uri", [
                "uri for @$local_alias_id drush alias does not match vagrant_hostname for DrupalVM.",
                "  uri is set to {$local_alias['uri']} for @$local_alias_id",
                "  vagrant_hostname is set to {$this->drupalVmConfig['vagrant_hostname']} for DrupalVM.",
                "  {$local_alias['uri']} != {$this->drupalVmConfig['vagrant_hostname']}",
              ], 'error');
              $passed = FALSE;
            }
            $expected_root = $this->drupalVmConfig['drupal_composer_install_dir'] . '/docroot';
            if ($local_alias['root'] != $expected_root) {
              $this->logOutcome(__FUNCTION__ . ":root", [
                "root for @$local_alias_id drush alias does not match docroot for DrupalVM.",
                "  root is set to {$local_alias['root']} for @$local_alias_id",
                "  docroot is set to $expected_root for DrupalVM.",
                "  {$local_alias['root']} != $expected_root",
              ], 'error');
              $passed = FALSE;
            }
          }
        }
      }
    }
    if ($passed) {
      $this->logOutcome(__FUNCTION__, "Drupal VM is configured correctly.", 'info');
    }
  }

  /**
   * @return string
   */
  protected function getDrupalVmConfigFile() {
    // This is the only non-config "box/config.yml" entry.
    $drupal_vm_config = isset($this->config['vm']['config']) ? $this->config['vm']['config'] : 'box/config.yml';
    // Is there a way to calculate this "${repo.root}"? Removing for now.
    $drupal_vm_config = str_replace('${repo.root}', "", $drupal_vm_config);
    return $drupal_vm_config;
  }

  /**
   * @return array|mixed
   */
  protected function setDrupalVmConfig() {
    $this->drupalVmConfig = Yaml::parse(file_get_contents($this->repoRoot . '/' . $this->getDrupalVmConfigFile()));

    return $this->drupalVmConfig;
  }

  /**
   * @return mixed
   */
  protected function drushAliasesFileExists() {
    return file_exists($this->repoRoot . '/drush/site-aliases/aliases.drushrc.php');
  }

  /**
   *
   */
  protected function checkDrushAliases() {
    $file_path = $this->repoRoot . '/drush/site-aliases/aliases.drushrc.php';
    if (!$this->drushAliasesFileExists()) {
      $this->logOutcome(__FUNCTION__, [
        "drush alias file does not exist!",
        "",
        "Create $file_path",
      ], 'error');
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
  protected function checkBehat() {
    if (!file_exists($this->repoRoot . '/tests/behat/local.yml')) {
      $this->logOutcome(__FUNCTION__ . ':exists', [
        "tests/behat/local.yml is missing!",
        "  Run `blt setup:behat` to generate it from example.local.yml.",
      ], 'error');

      return FALSE;
    }
    else {
      $this->logOutcome(__FUNCTION__ . ':exists', "Behat local settings file exists.", 'info');
    }

    $this->behatDefaultLocalConfig = Yaml::parse(file_get_contents($this->repoRoot . '/tests/behat/local.yml'));
    if ($this->drupalVmEnabled) {
      $behat_drupal_root = $this->behatDefaultLocalConfig['local']['extensions']['Drupal\DrupalExtension']['drupal']['drupal_root'];
      if (!strstr($behat_drupal_root, '/var/www/')) {
        $this->logOutcome(__FUNCTION__ . ':root', [
          "You have DrupalVM initialized, but drupal_root in tests/behat/local.yml does not reference the DrupalVM docroot.",
          "  Behat drupal_root is $behat_drupal_root.",
          "  To resolve, run blt setup:behat.",
        ], 'error');
      }
      else {
        $this->logOutcome(__FUNCTION__ . ':root', "Behat drupal_root is set correctly for Drupal VM.", 'info');
      }
    }

    $behat_base_url = $this->behatDefaultLocalConfig['local']['extensions']['Behat\MinkExtension']['base_url'];
    if ($behat_base_url != $this->getUri()) {
      $this->logOutcome(__FUNCTION__ . ':uri', [
        "base_url in tests/behat/local.yml does not match the site URI.",
        "  Behat base_url is set to \"$behat_base_url\".",
        "  Drush site URI is set to {$this->getUri()}.",
      ], 'error');
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
        $this->logOutcome(__FUNCTION__, [
          "Git repositories are not defined in project.yml.",
          "  Add values for git.remotes to project.yml to enabled automated deployment.",
        ], 'error');
      }
      else {
        $this->logOutcome(__FUNCTION__, "Git remotes are set in project.yml.", 'info');
      }
    }
  }

  /**
   * Checks that composer.json is configured correctly.
   */
  protected function checkComposer() {
    if (!empty($this->composerJson['require-dev']['acquia/blt'])) {
      $this->logOutcome(__FUNCTION__ . ':require', [
        "acquia/blt is defined as a development dependency in composer.json",
        "  Move acquia/blt out of the require-dev object and into the require object in composer.json.",
        "  This is necessary for BLT settings files to be available at runtime in production.",
      ], 'error');
    }
    else {
      $this->logOutcome(__FUNCTION__ . ':require', [
        "acquia/blt is in composer.json's require object.",
      ], 'info');
    }

    if ('vagrant' != $_SERVER['USER']) {
      $prestissimo_intalled = drush_shell_exec("composer global show | grep hirak/prestissimo");
      if (!$prestissimo_intalled) {
        $this->logOutcome(__FUNCTION__ . ":plugins", [
          "hirak/prestissimo plugin for composer is not installed.",
          "  Run `composer global require hirak/prestissimo:^0.3` to install it.",
          "  This will improve composer install/update performance by parallelizing the download of dependency information.",
        ], 'comment');
      }
      else {
        $this->logOutcome(__FUNCTION__ . ':plugins', [
          "hirak/prestissimo plugin for composer is installed.",
        ], 'info');
      }
    }
  }

  /**
   *
   */
  protected function checkAcsfConfig() {
    $file_path = $this->repoRoot . '/factory-hooks/pre-settings-php/includes.php';
    if (file_exists($file_path)) {
      $file_contents = file_get_contents($file_path);
      if (!strstr($file_contents, '/../vendor/acquia/blt/settings/blt.settings.php')) {
        $this->logOutcome(__FUNCTION__, [
          "BLT settings are not included in your pre-settings-php include.",
          "  Add a require statement for \"/../vendor/acquia/blt/settings/blt.settings.php\" to $file_path",
        ], 'error');
      }
      else {
        $this->logOutcome(__FUNCTION__, [
          "BLT settings are included in your pre-settings-php include.",
        ], 'info');
      }
    }
  }

  /**
   * Checks that caching is configured for local development.
   */
  protected function checkCaching() {
    if (drush_bootstrap_max(DRUSH_BOOTSTRAP_DRUPAL_FULL)) {
      global $conf;

      if ($conf['cache']) {
        $this->logOutcome(__FUNCTION__ . ':page', 'Drupal cache is enabled. It is suggested that you disable this for local development.', 'comment');
      }
      else {
        $this->logOutcome(__FUNCTION__ . ':page', 'Drupal cache is disabled.', 'info');
      }
      if ($conf['preprocess_css']) {
        $this->logOutcome(__FUNCTION__ . ':css', 'CSS preprocessing enabled. It is suggested that you disable this for local development.', 'comment');
      }
      else {
        $this->logOutcome(__FUNCTION__ . ':css', 'CSS preprocessing is disabled.', 'info');
      }
      if ($conf['preprocess_js']) {
        $this->logOutcome(__FUNCTION__ . ':js', 'JS preprocessing is enabled. It is suggested that you disable this for local development.', 'comment');
      }
      else {
        $this->logOutcome(__FUNCTION__ . ':js', 'JS preprocessing is disabled.', 'info');
      }
    }
  }

  /**
   * Check that the contributed modules directory exists.
   */
  protected function checkContribExists() {
    if (!file_exists($this->docroot . '/sites/all/modules/contrib')) {
      $this->logOutcome(__FUNCTION__, [
        "Contributed module dependencies are missing.",
        "  Run `blt setup:build to build all contributed dependencies.",
      ], 'error');
    }
    else {
      $this->logOutcome(__FUNCTION__, "Contributed module dependencies are present.", 'info');
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
    $outcome = [];
    foreach ($deprecated_keys as $deprecated_key) {
      if ($config->get($deprecated_key)) {
        $outcome[] = "The '$deprecated_key' key is deprecated. Please remove it from project.yml.";
        $deprecated_keys_exist = TRUE;
      }
    }

    if (!$deprecated_keys_exist) {
      $this->logOutcome(__FUNCTION__ . ':keys', "project.yml has no deprecated keys.", 'info');
    }
    else {
      $this->logOutcome(__FUNCTION__ . ':keys', $outcome, 'comment');
    }
  }

  /**
   * Performs a high level check of SimpleSAMLphp installation.
   */
  protected function checkSimpleSamlPhp() {
    if ($this->SimpleSamlPhpEnabled) {
      $lib_root = $this->repoRoot . '/vendor/simplesamlphp/simplesamlphp';
      $config_root = $this->repoRoot . '/simplesamlphp';

      // Check for the configurable files in docroot/simplesamlphp.
      if (!file_exists($config_root)) {
        $this->logOutcome(__FUNCTION__, [
          "Simplesamlphp config directory is missing. $config_root",
          "",
          "Run `blt simplesamlphp:config:init` to create a config directory.",
        ], 'error');
      }

      // Check for the SimpleSAMLphp library in the vendor directory.
      if (!file_exists($lib_root)) {
        $this->logOutcome(__FUNCTION__, [
          "The SimpleSAMLphp library was not found in the vendor directory.",
          "  Run `blt simplesamlphp:config:init` to add the library as a dependency.",
        ], 'error');
      }

      // Compare config files in $config_root and $lib_root.
      if (file_exists($lib_root) && file_exists($config_root)) {
        $config_files = [
          '/config/config.php',
          '/config/authsources.php',
          '/metadata/saml20-idp-remote.php',
        ];
        foreach ($config_files as $config_file) {
          if (file_exists($lib_root . $config_file) && file_exists($config_root . $config_file)) {
            $config_file_content = file_get_contents($config_root . $config_file);
            $lib_file_content = file_get_contents($lib_root . $config_file);
            if (strcmp($config_file_content, $lib_file_content) !== 0) {
              $this->logOutcome(__FUNCTION__, [
                "The configuration file: $config_file in $config_root does not match the one in $lib_root.",
                "  Run `blt simplesamlphp:build:config` to copy the files from the repo root to the library.",
              ], 'error');
            }
          }
          else {
            $lib_file_path = $lib_root . $config_file;
            $this->logOutcome(__FUNCTION__, [
              "$lib_file_path is missing. Run `blt simplesamlphp:build:config`.",
            ], 'error');
          }
        }
      }

      // Check that the library's www dirctory is symlinked in the docroot.
      if (!file_exists($this->docroot . '/simplesaml')) {
        $this->logOutcome(__FUNCTION__, [
          "The symlink to the SimpleSAMLphp library is missing from your docroot.",
          "  Run `blt simplesamlphp:init`",
        ], 'error');
      }

      // Check that access to the symlinked directory is not blocked.
      $htaccess = file_get_contents($this->docroot . '/.htaccess');
      if (!strstr($htaccess, 'simplesaml')) {
        $this->logOutcome(__FUNCTION__, [
          "Access to $this->docroot/simplesaml is blocked by .htaccess",
          "  Add the snippet in simplesamlphp-setup.md readme to your .htaccess file.",
        ], 'error');
      }
    }
  }

  /**
   * Checks the php date.timezone setting is correctly set.
   */
  protected function checkPhpDateTimezone() {
    $dateTimezone = ini_get('date.timezone');
    $php_ini_file = php_ini_loaded_file();
    if (!$dateTimezone) {
      $this->logOutcome(__FUNCTION__, [
        "PHP setting for date.timezone is not set.",
        "  Define date.timezone in $php_ini_file",
      ], 'error');
    }
    else {
      $this->logOutcome(__FUNCTION__, "PHP setting for date.timezone is correctly set", 'info');
    }
  }

}
