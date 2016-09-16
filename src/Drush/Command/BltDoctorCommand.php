<?php

/**
 * @file
 * Defines class that powers `drush blt-doctor` command.
 */

namespace Acquia\Blt\Drush\Command;

class BltDoctor {

  protected $localSettingsPath;
  protected $statusTable;

  /**
   * BoltDoctor constructor.
   */
  public function __construct() {
    $this->statusTable = drush_core_status();
    $this->localSettingsPath = $this->statusTable['root'] . '/' . $this->statusTable['site'] . '/settings/' . 'local.settings.php';
  }

  /**
   * Performs all checks.
   */
  public function checkAll() {
    $this->checkLocalSettingsFile();
    $this->checkUriResponse();
    $this->checkHttps();
    $this->checkDbConnection();
    $this->checkCachingConfig();
    $this->checkNvmExists();
    //$this->checkDatabaseUpdates();

    // @todo Check if Drupal is installed.
    // @todo Check if files directory exists.
    // @todo Check if config dir exists.
    // @todo Check for config issues. E.g., features overrides, config/optional dirs, etc.
    // @todo Check error_level is correct for local dev.
    // @todo Check if theme dependencies have been built.
    // @todo Check if composer dependencies have been built.
    // @todo If using DD, verify correct environmental variables.
    // @todo Check that CI is initialized, repos are populated.
    // @todo Check that if drupal/acsf is in composer.json, acsf is initialized.
    // @todo If using lightning, check lightning.extend.yml exists.
    // @todo Check for existence of deprecated BLT files.
    // @todo Check that docroot value in Behat's local.yml is correct. Consider DrupalVM.
    // @todo Check that base_url value in Behat's local.yml is correct. Consider DrupalVM.
    // @todo Check is PhantomJS bin matches OS.
  }

  /**
   * Checks that local settings file exists.
   */
  protected function checkLocalSettingsFile() {
    if (!file_exists($this->localSettingsPath)) {
      drush_log("Could not find local settings file!", 'error');
      drush_print("Your local settings file should exist at $this->localSettingsPath", 2);
    }
    else {
      drush_log("Found your local settings file at:", 'success');
      drush_print($this->localSettingsPath, 2);
    }
    drush_print();
  }

  /**
   * Checks that configured $base_url responds to requests.
   */
  protected function checkUriResponse() {
    $site_available = drush_shell_exec("curl -I --insecure %s", $this->statusTable['uri']);
    if (!$site_available) {
      drush_log("Did not get response from $this->statusTable['uri']", 'error');
      drush_print("Is your *AMP stack running?", 2);
      drush_print("Is your \$base_url set correctly in $this->localSettingsPath?", 2);
    }
    else {
      drush_log("Received response from site:", 'success');
      drush_print($this->statusTable['uri'], 2);
    }
    drush_print();
  }

  /**
   * Checks that SSL cert is valid for $base_url.
   */
  protected function checkHttps() {
    if (strstr($this->statusTable['uri'], 'https')) {
      if (!drush_shell_exec('curl -cacert %s', $this->statusTable['uri'])) {
        drush_log('The SSL certificate for your local site appears to be invalid:', 'error');
        drush_print($this->statusTable['uri'], 2);
        drush_print();
      }
    }
  }

  /**
   * Checks that drush is able to bootstrap and connect to database.
   */
  protected function checkDbConnection() {
    if (empty($this->statusTable['bootstrap']) || $this->statusTable['bootstrap'] != 'Successful') {
      drush_log('Could not bootstrap Drupal!', 'error');
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

      drush_print('Is the active PHP binary the same one that is associated with your database service?');
      drush_blt_print_status_rows($this->statusTable, array(
        'php-os',
        'php-bin',
        'php-conf',
      ));

      drush_print('Are you using the correct site and settings.php file?');
      drush_blt_print_status_rows($this->statusTable, array(
        'site',
        'drupal-settings-file',
      ));
    }
    else {
      drush_log('Bootstrapped Drupal and connected to database.', 'success');
    }
    drush_print();
  }

  /**
   * Checks if database updates are pending.
   */
  protected function checkDatabaseUpdates() {
    drush_include_engine('drupal', 'update');
    $pending = update_main();

    if ($pending) {
      drush_log("There are pending database updates", 'error');
      drush_print("Run `drush updb` to execute the updates.", 2);
    }
    else {
      drush_log("There are no pending database updates.", 'success');
    }
    drush_print();
  }

  /**
   * Checks that nvm exists.
   *
   * Note that this does not check if `nvm use` has been invoked for the correct
   * node version.
   */
  protected function checkNvmExists() {
    $home = getenv("HOME");
    if (!file_exists("$home/.nvm")) {
      drush_log('NVM does not exist. Install using the following commands:', 'error');
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

  protected function checkContribExists() {
    if (!file_exists($this->statusTable['root'] . '/sites/all/modules/contrib')) {
      drush_log("Contributed module dependencies are missing.", 'error');
      drush_print("Run `./task.sh setup:build:all to build all contributed dependencies.", 2);
      drush_print();
    }
    else {
      drush_log("Contributed module dependencies are present.");
    }
  }
}
